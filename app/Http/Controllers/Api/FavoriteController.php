<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
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
 
        /** @var \App\Models\User $user */
        $user = $request->user();
        
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
        /** @var \App\Models\User $user */
        $user = $request->user();
        
        return response()->json($user->favorites()->latest()->get());
    }
}