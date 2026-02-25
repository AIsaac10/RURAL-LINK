<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $post = Post::create($data);

        return response()->json([
            'message' => 'Postagem criada com sucesso',
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'price' => $post->price,
                'location' => $post->location,
                'created_at' => $post->created_at ? $post->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : null,
                'updated_at' => $post->updated_at ? $post->updated_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : null,
            ]
        ], 201);
    }

    public function index()
    {
        $posts = Post::latest()->get()->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'price' => $post->price,
                'location' => $post->location,
                'created_at' => $post->created_at ? $post->created_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : null,
                'updated_at' => $post->updated_at ? $post->updated_at->timezone('America/Sao_Paulo')->format('d/m/Y H:i') : null,
            ];
        });

        return response()->json($posts);
    }
}