<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/* Auth routes */

 
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});



/* Articles routes */
Route::prefix('articles')->group(function () {
    Route::get('/{article}', [ArticleController::class, 'show']);
    Route::get('/{article}/comments', [CommentController::class, 'index']);
    Route::post('/{article}/comments', [CommentController::class, 'store'])->middleware(['throttle:10,1', 'auth:sanctum']);
});
