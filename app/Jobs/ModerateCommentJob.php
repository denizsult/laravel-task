<?php

namespace App\Jobs;

use App\Models\Comment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ModerateCommentJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function backoff(): array
    {
        $onemins = 60;
        $threemins = 180;
        $fivemins = 300;
        return [$onemins, $threemins, $fivemins];
    }

    public function __construct(
        public Comment $comment
    ) {}

    public function handle(): void
    {
        if ($this->comment->status !== 'pending') {
            Log::channel('comment_moderation')->info('Comment moderation skipped - not pending', [
                'comment_id' => $this->comment->id,
                'status' => $this->comment->status,
            ]);
            return;
        }

        $containsBannedWords = $this->hasBannedWords();
        $newStatus = $containsBannedWords ? 'rejected' : 'published';
        $this->comment->update(['status' => $newStatus]);
        $this->invalidateCommentCache();
    }


    private function hasBannedWords(): bool
    {
        $bannedWords = config('comments.banned_words', []);

        if (!is_array($bannedWords)) {
            Log::channel('comment_moderation')->warning('banned_words is not an array', [
                'value' => $bannedWords,
                'type' => gettype($bannedWords)
            ]);
            $bannedWords = [];
        }

        $content = Str::lower($this->comment->content);

        foreach ($bannedWords as $word) {
            if (Str::contains($content, Str::lower($word))) {
                return true;
            }
        }

        return false;
    }


    private function invalidateCommentCache(): void
    {
        $articleId = $this->comment->article_id;

        Cache::tags(["article:{$articleId}"])->flush();        
    }
}
