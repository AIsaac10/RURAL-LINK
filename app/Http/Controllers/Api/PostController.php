<?php
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
 
class PostController extends Controller
{
    private function parseSeals($value): ?string
    {
        if (empty($value)) return null;
 
        if (is_array($value)) {
            return json_encode(array_values($value));
        }
 
        if (is_string($value)) {
            $clean = trim($value, '[]');
            $items = array_values(array_filter(array_map('trim', explode(',', $clean))));
            return !empty($items) ? json_encode($items) : null;
        }
 
        return null;
    }
 
    private function formatSeals(?string $seals): array
    {
        if (!$seals) return [];
 
        $map = [
            'autonomo'    => 'Autônomo',
            'empresa'     => 'Empresa',
            'cooperativa' => 'Cooperativa',
            'organico'    => 'Orgânico',
        ];
 
        $items = json_decode($seals, true) ?? [];
        return array_map(fn($s) => $map[$s] ?? $s, $items);
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'nullable|string',
            'location'    => 'nullable|string',
            'stock'       => 'nullable|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id'     => $request->user()->id,
            'title'       => $request->title,
            'description' => $request->description,
            'price'       => $request->price ?? null,
            'location'    => $request->location ?? null,
            'stock'       => $request->stock ?? null,
            'seals'       => $this->parseSeals($request->input('seals')),
            'image'       => $imagePath,
        ]);
 
        return response()->json([
            'message' => 'Postagem criada com sucesso',
            'post' => [
                'id'          => $post->id,
                'user_id'     => $post->user_id,
                'title'       => $post->title,
                'description' => $post->description,
                'price'       => $post->price,
                'location'    => $post->location,
                'stock'       => $post->stock,
                'seals'       => $this->formatSeals($post->seals),
                'image' => $post->image ? asset('storage/' . $post->image) : null,
                'created_at'  => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'updated_at'  => $post->updated_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ]
        ], 201);
    }
 
    public function index()
    {
        $posts = Post::with('user')->latest()->get()->map(function ($post) {
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
                'seals'       => $this->formatSeals($post->seals),
                'created_at'  => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
                'updated_at'  => $post->updated_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
            ];
        });
 
        return response()->json($posts);
    }
 
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
 
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
 
        $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price'       => 'nullable|string',
            'location'    => 'nullable|string',
            'stock'       => 'nullable|numeric',
        ]);
 
        $data = $request->only(['title', 'description', 'price', 'location', 'stock']);
 
        if ($request->has('seals')) {
            $data['seals'] = $this->parseSeals($request->input('seals'));
        }
 
        $post->update($data);
 
        return response()->json([
            'message' => 'Postagem atualizada com sucesso',
            'post'    => $post
        ]);
    }
 
    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);
 
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
 
        $post->delete();
 
        return response()->json(['message' => 'Postagem deletada com sucesso']);
    }
}