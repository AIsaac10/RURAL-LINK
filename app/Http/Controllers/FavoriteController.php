<?php

namespace App\Http\Controllers;

use App\Models\Post; 
use App\Models\User; 
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id'
        ]);

        // A MÁGICA PARA TIRAR A LINHA VERMELHA ESTÁ AQUI:
        /** @var \App\Models\User $user */
        $user = $request->user(); // Mudei para $request->user() que o VS Code lê melhor
        
        $status = $user->favorites()->toggle($request->post_id);

        $isFavorited = count($status['attached']) > 0;

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? 'Salvo com sucesso!' : 'Removido dos salvos.'
        ]);
    }

    public function index(Request $request)
    {
        // A MESMA MÁGICA AQUI:
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        return response()->json($user->favorites()->latest()->get());
    }
}