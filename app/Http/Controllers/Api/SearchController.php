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
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('price', 'like', $searchTerm)
                  ->orWhere('stock', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm);
            });
        }

        $resultados = $query->with('user')->latest()->get()->map(function ($post) {
            return [
                'id'          => (string) $post->id,
                'user_id'     => (string) $post->user_id,
                'user_name'   => $post->user->name ?? null,
                'user_phone'  => $post->user->phone ?? null,
                'title'       => $post->title,
                'description' => $post->description,
                'price'       => $post->price ? (string) $post->price : '0',
                'location'    => $post->location,
                'stock'       => $post->stock ? (string) $post->stock : '0',
                'seals'       => $post->seals ? json_decode($post->seals) : [],
                'image'       => $post->image ?? null,
                'created_at'  => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ];
        });

        return response()->json($resultados, 200);
    }
}