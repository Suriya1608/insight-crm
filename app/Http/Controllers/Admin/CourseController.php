<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::orderBy('sort_order')->orderBy('name');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $courses = $query->paginate(15)->withQueryString();

        $totalCourses   = Course::count();
        $activeCourses  = Course::where('is_active', true)->count();
        $inactiveCourses = Course::where('is_active', false)->count();
        $withCode       = Course::whereNotNull('code')->where('code', '!=', '')->count();

        return view('admin.courses.index', compact(
            'courses', 'totalCourses', 'activeCourses', 'inactiveCourses', 'withCode'
        ));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $request->input('sort_order', 0);

        Course::create($data);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['sort_order'] = $request->input('sort_order', 0);

        $course->update($data);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function toggleStatus(Course $course)
    {
        $course->update(['is_active' => !$course->is_active]);

        return back()->with('success', '"' . $course->name . '" marked as ' . ($course->is_active ? 'Active' : 'Inactive') . '.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted.');
    }
}
