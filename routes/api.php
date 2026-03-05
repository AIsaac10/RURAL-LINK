<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;

/*
|--------------------------------------------------------------------------
| Rotas públicas (não precisam de login)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/posts', [PostController::class, 'index']); // listar posts


/*
|--------------------------------------------------------------------------
| Rotas protegidas (precisam de token)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // usuário logado
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // criar post
    Route::post('/posts', [PostController::class, 'store']);

    // atualizar post
    Route::put('/posts/{id}', [PostController::class, 'update']);

    // deletar post
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});