<?php

namespace App\Listeners;

use App\Events\NewPostCreatedEvent;
use App\Models\Post;
use App\Models\User;
use App\Notifications\NewPostNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NewPostListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewPostCreatedEvent $event): void
    {
        $post = $event->post;
        $author = $event->author;

        //Get all users who follow the author
        $favorites=User::whereHas('favoritables',function($q) use($author){
            $q->where('favorite_id',$author->id)
                ->where('favorite_type',User::class);
        })->get();

        foreach ($favorites as $favorite){
            $favorite->notify(new NewPostNotification($post,$author));
        }
    }
}
