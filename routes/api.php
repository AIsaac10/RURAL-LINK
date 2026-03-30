<?php
 
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FavoriteController;
 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
 
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/search', [SearchController::class, 'index']);
 
Route::post('/posts', [PostController::class, 'store'])
    ->middleware(['auth:sanctum']);
 
Route::put('/profile', [AuthController::class, 'updateProfile'])
    ->middleware(['token.from.body', 'auth:sanctum']);
 
Route::middleware('auth:sanctum')->group(function () {
 
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::get('/my-posts', [PostController::class, 'myPosts']); // ← novo
 
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
 
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
});