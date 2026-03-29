<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    // Método central para padronizar o que o App recebe
    private function transformPost(Post $post): array
    {
        return [
            'id'          => $post->id,
            'user_id'     => $post->user_id,
            'user_name'   => $post->user->name ?? null,
            'user_phone'  => $post->user->phone ?? null,
            'title'       => $post->title,
            'description' => $post->description,
            'price'       => $post->price ? (float) $post->price : 0.0,
            'location'    => $post->location,
            'stock'       => $post->stock ? (float) $post->stock : 0.0,
            'seals'       => $this->formatSeals($post->seals),
            'image'       => $post->image ? asset('storage/' . $post->image) : null,
            'created_at'  => $post->created_at?->timezone('America/Sao_Paulo')->format('d/m/Y H:i'),
        ];
    }

    private function parseSeals($value): ?string
    {
        if (empty($value)) return null;
        if (is_array($value)) return json_encode(array_values($value));
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
        $map = ['autonomo' => 'Autônomo', 'empresa' => 'Empresa', 'cooperativa' => 'Cooperativa', 'organico' => 'Orgânico'];
        $items = json_decode($seals, true) ?? [];
        return array_map(fn($s) => $map[$s] ?? $s, $items);
    }

    private function saveUploadedImage($file): ?string
    {
        return $file ? $file->store('posts', 'public') : null;
    }

    private function saveBase64Image(?string $base64): ?string
    {
        if (!$base64) return null;
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $decoded = base64_decode($base64);
        if (!$decoded) return null;
        $filename = 'posts/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($filename, $decoded);
        return $filename;
    }

    public function index()
    {
        $posts = Post::with('user')->latest()->get()->map(fn($p) => $this->transformPost($p));
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|file|image|max:5120',
            'image_base64' => 'nullable|string',
        ]);

        $imagePath = $request->hasFile('image') 
            ? $this->saveUploadedImage($request->file('image')) 
            : $this->saveBase64Image($request->input('image_base64'));

        $post = Post::create(array_merge($request->all(), [
            'user_id' => $request->user()->id,
            'image'   => $imagePath,
            'seals'   => $this->parseSeals($request->input('seals'))
        ]));

        return response()->json(['message' => 'Criado!', 'post' => $this->transformPost($post)], 201);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== $request->user()->id) return response()->json(['message' => 'Não autorizado'], 403);

        $data = $request->only(['title', 'description', 'price', 'location', 'stock']);
        
        if ($request->hasFile('image') || $request->filled('image_base64')) {
            if ($post->image) Storage::disk('public')->delete($post->image);
            $data['image'] = $request->hasFile('image') 
                ? $this->saveUploadedImage($request->file('image')) 
                : $this->saveBase64Image($request->input('image_base64'));
        }

        if ($request->has('seals')) $data['seals'] = $this->parseSeals($request->input('seals'));

        $post->update($data);
        return response()->json(['message' => 'Atualizado!', 'post' => $this->transformPost($post)]);
    }

    public function destroy(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== $request->user()->id) return response()->json(['message' => 'Não autorizado'], 403);
        if ($post->image) Storage::disk('public')->delete($post->image);
        $post->delete();
        return response()->json(['message' => 'Deletado!']);
    }
}