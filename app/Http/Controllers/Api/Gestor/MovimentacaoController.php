<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Movimentacao;
use App\Services\FinanceiroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovimentacaoController extends Controller
{
    public function __construct(
        private FinanceiroService $financeiroService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Movimentacao::with(['categoria', 'aluno:id,nome', 'parcelas'])
            ->where('academia_id', $request->user()->academia_id);

        // Filtros
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('mes') && $request->has('ano')) {
            $query->whereMonth('data_competencia', $request->mes)
                  ->whereYear('data_competencia', $request->ano);
        }

        $movimentacoes = $query->orderBy('data_competencia', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($movimentacoes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias_financeiras,id',
            'aluno_id' => 'nullable|exists:alunos,id',
            'tipo' => 'required|in:entrada,saida',
            'descricao' => 'required|string|max:255',
            'valor_total' => 'required|numeric|min:0.01',
            'data_competencia' => 'required|date',
            'observacao' => 'nullable|string',
            'recorrente' => 'boolean',
            
            // Pagamento
            'pagamento.entrada.valor' => 'nullable|numeric|min:0',
            'pagamento.entrada.metodo' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'pagamento.entrada.data' => 'nullable|date',
            'pagamento.entrada.pago' => 'nullable|boolean',
            
            'pagamento.parcelas.quantidade' => 'nullable|integer|min:1|max:48',
            'pagamento.parcelas.metodo' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'pagamento.parcelas.primeira_data' => 'nullable|date',
            'pagamento.parcelas.valor_parcela' => 'nullable|numeric|min:0.01',
            
            'pagamento.metodo' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'pagamento.pago' => 'nullable|boolean',
        ]);

        $validated['academia_id'] = $request->user()->academia_id;

        $movimentacao = $this->financeiroService->criarMovimentacao($validated);

        return response()->json($movimentacao, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $movimentacao = Movimentacao::with(['categoria', 'aluno:id,nome', 'parcelas'])
            ->where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        return response()->json($movimentacao);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $movimentacao = Movimentacao::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        // Não permite excluir se há parcelas pagas
        if ($movimentacao->parcelas()->where('status', 'pago')->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir movimentação com parcelas pagas.'
            ], 422);
        }

        $movimentacao->delete(); // Cascade deleta parcelas

        return response()->json(null, 204);
    }

    public function resumoMensal(Request $request): JsonResponse
    {
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        $resumo = $this->financeiroService->getResumoMensal(
            $request->user()->academia_id,
            $mes,
            $ano
        );

        return response()->json($resumo);
    }

    public function extrato(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo', 'saida'); // entrada ou saida
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);
        $academiaId = $request->user()->academia_id;

        $transacoes = [];

        if ($tipo === 'entrada') {
            // Mensalidades pagas
            $mensalidades = \App\Models\Mensalidade::with(['aluno:id,nome', 'plano:id,nome'])
                ->where('academia_id', $academiaId)
                ->where('status', 'pago')
                ->whereMonth('data_pagamento', $mes)
                ->whereYear('data_pagamento', $ano)
                ->get()
                ->map(function ($m) {
                    return [
                        'id' => 'mens_' . $m->id,
                        'tipo' => 'mensalidade',
                        'descricao' => $m->plano->nome,
                        'categoria' => 'Mensalidade',
                        'aluno' => $m->aluno->nome,
                        'valor' => $m->valor,
                        'data' => $m->data_pagamento,
                        'metodo' => $m->metodo_pagamento,
                    ];
                });

            // Parcelas de receitas
            $parcelas = \App\Models\Parcela::with(['movimentacao.categoria', 'movimentacao.aluno:id,nome'])
                ->where('academia_id', $academiaId)
                ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'entrada'))
                ->where('status', 'pago')
                ->whereMonth('data_pagamento', $mes)
                ->whereYear('data_pagamento', $ano)
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => 'parc_' . $p->id,
                        'tipo' => 'receita',
                        'descricao' => $p->movimentacao->descricao,
                        'categoria' => $p->movimentacao->categoria->nome,
                        'aluno' => $p->movimentacao->aluno ? $p->movimentacao->aluno->nome : null,
                        'valor' => $p->valor,
                        'data' => $p->data_pagamento,
                        'metodo' => $p->metodo_pagamento,
                        'parcela' => $p->numero === 0 ? 'Entrada' : $p->numero . '/' . $p->movimentacao->parcelas->count(),
                    ];
                });

            $transacoes = $mensalidades->concat($parcelas);
        } else {
            // Parcelas de despesas
            $parcelas = \App\Models\Parcela::with(['movimentacao.categoria', 'movimentacao.aluno:id,nome'])
                ->where('academia_id', $academiaId)
                ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'saida'))
                ->where('status', 'pago')
                ->whereMonth('data_pagamento', $mes)
                ->whereYear('data_pagamento', $ano)
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => 'parc_' . $p->id,
                        'tipo' => 'despesa',
                        'descricao' => $p->movimentacao->descricao,
                        'categoria' => $p->movimentacao->categoria->nome,
                        'aluno' => $p->movimentacao->aluno ? $p->movimentacao->aluno->nome : null,
                        'valor' => $p->valor,
                        'data' => $p->data_pagamento,
                        'metodo' => $p->metodo_pagamento,
                        'parcela' => $p->numero === 0 ? 'Entrada' : $p->numero . '/' . $p->movimentacao->parcelas->count(),
                    ];
                });

            $transacoes = $parcelas;
        }

        // Ordenar por data (mais recente primeiro)
        $transacoes = $transacoes->sortByDesc('data')->values();

        return response()->json($transacoes);
    }
}
