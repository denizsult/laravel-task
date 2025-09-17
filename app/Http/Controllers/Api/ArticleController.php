<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json([
            'data' => new ArticleResource($article),
        ]);
    }
}
