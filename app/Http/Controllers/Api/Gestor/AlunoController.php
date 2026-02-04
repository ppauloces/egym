<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Services\MensalidadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlunoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $academiaId = $request->user()->academia_id;

        $query = Aluno::with('plano:id,nome,valor')
            ->where('academia_id', $academiaId);

        // Filtro por status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Busca por nome ou CPF
        if ($request->has('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        $alunos = $query->orderBy('nome')->paginate(20);

        return response()->json($alunos);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date',
            'data_matricula' => 'nullable|date',
            'matricula_retroativa' => 'nullable|boolean',
            'data_proxima_mensalidade' => 'nullable|date|required_if:matricula_retroativa,true',
            'plano_id' => 'nullable|exists:planos,id',
            'status' => 'nullable|in:ativo,inativo,bloqueado',
        ]);

        $validated['academia_id'] = $request->user()->academia_id;
        $validated['status'] = $validated['status'] ?? 'ativo';
        
        // Se não informar data de matrícula, usa hoje
        if (isset($validated['plano_id']) && !isset($validated['data_matricula'])) {
            $validated['data_matricula'] = now();
        }

        $aluno = Aluno::create($validated);

        // Gera a primeira mensalidade se tiver plano
        if ($aluno->plano_id && $aluno->data_matricula) {
            $mensalidadeService = new MensalidadeService();
            $mensalidadeService->gerarPrimeiraMensalidade($aluno);
        }

        return response()->json([
            'message' => 'Aluno cadastrado com sucesso.',
            'aluno' => $aluno->load('plano:id,nome'),
        ], 201);
    }

    public function show(Request $request, Aluno $aluno): JsonResponse
    {
        if ($aluno->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        return response()->json($aluno->load(['plano', 'mensalidades' => function ($q) {
            $q->orderBy('data_vencimento', 'desc')->limit(12);
        }]));
    }

    public function update(Request $request, Aluno $aluno): JsonResponse
    {
        if ($aluno->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'data_nascimento' => 'nullable|date',
            'data_matricula' => 'nullable|date',
            'matricula_retroativa' => 'nullable|boolean',
            'data_proxima_mensalidade' => 'nullable|date|required_if:matricula_retroativa,true',
            'plano_id' => 'nullable|exists:planos,id',
            'status' => 'nullable|in:ativo,inativo,bloqueado',
        ]);

        // Se está adicionando um plano pela primeira vez, gera mensalidade
        $geraMensalidade = !$aluno->plano_id && isset($validated['plano_id']);
        
        if ($geraMensalidade && !isset($validated['data_matricula']) && !$aluno->data_matricula) {
            $validated['data_matricula'] = now();
        }

        $aluno->update($validated);

        // Gera primeira mensalidade se necessário
        if ($geraMensalidade && $aluno->fresh()->plano_id && $aluno->fresh()->data_matricula) {
            $mensalidadeService = new MensalidadeService();
            $mensalidadeService->gerarPrimeiraMensalidade($aluno->fresh());
        }

        return response()->json([
            'message' => 'Aluno atualizado com sucesso.',
            'aluno' => $aluno->fresh()->load('plano:id,nome'),
        ]);
    }

    public function destroy(Request $request, Aluno $aluno): JsonResponse
    {
        if ($aluno->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $aluno->delete();

        return response()->json(['message' => 'Aluno removido com sucesso.']);
    }

    public function toggleStatus(Request $request, Aluno $aluno): JsonResponse
    {
        if ($aluno->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $novoStatus = $aluno->status === 'ativo' ? 'inativo' : 'ativo';
        $aluno->update(['status' => $novoStatus]);

        return response()->json([
            'message' => 'Status atualizado com sucesso.',
            'aluno' => $aluno->fresh()->load('plano:id,nome'),
        ]);
    }
}
