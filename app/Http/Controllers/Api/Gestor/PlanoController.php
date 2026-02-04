<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Plano;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $academiaId = $request->user()->academia_id;

        $planos = Plano::where('academia_id', $academiaId)
            ->orderBy('nome')
            ->get();

        return response()->json($planos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0',
            'duracao_dias' => 'required|integer|min:1',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $validated['academia_id'] = $request->user()->academia_id;
        $validated['ativo'] = $validated['ativo'] ?? true;

        $plano = Plano::create($validated);

        return response()->json([
            'message' => 'Plano cadastrado com sucesso.',
            'plano' => $plano,
        ], 201);
    }

    public function show(Request $request, Plano $plano): JsonResponse
    {
        if ($plano->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        return response()->json($plano);
    }

    public function update(Request $request, Plano $plano): JsonResponse
    {
        if ($plano->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'valor' => 'sometimes|required|numeric|min:0',
            'duracao_dias' => 'sometimes|required|integer|min:1',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $plano->update($validated);

        return response()->json([
            'message' => 'Plano atualizado com sucesso.',
            'plano' => $plano->fresh(),
        ]);
    }

    public function destroy(Request $request, Plano $plano): JsonResponse
    {
        if ($plano->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // Verifica se há alunos usando este plano
        if ($plano->alunos()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível excluir um plano que possui alunos vinculados.',
            ], 422);
        }

        $plano->delete();

        return response()->json(['message' => 'Plano removido com sucesso.']);
    }
}
