<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
 
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();
 
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('price', 'like', '%' . $request->search . '%')
                  ->orWhere('stock', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%')
                  ->orWhere('seals', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('seals')) {
            $query->where('seals', 'like', '%' . $request->seals . '%');
        }
 
        $resultados = $query->with('user')->get()->map(function ($post) {
            return [
                'id'          => $post->id,
                'user_id'     => $post->user_id,
                'user_name'   => $post->user->name ?? null,
                'user_phone'  => $post->user->phone ?? null,
                'title'       => $post->title,
                'description' => $post->description,
                'price'       => $post->price,
                'location'    => $post->location,
                'stock'       => $post->stock,
                'seals'       => $post->seals ? json_decode($post->seals) : [],
                'created_at'  => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'updated_at'  => $post->updated_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ];
        });
 
        return response()->json($resultados, 200);
    }
}