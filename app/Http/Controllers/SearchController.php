<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Realiza a busca dos posts.
     */
    public function index(Request $request)
    {
        $query = Post::query();

        // Filtro pela barra de pesquisa (título ou descrição)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por tipo de produtor
        if ($request->filled('producer_type')) {
            $query->where('producer_type', $request->producer_type);
        }

        $resultados = $query->get();

        return response()->json($resultados, 200);
    }
}