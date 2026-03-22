<?php
 
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\FavoriteController;
 
/*
|--------------------------------------------------------------------------
| Rotas públicas (não precisam de login)
|--------------------------------------------------------------------------
*/
 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
 
Route::get('/posts', [PostController::class, 'index']); 
 
Route::get('/posts/search', [SearchController::class, 'index']);
 
// criar post — token vem do body via middleware
Route::post('/posts', [PostController::class, 'store'])
    ->middleware(['token.from.body', 'auth:sanctum']);
 
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
 
    // atualizar post
    Route::put('/posts/{id}', [PostController::class, 'update']);
 
    // deletar post
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    
    // --- ROTAS DE FAVORITOS ---
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
});