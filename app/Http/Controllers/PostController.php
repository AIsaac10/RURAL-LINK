<?php

namespace App\Http\Controllers;

use App\Models\publicar;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{

    $query = Post::query();

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('description', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('producer_type')) {
        $query->where('producer_type', $request->producer_type);
    }

    $resultados = $query->get();

    return response()->json($resultados, 200);
    /**
     * Show the form for creating a new resource.
     */
}
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $publicar)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $publicar)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $publicar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $publicar)
    {
        //
    }
}
