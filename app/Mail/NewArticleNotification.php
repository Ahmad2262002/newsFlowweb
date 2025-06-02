<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Article;
use App\Models\User;

class NewArticleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $article;
    public $user;
    public $unsubscribeUrl;

    public function __construct(Article $article, User $user)
    {
        $this->article = $article;
        $this->user = $user;
        $this->unsubscribeUrl = url('/unsubscribe/' . $user->user_id);
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('New Article: ' . $this->article->title)
                    ->view('emails.new_article');
    }
}