<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $courses = Course::query();
        if ($request->has('search')) {
            $courses = $courses->where('name','like','%'. $request->get('search') .'%');
        }

        $courses = $courses->orderByDesc('id')->paginate(10);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $courses
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file_path' => 'required|string',
        ]);

        $course = Course::create($validated);

        return response()->json([
            'message' => 'Course created successfully',
            'course' => $course
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::where('id',$id)->first();

        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ],404);
        }

        return response()->json([
            'course' => $course
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $course = Course::where('id',$id)->first();

        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ],404);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'file_path' => 'string',
        ]);
        $course->update($validated);

        return response()->json([
            'message' => 'Course updated successfully',
            'course' => $course
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $course = Course::where('id',$id)->first();

        if (!$course) {
            return response()->json([
                'message' => 'Course not found',
            ],404);
        }

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully',
        ], 200);
    }
}
