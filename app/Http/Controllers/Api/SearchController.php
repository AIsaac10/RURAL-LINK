<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Importante para gerar URLs

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query()->with('user');

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                ->orWhere('description', 'like', $searchTerm)
                ->orWhere('location', 'like', $searchTerm);
                
                // Se a busca for numérica, pesquisamos em preço e estoque
                if (is_numeric(str_replace(['.', ','], '', $searchTerm))) {
                    $q->orWhere('price', 'like', $searchTerm)
                    ->orWhere('stock', 'like', $searchTerm);
                }
            });
        }

        if ($request->filled('seals')) {
            $query->where('seals', 'like', '%' . $request->seals . '%');
        }

        $resultados = $query->latest()->get()->map(function ($post) {
            return [
                'id'          => (int) $post->id,
                'user_id'     => (int) $post->user_id,
                'user_name'   => $post->user->name ?? 'Usuário',
                'user_phone'  => $post->user->phone ?? '',
                'title'       => $post->title,
                'description' => $post->description,
                'price'       => $post->price ? (double) $post->price : 0.0,
                'location'    => $post->location,
                'stock'       => $post->stock ? (int) $post->stock : 0,
                'seals'       => $post->seals ? json_decode($post->seals) : [],
                'image'       => $post->image ? asset('storage/' . $post->image) : null,
                'created_at'  => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ];
        });

        return response()->json($resultados, 200);
    }
}