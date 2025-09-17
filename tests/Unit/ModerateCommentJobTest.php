<?php

namespace Tests\Unit;

use App\Jobs\ModerateCommentJob;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ModerateCommentJobTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Article $article;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake events to prevent CommentCreating from interfering
        Event::fake();
        
        $this->user = User::factory()->create();
        $this->article = Article::factory()->create();
    }

    public function test_job_skips_non_pending_comments(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'published',
            'content' => 'Already published comment'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment status should remain unchanged
        $this->assertEquals('published', $comment->fresh()->status);
    }

    public function test_job_publishes_comment_without_banned_words(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'This is a clean and appropriate comment'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment should be published
        $this->assertEquals('published', $comment->fresh()->status);
    }

    public function test_job_rejects_comment_with_banned_words(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'This comment contains spam and abuse'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment should be rejected
        $this->assertEquals('rejected', $comment->fresh()->status);
    }

    public function test_job_handles_case_insensitive_banned_words(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'This comment contains SPAM and Abuse'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment should be rejected due to case-insensitive matching
        $this->assertEquals('rejected', $comment->fresh()->status);
    }

    public function test_job_handles_partial_word_matches(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'This comment contains spammy content'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment should be rejected because "spam" is contained in "spammy"
        $this->assertEquals('rejected', $comment->fresh()->status);
    }

    public function test_job_handles_empty_banned_words_config(): void
    {
        // Override banned words config to be empty
        config(['comments.banned_words' => []]);

        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'This comment contains spam and abuse'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment should be published since no banned words are configured
        $this->assertEquals('published', $comment->fresh()->status);
    }

    public function test_job_handles_invalid_banned_words_config(): void
    {
        // Override banned words config to be invalid
        config(['comments.banned_words' => 'not-an-array']);

        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'This comment contains spam'
        ]);

        $job = new ModerateCommentJob($comment);
        $job->handle();

        // Comment should be published since banned words config is invalid
        $this->assertEquals('published', $comment->fresh()->status);
    }

    public function test_job_invalidates_correct_article_cache(): void
    {
        // Create comments for different articles
        $anotherArticle = Article::factory()->create();
        
        $comment1 = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'Comment for first article'
        ]);

        $comment2 = Comment::factory()->create([
            'article_id' => $anotherArticle->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'Comment for second article'
        ]);

        // Process comments
        $job1 = new ModerateCommentJob($comment1);
        $job1->handle();

        $job2 = new ModerateCommentJob($comment2);
        $job2->handle();

        // Verify comments were processed
        $this->assertEquals('published', $comment1->fresh()->status);
        $this->assertEquals('published', $comment2->fresh()->status);
    }

    public function test_job_with_multiple_banned_words(): void
    {
        $testCases = [
            ['content' => 'This is spam', 'expected' => 'rejected'],
            ['content' => 'This is abuse', 'expected' => 'rejected'],
            ['content' => 'This is inappropriate', 'expected' => 'rejected'],
            ['content' => 'This is offensive', 'expected' => 'rejected'],
            ['content' => 'This is a normal comment', 'expected' => 'published'],
        ];

        foreach ($testCases as $testCase) {
            $comment = Comment::factory()->create([
                'article_id' => $this->article->id,
                'user_id' => $this->user->id,
                'status' => 'pending',
                'content' => $testCase['content']
            ]);

            $job = new ModerateCommentJob($comment);
            $job->handle();

            $this->assertEquals(
                $testCase['expected'], 
                $comment->fresh()->status,
                "Failed for content: {$testCase['content']}"
            );
        }
    }

    public function test_job_queue_configuration(): void
    {
        $comment = Comment::factory()->create([
            'article_id' => $this->article->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'content' => 'Test comment'
        ]);

        $job = new ModerateCommentJob($comment);

        // Test job configuration
        $this->assertEquals(3, $job->tries);
        $this->assertEquals([60, 180, 300], $job->backoff());
    }
}
