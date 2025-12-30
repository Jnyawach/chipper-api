<?php

namespace App\Http\Controllers;

use App\Http\Resources\FavoriteResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\CreateFavoriteRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @group Favorites
 *
 * API endpoints for managing favorites
 */
class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites;
        return FavoriteResource::collection($favorites);
    }

    public function store(CreateFavoriteRequest $request, Post $post)
    {
        $request->user()->favorites()
            ->create([
                'favorite_id' => $post->id,
                'favorite_type' => Post::class,

            ]);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, Post $post)
    {
        $favorite = $request->user()->favorites()
            ->where('favorite_id', $post->id)
            ->where('favorite_type', Post::class)
            ->firstOrFail();

        $favorite->delete();

        return response()->noContent();
    }

    public function favoriteUser(CreateFavoriteRequest $request,string $userId)
    {
        if($userId==Auth::id()){
        return response()->json(['message' => 'You cannot favorite yourself.'], Response::HTTP_BAD_REQUEST);
        }

        $request->user()->favorites()->create([
            'favorite_id' => $userId,
            'favorite_type' => User::class,
        ]);

        return response()->noContent(Response::HTTP_CREATED);

    }

    public function unFavoriteUser(string $userId)
    {
        $favorite = Auth::user()->favorites()
            ->where('favorite_id', $userId)
            ->where('favorite_type', User::class)
            ->firstOrFail();

        $favorite->delete();

        return response()->noContent();
    }
}
