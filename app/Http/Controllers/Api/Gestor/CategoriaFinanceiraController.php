<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\CategoriaFinanceira;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaFinanceiraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categorias = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->where('ativo', true)
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return response()->json($categorias);
    }

    public function porTipo(Request $request, string $tipo): JsonResponse
    {
        $categorias = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->where('tipo', $tipo)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json($categorias);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|in:entrada,saida',
            'nome' => 'required|string|max:100',
            'cor' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
        ]);

        $categoria = CategoriaFinanceira::create([
            'academia_id' => $request->user()->academia_id,
            ...$validated,
        ]);

        return response()->json($categoria, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $categoria = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($categoria->sistema) {
            return response()->json(['message' => 'Categorias do sistema não podem ser alteradas.'], 403);
        }

        $validated = $request->validate([
            'nome' => 'sometimes|string|max:100',
            'cor' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
            'ativo' => 'sometimes|boolean',
        ]);

        $categoria->update($validated);

        return response()->json($categoria);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $categoria = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($categoria->sistema) {
            return response()->json(['message' => 'Categorias do sistema não podem ser excluídas.'], 403);
        }

        if ($categoria->movimentacoes()->exists()) {
            return response()->json(['message' => 'Categoria possui movimentações vinculadas.'], 422);
        }

        $categoria->delete();

        return response()->json(null, 204);
    }
}
