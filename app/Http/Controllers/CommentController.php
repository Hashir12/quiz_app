<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'parent_id' => 'nullable|exists:comments,id',
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $request->post_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Comment added successfully', 'comment' => new CommentResource($comment)],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::where('id', $id)->where('user_id',Auth::id())->with('replies')->first();
        if (!$comment) {
            return response()->json('Comment not found', 404);
        }
        return response()->json($comment->load('replies', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = Comment::where('id', $id)->where('user_id',Auth::id())->with('replies')->first();
        if (!$comment) {
            return response()->json('Comment not found', 404);
        }
        $comment->content = $request->content;
        $comment->save();

        return response()->json(['message' => 'Comment updated successfully', 'comment' => $comment],200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = Comment::where('id', $id)->where('user_id',Auth::id())->with('replies')->first();
        if (!$comment) {
            return response()->json('Comment not found', 404);
        }
        $comment->delete();
        return response()->json('Comment deleted successfully', 404);
    }
}
