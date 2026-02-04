<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Mensalidade;
use App\Services\MensalidadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MensalidadeController extends Controller
{
    public function pendentes(Request $request): JsonResponse
    {
        $academiaId = $request->user()->academia_id;

        // Atualiza status de mensalidades vencidas antes de buscar
        $mensalidadeService = new MensalidadeService();
        $mensalidadeService->atualizarMensalidadesVencidas($academiaId);

        $dataLimite = now()->addDays(3)->endOfDay();

        // Mensalidades urgentes (atrasadas + vencendo nos próximos 3 dias)
        $urgentes = Mensalidade::with(['aluno:id,nome,telefone,email', 'plano:id,nome'])
            ->where('academia_id', $academiaId)
            ->pendentes()
            ->where('data_vencimento', '<=', $dataLimite)
            ->orderBy('data_vencimento', 'asc')
            ->get();

        // Próximas cobranças (vencendo depois de 3 dias)
        $proximas = Mensalidade::with(['aluno:id,nome,telefone,email', 'plano:id,nome'])
            ->where('academia_id', $academiaId)
            ->pendentes()
            ->where('data_vencimento', '>', $dataLimite)
            ->orderBy('data_vencimento', 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'urgentes' => $urgentes,
            'proximas' => $proximas,
        ]);
    }

    public function registrarPagamento(Request $request, Mensalidade $mensalidade): JsonResponse
    {
        // Verificar se a mensalidade pertence à academia do usuário
        if ($mensalidade->academia_id !== $request->user()->academia_id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $validated = $request->validate([
            'metodo_pagamento' => 'required|in:dinheiro,pix,cartao,boleto',
            'observacao' => 'nullable|string|max:255',
        ]);

        $mensalidadeService = new MensalidadeService();
        $mensalidadeService->registrarPagamento(
            $mensalidade,
            $validated['metodo_pagamento'],
            $validated['observacao'] ?? null
        );

        return response()->json([
            'message' => 'Pagamento registrado com sucesso.',
            'mensalidade' => $mensalidade->fresh()->load('aluno:id,nome'),
        ]);
    }
}
