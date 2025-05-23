<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $post = Post::with([
            'comments' => function ($query) {
                $query->with(['user', 'replies' => function ($replyQuery) {
                    $replyQuery->with('user');
                }])->orderByDesc('id');
            },
            'user'
        ]);

        if($request->has('search')) {
            $post = $post->where('content','like', '%' . $request->get('search') . '%');
        }

        $post = $post->orderByDesc('id')->paginate(10);

        return response()->json([
            'status' => true,
            'posts' => PostResource::collection($post),
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $postData = ['content' => $request['content'], 'user_id' => Auth::id()];
        $post = Post::create($postData);

        return response()->json([
            'status' => true,
            'post' => new PostResource($post),
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $post = Post::with([
            'comments' => function ($query) {
                $query->with(['user', 'replies' => function ($replyQuery) {
                    $replyQuery->with('user');
                }])->orderByDesc('id');
            },
            'user'
        ])->where('id', $id)->first();

        if (!$post) {
            $response['status'] = false;
            $response['message'] = 'Post not found';
            $responseCode= 404;
        } else {
            $response['status'] = true;
            $response['post'] = new PostResource($post);
            $responseCode= 200;
        }

        return response()->json($response,$responseCode);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        $post = Post::where('id', $id)->where('user_id',Auth::id())->first();
        if (!$post) {
            $response['status'] = false;
            $response['message'] = 'Post not found';
            $responseCode= 404;
        } else {
            $post->content = $request['content'];
            $post->save();
            $response['status'] = true;
            $response['post'] = new PostResource($post->load('comments'));
            $responseCode= 200;
        }

        return response()->json($response,$responseCode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::where('id', $id)->where('user_id',Auth::id())->first();
        if (!$post) {
            $response['status'] = false;
            $response['message'] = 'Post not found';
            $responseCode= 404;
        } else {
            $post->delete();
            $response['status'] = true;
            $response['message'] = 'Post deleted successfully';
            $responseCode= 200;
        }
        return response()->json($response,$responseCode);
    }
}
