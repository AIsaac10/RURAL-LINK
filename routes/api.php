<?php 

use App\Http\Controllers\Api\PostController;

Route::post('/posts', [PostController::class, 'store']);
Route::get('/posts', [PostController::class, 'index']);
?>