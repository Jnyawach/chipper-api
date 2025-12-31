<?php

namespace App\Http\Controllers;

use App\Events\NewPostCreatedEvent;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\DestroyPostRequest;
use Illuminate\Support\Facades\Storage;

/**
 * @group Posts
 *
 * API endpoints for managing posts
 */
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->orderByDesc('created_at')->get();
        return PostResource::collection($posts);
    }

    public function store(CreatePostRequest $request)
    {
        $user = $request->user();

        // Create a new post
        $post = Post::create([
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'user_id' => $user->id,
        ]);
        if($request->hasFile('image')){
            $postImage= $request->file('image');
            $imageName=time().'_'.$postImage->getClientOriginalName();
            $postImage->storeAs('posts', $imageName, 'public');
            $post->image = $imageName;
            $post->save();

        }

        NewPostCreatedEvent::dispatch($post,$user);

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update([
            'title' => $request->input('title'),
            'body' => $request->input('body'),
        ]);

        if($request->hasFile('image')){
            $postImage= $request->file('image');

            //delete old image if exists
            if($post->image && Storage::disk('public')->exists('posts/'.$post->image)){
                Storage::disk('public')->delete('posts/'.$post->image);
            }
            $imageName=time().'_'.$postImage->getClientOriginalName();
            $postImage->storeAs('posts', $imageName, 'public');
            $post->image = $imageName;
            $post->save();

        }

        return new PostResource($post);
    }

    public function destroy(DestroyPostRequest $request, Post $post)
    {
        $post->delete();

        return response()->noContent();
    }
}
