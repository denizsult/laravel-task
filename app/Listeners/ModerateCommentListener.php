<?php

namespace App\Listeners;

use App\Events\CommentCreating;
use App\Jobs\ModerateCommentJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ModerateCommentListener implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(CommentCreating $event): void
    {
        ModerateCommentJob::dispatch($event->comment);
    }
}
