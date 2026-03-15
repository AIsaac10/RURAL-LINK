<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\FavoriteController; // <-- Adicionado aqui!

/*
|--------------------------------------------------------------------------
| Rotas públicas (não precisam de login)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/posts', [PostController::class, 'index']); 

Route::get('/posts/search', [SearchController::class, 'index']);


/*
|--------------------------------------------------------------------------
| Rotas protegidas (precisam de token)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // editar perfil
    Route::put('/profile', [AuthController::class, 'updateProfile']);

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
    
    // --- ROTAS DE FAVORITOS ---
    
    // favoritar ou desfavoritar um post
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    
    // listar todos os favoritos do usuário logado
    Route::get('/favorites', [FavoriteController::class, 'index']);
});