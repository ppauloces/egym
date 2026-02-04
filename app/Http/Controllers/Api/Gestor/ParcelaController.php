<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Parcela;
use App\Services\FinanceiroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    public function __construct(
        private FinanceiroService $financeiroService
    ) {}

    public function pendentes(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo'); // 'entrada' ou 'saida'
        
        $parcelas = $this->financeiroService->getParcelasPendentes(
            $request->user()->academia_id,
            $tipo,
            50
        );

        return response()->json($parcelas);
    }

    public function pagar(Request $request, int $id): JsonResponse
    {
        $parcela = Parcela::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($parcela->status === 'pago') {
            return response()->json(['message' => 'Parcela já está paga.'], 422);
        }

        $validated = $request->validate([
            'metodo_pagamento' => 'required|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'data_pagamento' => 'nullable|date',
            'observacao' => 'nullable|string',
        ]);

        $parcela = $this->financeiroService->registrarPagamento(
            $parcela,
            $validated['metodo_pagamento'],
            $validated['data_pagamento'] ?? null,
            $validated['observacao'] ?? null
        );

        return response()->json($parcela);
    }

    public function cancelar(Request $request, int $id): JsonResponse
    {
        $parcela = Parcela::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($parcela->status === 'pago') {
            return response()->json(['message' => 'Não é possível cancelar parcela paga.'], 422);
        }

        $parcela->update(['status' => 'cancelado']);

        return response()->json($parcela);
    }
}
