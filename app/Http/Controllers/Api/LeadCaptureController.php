<?php

namespace App\Http\Controllers\Api;

use App\Enums\ActivityType;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Notifications\LeadAssignmentNotification;
use App\Services\LeadCodeGenerator;
use App\Services\LeadDefaults;
use App\Services\LeadAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeadCaptureController extends Controller
{
    public function __construct(private LeadAssignmentService $leadAssignment) {}

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email',
            'phone'        => 'required|string|max:20',
            'service'      => 'nullable|string|max:255',
            'gender'       => 'nullable|in:male,female,other',
            'dob'          => 'nullable|date|before:today',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'district'     => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'pincode'      => 'nullable|string|max:10',
            'utm_source'   => 'nullable|string|max:255',
            'utm_medium'   => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_content'  => 'nullable|string|max:255',
            'utm_term'     => 'nullable|string|max:255',
            'fbclid'       => 'nullable|string|max:255',
        ]);

        $phone = $request->phone;
        if (!str_starts_with($phone, '+91')) {
            $phone = '+91' . ltrim($phone, '0');
        }

        $duplicate = Lead::where('email', $request->email)
            ->orWhere('phone', $phone)
            ->first();

        if ($duplicate) {
            $fields = [];
            if ($duplicate->email === $request->email) {
                $fields[] = 'email';
            }
            if ($duplicate->phone === $phone) {
                $fields[] = 'mobile number';
            }
            return response()->json([
                'success' => false,
                'message' => 'The ' . implode(' and ', $fields) . ' already exists.',
            ], 409)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        $serviceId = $request->filled('service')
            ? Service::where('name', trim($request->service))->value('id')
            : null;

        [$sourceCategory, $source] = $this->resolveMetaSource(
            $request->input('utm_source'),
            $request->input('utm_medium'),
            $request->input('fbclid')
        );

        $lead = Lead::create([
            'lead_code'       => LeadCodeGenerator::placeholder(),
            'name'            => $request->name,
            'email'           => $request->email,
            'phone'           => $phone,
            'gender'          => $request->gender ?: null,
            'dob'             => $request->dob ?: null,
            'address'         => $request->address ?: null,
            'city'            => $request->city ?: null,
            'district'        => $request->district ?: null,
            'state'           => $request->state ?: null,
            'pincode'         => $request->pincode ?: null,
            'service_id'      => $serviceId,
            'source'          => $source,
            'source_type'     => 'landing_page',
            'source_category' => $sourceCategory,
            'source_detail'   => $request->input('utm_source'),
            'fbclid'          => $request->input('fbclid'),
            'utm_campaign'    => $request->input('utm_campaign'),
            'utm_medium'      => $request->input('utm_medium'),
            'utm_content'     => $request->input('utm_content'),
            'utm_term'        => $request->input('utm_term'),
            'status'          => LeadDefaults::defaultStatus(),
        ]);

        LeadCodeGenerator::assignCode($lead);

        $this->leadAssignment->assignIncomingLead($lead);

        LeadActivity::create([
            'lead_id'       => $lead->id,
            'user_id'       => null,
            'type'          => ActivityType::Note->value,
            'description'   => 'Lead captured from Landing Page',
            'meta_data'     => null,
            'activity_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
        ]);

        $managerId = $lead->assigned_by;
        if ($managerId) {
            $manager = \App\Models\User::find($managerId);
            if ($manager) {
                $manager->notify(new LeadAssignmentNotification(
                    title:   'New Lead Assigned',
                    message: 'Lead ' . $lead->lead_code . ' auto-assigned to you.',
                    link:    route('manager.leads.show', encrypt($lead->id)),
                    meta:    ['type' => 'lead_assignment', 'lead_id' => $lead->id]
                ));
            }

            LeadActivity::create([
                'lead_id'       => $lead->id,
                'user_id'       => null,
                'type'          => ActivityType::Assignment->value,
                'description'   => "Auto-assigned to manager #{$managerId}",
                'meta_data'     => ['manager_id' => $managerId],
                'activity_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead stored successfully',
        ])->withHeaders([
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Detect whether the lead came from Meta (Facebook/Instagram) ads
     * based on UTM parameters or the presence of fbclid.
     *
     * Returns [$sourceCategory, $source].
     */
    private function resolveMetaSource(?string $utmSource, ?string $utmMedium, ?string $fbclid): array
    {
        $utmSource = strtolower(trim((string) $utmSource));
        $utmMedium = strtolower(trim((string) $utmMedium));

        if (str_contains($utmSource, 'instagram')) {
            return ['instagram_ads', 'instagram_ads'];
        }

        if (str_contains($utmSource, 'facebook') || str_contains($utmSource, 'fb')) {
            return ['facebook_ads', 'facebook_ads'];
        }

        if ($fbclid) {
            return ['facebook_ads', 'facebook_ads'];
        }

        if (str_contains($utmMedium, 'paid') || str_contains($utmMedium, 'cpc') || str_contains($utmMedium, 'cpm')) {
            return ['other_digital', 'Landing Page'];
        }

        return ['website', 'Landing Page'];
    }
}
