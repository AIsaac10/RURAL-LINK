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
            'stock' => 'nullable|numeric',
            'producer_type' => 'nullable|string',
            'seals' => 'nullable|array',
            'seals.*' => 'in:autonomo,cooperativa,empresa,organico',
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'] ?? null,
            'location' => $data['location'] ?? null,
            'stock' => $data['stock'] ?? null,
            'producer_type' => $data['producer_type'] ?? null,
            'seals' => isset($data['seals']) ? json_encode($data['seals']) : null,
        ]);

        return response()->json([
            'message' => 'Postagem criada com sucesso',
            'post' => [
                'id' => $post->id,
                'user_id' => $post->user_id,
                'title' => $post->title,
                'description' => $post->description,
                'price' => $post->price,
                'location' => $post->location,
                'stock' => $post->stock,
                'producer_type' => $post->producer_type,
                'seals' => $post->seals ? json_decode($post->seals) : [],
                'created_at' => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'updated_at' => $post->updated_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ]
        ], 201);
    }

    public function index()
    {
        $posts = Post::with('user')->latest()->get()->map(function ($post) {
            return [
                'id' => $post->id,
                'user_id' => $post->user_id,
                'user_name' => $post->user->name ?? null,
                'user_phone' => $post->user->phone ?? null,
                'title' => $post->title,
                'description' => $post->description,
                'price' => $post->price,
                'location' => $post->location,
                'stock' => $post->stock,
                'producer_type' => $post->producer_type,
                'seals' => $post->seals ? json_decode($post->seals) : [],
                'created_at' => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'updated_at' => $post->updated_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ];
        });

        return response()->json($posts);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Não autorizado'
            ], 403);
        }

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'nullable|string',
            'location' => 'nullable|string',
            'stock' => 'nullable|numeric',
            'producer_type' => 'nullable|string',
            'seals' => 'nullable|array',
            'seals.*' => 'in:autonomo,cooperativa,empresa,organico',
        ]);

        if(isset($data['seals'])){
        $data['seals'] = json_encode($data['seals']);
        }

        $post->update($data);

        return response()->json([
            'message' => 'Postagem atualizada com sucesso',
            'post' => $post
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Não autorizado'
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Postagem deletada com sucesso'
        ]);
    }
}