<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Mensalidade;
use App\Services\MensalidadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $academiaId = $request->user()->academia_id;
        $hoje = now();
        $mesAtual = $hoje->month;
        $anoAtual = $hoje->year;

        // Atualiza status de mensalidades vencidas
        $mensalidadeService = new MensalidadeService();
        $mensalidadeService->atualizarMensalidadesVencidas($academiaId);

        // Alunos ativos
        $alunosAtivos = Aluno::where('academia_id', $academiaId)
            ->where('status', 'ativo')
            ->count();

        

        // Receita do mês (mensalidades pagas no mês atual)
        $receitaMes = Mensalidade::where('academia_id', $academiaId)
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mesAtual)
            ->whereYear('data_pagamento', $anoAtual)
            ->sum('valor');

        // A receber no mês (mensalidades pendentes do mês)
        $aReceberMes = Mensalidade::where('academia_id', $academiaId)
            ->pendentes()
            ->whereMonth('data_vencimento', $mesAtual)
            ->whereYear('data_vencimento', $anoAtual)
            ->sum('valor');

        // Lista de pendentes URGENTES (atrasadas + vencendo nos próximos 2 dias)
        $dataLimite = now()->addDays(2)->endOfDay();

        // Mensalidades pendentes (pendente ou atrasado)
        $mensalidadesPendentes = Mensalidade::where('academia_id', $academiaId)
            ->pendentes()
            ->where('data_vencimento', '<=', $dataLimite)
            ->count();
        
        $pendentes = Mensalidade::with(['aluno:id,nome,telefone'])
            ->where('academia_id', $academiaId)
            ->pendentes()
            ->where('data_vencimento', '<=', $dataLimite)
            ->orderBy('data_vencimento', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($mensalidade) {
                return [
                    'id' => $mensalidade->id,
                    'aluno' => [
                        'id' => $mensalidade->aluno->id,
                        'nome' => $mensalidade->aluno->nome,
                        'telefone' => $mensalidade->aluno->telefone,
                    ],
                    'valor' => $mensalidade->valor,
                    'data_vencimento' => $mensalidade->data_vencimento->format('Y-m-d'),
                    'dias_atraso' => $mensalidade->dias_atraso,
                    'status' => $mensalidade->status,
                    'observacao' => $mensalidade->observacao,
                ];
            });

        return response()->json([
            'alunos_ativos' => $alunosAtivos,
            'mensalidades_pendentes' => $mensalidadesPendentes,
            'receita_mes' => (float) $receitaMes,
            'a_receber_mes' => (float) $aReceberMes,
            'pendentes' => $pendentes,
        ]);
    }
}
