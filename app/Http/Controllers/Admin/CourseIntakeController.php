<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CourseIntake;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CourseIntakeController extends Controller
{
    public function index(Request $request)
    {
        $years        = AcademicYear::orderByDesc('id')->get();
        $activeYear   = AcademicYear::current();
        $selectedYear = $request->year_id
            ? AcademicYear::find($request->year_id)
            : $activeYear;

        $allIntakes = $selectedYear
            ? CourseIntake::with('course')
                ->where('academic_year_id', $selectedYear->id)
                ->orderBy('id')
                ->get()
            : collect();

        $intakes = $selectedYear
            ? CourseIntake::with('course')
                ->where('academic_year_id', $selectedYear->id)
                ->orderBy('id')
                ->paginate(10)
                ->appends(request()->only('year_id'))
            : collect();

        return view('admin.course-intakes.index', compact('years', 'selectedYear', 'intakes', 'allIntakes', 'activeYear'));
    }

    public function create()
    {
        $years   = AcademicYear::orderByDesc('id')->get();
        $courses = Course::active()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.course-intakes.create', compact('years', 'courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id'  => 'required|exists:academic_years,id',
            'course_id'         => 'required|exists:courses,id',
            'management_seats'  => 'required|integer|min:0|max:9999',
            'counselling_seats' => 'required|integer|min:0|max:9999',
        ]);

        $exists = CourseIntake::withTrashed()
            ->where('course_id', $data['course_id'])
            ->where('academic_year_id', $data['academic_year_id'])
            ->first();

        if ($exists) {
            if ($exists->trashed()) {
                $exists->restore();
                $exists->update([
                    'management_seats'   => $data['management_seats'],
                    'counselling_seats'  => $data['counselling_seats'],
                    'management_enrolled'  => 0,
                    'counselling_enrolled' => 0,
                ]);
            } else {
                return back()->withErrors(['course_id' => 'An intake for this course and year already exists.'])->withInput();
            }
        } else {
            CourseIntake::create($data + [
                'management_enrolled'  => 0,
                'counselling_enrolled' => 0,
            ]);
        }

        return redirect()->route('admin.course-intakes.index', ['year_id' => $data['academic_year_id']])
            ->with('success', 'Intake created successfully.');
    }

    public function edit(string $id)
    {
        $courseIntake = CourseIntake::with(['course', 'academicYear'])->findOrFail(decrypt($id));

        return view('admin.course-intakes.edit', compact('courseIntake'));
    }

    public function update(Request $request, string $id)
    {
        $courseIntake = CourseIntake::findOrFail(decrypt($id));

        $data = $request->validate([
            'management_seats'  => 'required|integer|min:' . $courseIntake->management_enrolled . '|max:9999',
            'counselling_seats' => 'required|integer|min:' . $courseIntake->counselling_enrolled . '|max:9999',
        ]);

        $courseIntake->update($data);

        return redirect()->route('admin.course-intakes.index', ['year_id' => $courseIntake->academic_year_id])
            ->with('success', 'Intake updated.');
    }

    public function export(Request $request, string $format)
    {
        if (!in_array($format, ['excel', 'pdf'], true)) {
            abort(404);
        }

        $year = $request->year_id
            ? AcademicYear::find($request->year_id)
            : AcademicYear::current();

        $intakes = $year
            ? CourseIntake::with('course')
                ->where('academic_year_id', $year->id)
                ->orderBy('id')
                ->get()
            : collect();

        $totalMgmtSeats = $intakes->sum('management_seats');
        $totalCounSeats = $intakes->sum('counselling_seats');
        $totalSeats     = $totalMgmtSeats + $totalCounSeats;
        $totalMgmtEnr   = $intakes->sum('management_enrolled');
        $totalCounEnr   = $intakes->sum('counselling_enrolled');
        $totalEnrolled  = $totalMgmtEnr + $totalCounEnr;
        $fillPct        = $totalSeats > 0 ? round($totalEnrolled / $totalSeats * 100) : 0;

        $summary = [
            'total_seats'    => $totalSeats,
            'total_enrolled' => $totalEnrolled,
            'total_balance'  => $totalSeats - $totalEnrolled,
            'fill_pct'       => $fillPct,
            'mgmt_seats'     => $totalMgmtSeats,
            'mgmt_enrolled'  => $totalMgmtEnr,
            'mgmt_balance'   => $totalMgmtSeats - $totalMgmtEnr,
            'coun_seats'     => $totalCounSeats,
            'coun_enrolled'  => $totalCounEnr,
            'coun_balance'   => $totalCounSeats - $totalCounEnr,
        ];

        $yearLabel   = $year?->name ?? 'All Years';
        $generatedAt = now()->format('d M Y H:i');

        if ($format === 'excel') {
            $headings = [
                '#', 'Course',
                'Mgmt Seats', 'Mgmt Enrolled', 'Mgmt Balance',
                'Coun Seats', 'Coun Enrolled', 'Coun Balance',
                'Total Seats', 'Total Enrolled', 'Fill %',
            ];
            $rows = $intakes->values()->map(fn ($intake, $i) => [
                $i + 1,
                $intake->course?->name ?? '—',
                $intake->management_seats,
                $intake->management_enrolled,
                $intake->management_balance,
                $intake->counselling_seats,
                $intake->counselling_enrolled,
                $intake->counselling_balance,
                $intake->total_seats,
                $intake->total_enrolled,
                $intake->total_seats > 0
                    ? round($intake->total_enrolled / $intake->total_seats * 100) . '%'
                    : '0%',
            ])->toArray();

            return Excel::download(
                new ArrayExport($rows, $headings, "Course Intakes {$yearLabel}"),
                'course-intakes-' . now()->format('Ymd') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.admin.course_intakes', compact(
            'intakes', 'summary', 'yearLabel', 'generatedAt'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('course-intakes-' . now()->format('Ymd') . '.pdf');
    }

    public function destroy(string $id)
    {
        $courseIntake = CourseIntake::findOrFail(decrypt($id));
        $yearId = $courseIntake->academic_year_id;
        $courseIntake->delete();

        return redirect()->route('admin.course-intakes.index', ['year_id' => $yearId])
            ->with('success', 'Intake removed (soft-deleted). Historical leads retain their course reference.');
    }
}
