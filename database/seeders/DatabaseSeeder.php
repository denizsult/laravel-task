<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 2 users as required
        $user1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $user2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        // Create 2 articles as required
        $article1 = Article::factory()->create([
            'title' => 'First Article',
            'body' => 'This is the content of the first article. It contains some interesting information about Laravel development and best practices.',
        ]);

        $article2 = Article::factory()->create([
            'title' => 'Second Article',
            'body' => 'This is the content of the second article. It discusses advanced topics in web development and modern frameworks.',
        ]);

        // Create some comments for testing
        $article1->comments()->create([
            'user_id' => $user1->id,
            'content' => 'Great article! Very informative.',
            'status' => 'published',
        ]);

        $article1->comments()->create([
            'user_id' => $user2->id,
            'content' => 'I found this very helpful, thanks for sharing.',
            'status' => 'published',
        ]);

        $article2->comments()->create([
            'user_id' => $user1->id,
            'content' => 'This needs more explanation on the topic.',
            'status' => 'pending',
        ]);

        $article2->comments()->create([
            'user_id' => $user2->id,
            'content' => 'This is spam content that should be rejected.',
            'status' => 'rejected',
        ]);
    }
}
