<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeedAnswer;
use Illuminate\Support\Facades\Auth;

class FeedAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(FeedAnswer::with('user', 'question')->orderByDesc('id')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:feed_questions,id',
            'answer' => 'required|string|max:255',
        ]);

        $data =[
            'question_id' => $request->get('question_id'),
            'answer' => $request->get('answer'),
            'user_id' => Auth::id(),
        ];

        $answer = FeedAnswer::create($data);

        return response()->json($answer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $answer = FeedAnswer::with('user', 'question')->find($id);

        if (!$answer) {
            return response()->json(['message' => 'Answer not found'], 404);
        }

        return response()->json($answer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $answer = FeedAnswer::find($id);

        if (!$answer) {
            return response()->json(['message' => 'Answer not found'], 404);
        }

        $request->validate([
            'answer' => 'required|string|max:255',
        ]);

        $answer->update($request->all());

        return response()->json($answer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $answer = FeedAnswer::find($id);

        if (!$answer) {
            return response()->json(['message' => 'Answer not found'], 404);
        }

        $answer->delete();

        return response()->json(['message' => 'Answer deleted successfully']);
    }
}
