<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeedQuestion;
use Illuminate\Support\Facades\Auth;

class FeedQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(FeedQuestion::with('user', 'answers.user')->orderByDesc('id')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'question' => $request->question
        ];

        $question = FeedQuestion::create($data);

        return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $question = FeedQuestion::with('user', 'answers')->find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $question = FeedQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $request->validate([
            'question' => 'required|string|max:255',
        ]);

        $question->update($request->all());

        return response()->json($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = FeedQuestion::find($id);

        if (!$question) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully']);
    }
}
