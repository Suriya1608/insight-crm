<?php

namespace App\Exports;

use App\Models\Lead;
use App\Models\LeadActivity;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public static function buildQuery(array $filters = [])
    {
        $query = Lead::with(['assignedBy:id,name', 'assignedUser:id,name', 'service:id,name']);

        // Scope filter (mirrors renderIndex scope switch)
        $scope = $filters['scope'] ?? 'all';
        switch ($scope) {
            case 'unassigned': $query->whereNull('assigned_to'); break;
            case 'assigned':   $query->whereNotNull('assigned_to'); break;
            case 'converted':  $query->where('status', 'converted'); break;
            case 'lost':       $query->where('status', 'not_interested'); break;
            case 'duplicates':
                $dupPhones = Lead::select('phone')->whereNotNull('phone')->groupBy('phone')->havingRaw('COUNT(*) > 1')->pluck('phone');
                $dupEmails = Lead::select('email')->whereNotNull('email')->where('email', '!=', '')->groupBy('email')->havingRaw('COUNT(*) > 1')->pluck('email');
                $query->where(fn($q) => $q->whereIn('phone', $dupPhones)->orWhereIn('email', $dupEmails));
                break;
        }

        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn($q) => $q
                ->where('lead_code', 'like', "%{$s}%")
                ->orWhere('name',  'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
            );
        }

        if (!empty($filters['manager_id']))       $query->where('assigned_by',      $filters['manager_id']);
        if (!empty($filters['telecaller_id']))    $query->where('assigned_to',       $filters['telecaller_id']);
        if (!empty($filters['status']))           $query->where('status',            $filters['status']);
        if (!empty($filters['service_id']))        $query->where('service_id', $filters['service_id']);
        if (!empty($filters['source']))           $query->where('source',            $filters['source']);
        if (!empty($filters['gender']))           $query->where('gender',            $filters['gender']);
        if (!empty($filters['state']))            $query->where('state',    'like',  '%' . $filters['state']    . '%');
        if (!empty($filters['city']))             $query->where('city',     'like',  '%' . $filters['city']     . '%');
        if (!empty($filters['district']))         $query->where('district', 'like',  '%' . $filters['district'] . '%');

        if (!empty($filters['date_range'])) {
            if ($filters['date_range'] === 'custom') {
                if (!empty($filters['date_from'])) $query->whereDate('created_at', '>=', $filters['date_from']);
                if (!empty($filters['date_to']))   $query->whereDate('created_at', '<=', $filters['date_to']);
            } elseif ($filters['date_range'] === 'today') {
                $query->whereDate('created_at', today());
            } elseif (is_numeric($filters['date_range'])) {
                $query->whereDate('created_at', '>=', now()->subDays((int) $filters['date_range']));
            }
        }

        if (!empty($filters['followup'])) {
            match ($filters['followup']) {
                'today'     => $query->whereHas('followups', fn($q) => $q->whereDate('next_followup', today())),
                'overdue'   => $query->whereHas('followups', fn($q) => $q->whereDate('next_followup', '<', today())),
                'this_week' => $query->whereHas('followups', fn($q) => $q
                    ->whereDate('next_followup', '>=', today())
                    ->whereDate('next_followup', '<=', today()->endOfWeek())),
                'none'      => $query->whereDoesntHave('followups'),
                default     => null,
            };
        }

        if (!empty($filters['no_activity_days']) && is_numeric($filters['no_activity_days'])) {
            $cutoff    = now()->subDays((int) $filters['no_activity_days']);
            $recentIds = LeadActivity::where('activity_time', '>=', $cutoff)->distinct()->pluck('lead_id');
            $query->whereNotIn('id', $recentIds);
        }

        if (!empty($filters['sla'])) {
            if ($filters['sla'] === 'escalated') {
                $query->whereNotNull('sla_escalated_at');
            } elseif (is_numeric($filters['sla'])) {
                $query->where('sla_level', '>=', (int) $filters['sla']);
            }
        }

        if (isset($filters['is_duplicate']) && $filters['is_duplicate'] !== '')
            $query->where('is_duplicate', (bool) $filters['is_duplicate']);
        if (isset($filters['is_active']) && $filters['is_active'] !== '')
            $query->where('is_active', (bool) $filters['is_active']);

        if (!empty($filters['aged_min']) && is_numeric($filters['aged_min']))
            $query->whereDate('created_at', '<=', now()->subDays((int) $filters['aged_min']));
        if (!empty($filters['aged_max']) && is_numeric($filters['aged_max']))
            $query->whereDate('created_at', '>=', now()->subDays((int) $filters['aged_max']));

        return $query;
    }

    public function query()
    {
        return self::buildQuery($this->filters)->orderBy('id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Lead Code', 'Name', 'Phone', 'Email',
            'Service',
            'Source', 'Gender', 'State', 'City', 'District',
            'Status', 'Manager', 'Telecaller',
            'Duplicate', 'Active', 'SLA Level', 'Days Aged', 'Created At',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->lead_code,
            $lead->name,
            $lead->phone,
            $lead->email ?? '',
            $lead->service?->name ?? '',
            $lead->source ?? '',
            $lead->gender ? ucfirst($lead->gender) : '',
            $lead->state ?? '',
            $lead->city ?? '',
            $lead->district ?? '',
            ucfirst(str_replace('_', ' ', $lead->status)),
            $lead->assignedBy?->name ?? '—',
            $lead->assignedUser?->name ?? '—',
            $lead->is_duplicate ? 'Yes' : 'No',
            $lead->is_active ? 'Yes' : 'No',
            $lead->sla_level ?? 0,
            $lead->days_aged,
            $lead->created_at->format('d M Y H:i'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, 'B' => 25, 'C' => 18, 'D' => 30,
            'E' => 22,
            'F' => 16, 'G' => 12, 'H' => 16, 'I' => 16, 'J' => 16,
            'K' => 18, 'L' => 22, 'M' => 22,
            'N' => 12, 'O' => 10, 'P' => 10, 'Q' => 12, 'R' => 20,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF6366F1'],
                ],
            ],
        ];
    }
}
