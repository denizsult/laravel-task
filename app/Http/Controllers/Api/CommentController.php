<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class CommentController extends Controller
{

    public function index(Request $request, Article $article): JsonResponse
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);
        $page = max(1, (int) $page);
        $perPage = min(50, max(1, (int) $perPage));

        $cacheKey = "comments:article:{$article->id}:page:{$page}:per_page:{$perPage}";
        $cacheTtl = config('comments.cache_ttl', 60);

        $result = Cache::tags(["article:{$article->id}"])->remember($cacheKey, $cacheTtl, function () use ($article, $page, $perPage) {
            $comments = $article->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $result = [
                'data' => CommentResource::collection($comments->items()),
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'per_page' => $comments->perPage(),
                    'total' => $comments->total(),
                    'last_page' => $comments->lastPage(),
                    'from' => $comments->firstItem(),
                    'to' => $comments->lastItem(),
                ],
            ];

            return $result;
        });

        return response()->json($result);
    }


    public function store(StoreCommentRequest $request, Article $article): JsonResponse
    {
        $user = auth()->user();

        $comment = $article->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
            'status' => 'pending',
        ]);

        return response()->json([
            'comment_id' => $comment->id,
            'message' => 'Comment submitted for moderation',
        ], 202);
    }
}
