<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPostNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public Post $post;
    public User $author;


    /**
     * Create a new notification instance.
     */
    public function __construct(
        Post $post, User $author
    )
    {
        $this->post = $post;
        $this->author = $author;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Post by {$this->author->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line($this->author->name . ' created a new post')
            ->line("Title: {$this->post->title}")
            ->action('View Post', url("/posts/{$this->post->id}"))
            ->line("Thank you for using our application!");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
