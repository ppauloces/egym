<?php

namespace App\Services;

use App\Models\Movimentacao;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceiroService
{
    /**
     * Cria uma movimentação com suas parcelas
     * 
     * @param array $dados [
     *   'academia_id' => int,
     *   'categoria_id' => int,
     *   'tipo' => 'entrada'|'saida',
     *   'descricao' => string,
     *   'valor_total' => float,
     *   'data_competencia' => string (Y-m-d),
     *   'observacao' => ?string,
     *   'recorrente' => bool,
     *   'pagamento' => [
     *     'entrada' => ?[
     *       'valor' => float,
     *       'metodo' => string,
     *       'data' => string (Y-m-d),
     *       'pago' => bool
     *     ],
     *     'parcelas' => ?[
     *       'quantidade' => int,
     *       'metodo' => string,
     *       'primeira_data' => string (Y-m-d),
     *       'valor_parcela' => ?float (calculado se não informado)
     *     ]
     *   ]
     * ]
     */
    public function criarMovimentacao(array $dados): Movimentacao
    {
        return DB::transaction(function () use ($dados) {
            // Criar movimentação
            $movimentacao = Movimentacao::create([
                'academia_id' => $dados['academia_id'],
                'aluno_id' => $dados['aluno_id'] ?? null,
                'categoria_id' => $dados['categoria_id'],
                'tipo' => $dados['tipo'],
                'descricao' => $dados['descricao'],
                'valor_total' => $dados['valor_total'],
                'data_competencia' => $dados['data_competencia'],
                'observacao' => $dados['observacao'] ?? null,
                'recorrente' => $dados['recorrente'] ?? false,
            ]);

            $pagamento = $dados['pagamento'];
            $valorRestante = $dados['valor_total'];

            // Criar parcela de entrada (se houver)
            if (!empty($pagamento['entrada']) && $pagamento['entrada']['valor'] > 0) {
                $entrada = $pagamento['entrada'];
                $valorEntrada = $entrada['valor'];
                $valorRestante -= $valorEntrada;

                Parcela::create([
                    'movimentacao_id' => $movimentacao->id,
                    'academia_id' => $dados['academia_id'],
                    'numero' => 0, // 0 = entrada
                    'valor' => $valorEntrada,
                    'data_vencimento' => $entrada['data'],
                    'data_pagamento' => $entrada['pago'] ? $entrada['data'] : null,
                    'metodo_pagamento' => $entrada['metodo'],
                    'status' => $entrada['pago'] ? 'pago' : 'pendente',
                ]);
            }

            // Criar parcelas (se houver)
            if (!empty($pagamento['parcelas']) && $pagamento['parcelas']['quantidade'] > 0) {
                $parcelas = $pagamento['parcelas'];
                $qtdParcelas = $parcelas['quantidade'];
                $valorParcela = $parcelas['valor_parcela'] ?? round($valorRestante / $qtdParcelas, 2);
                $primeiraData = Carbon::parse($parcelas['primeira_data']);

                // Ajuste para centavos na última parcela
                $somaCalculada = $valorParcela * $qtdParcelas;
                $diferenca = $valorRestante - $somaCalculada;

                for ($i = 1; $i <= $qtdParcelas; $i++) {
                    $valorParcAtual = $valorParcela;
                    
                    // Adiciona diferença de centavos na última parcela
                    if ($i === $qtdParcelas && $diferenca != 0) {
                        $valorParcAtual += $diferenca;
                    }

                    $dataVencimento = $primeiraData->copy()->addMonths($i - 1);

                    Parcela::create([
                        'movimentacao_id' => $movimentacao->id,
                        'academia_id' => $dados['academia_id'],
                        'numero' => $i,
                        'valor' => $valorParcAtual,
                        'data_vencimento' => $dataVencimento,
                        'metodo_pagamento' => $parcelas['metodo'],
                        'status' => 'pendente',
                    ]);
                }
            }

            // Se não tem entrada nem parcelas, é pagamento à vista único
            if (empty($pagamento['entrada']) && empty($pagamento['parcelas'])) {
                $isPago = $pagamento['pago'] ?? false;
                
                Parcela::create([
                    'movimentacao_id' => $movimentacao->id,
                    'academia_id' => $dados['academia_id'],
                    'numero' => 1,
                    'valor' => $dados['valor_total'],
                    'data_vencimento' => $dados['data_competencia'],
                    'data_pagamento' => $isPago ? ($dados['data_competencia'] ?? now()) : null,
                    'metodo_pagamento' => $pagamento['metodo'] ?? 'dinheiro',
                    'status' => $isPago ? 'pago' : 'pendente',
                ]);
            }

            return $movimentacao->load('parcelas', 'categoria');
        });
    }

    /**
     * Registra pagamento de uma parcela
     */
    public function registrarPagamento(
        Parcela $parcela,
        string $metodoPagamento,
        ?string $dataPagamento = null,
        ?string $observacao = null
    ): Parcela {
        $parcela->update([
            'status' => 'pago',
            'data_pagamento' => $dataPagamento ?? now(),
            'metodo_pagamento' => $metodoPagamento,
            'observacao' => $observacao,
        ]);

        // Se a movimentação é recorrente, gera a próxima
        $this->gerarProximaRecorrente($parcela);

        return $parcela->fresh();
    }

    /**
     * Gera próxima movimentação recorrente após pagamento
     */
    private function gerarProximaRecorrente(Parcela $parcela): void
    {
        $movimentacao = $parcela->movimentacao;

        // Só processa se for recorrente
        if (!$movimentacao->recorrente) {
            return;
        }

        // Calcula data do próximo mês
        $proximaData = Carbon::parse($movimentacao->data_competencia)->addMonth();
        $proximoMes = $proximaData->month;
        $proximoAno = $proximaData->year;

        // Verifica se já existe para o próximo mês
        $existe = Movimentacao::where('academia_id', $movimentacao->academia_id)
            ->where('categoria_id', $movimentacao->categoria_id)
            ->where('descricao', $movimentacao->descricao)
            ->whereMonth('data_competencia', $proximoMes)
            ->whereYear('data_competencia', $proximoAno)
            ->exists();

        if ($existe) {
            return; // Já existe, não cria
        }

        // Cria nova movimentação
        $nova = $movimentacao->replicate();
        $nova->data_competencia = $proximaData;
        $nova->recorrente = true; // Mantém como recorrente
        $nova->save();

        // Cria a parcela única com vencimento no próximo mês
        Parcela::create([
            'movimentacao_id' => $nova->id,
            'academia_id' => $nova->academia_id,
            'numero' => 1,
            'valor' => $nova->valor_total,
            'data_vencimento' => Carbon::parse($parcela->data_vencimento)->addMonth(),
            'metodo_pagamento' => $parcela->metodo_pagamento,
            'status' => 'pendente',
        ]);
    }

    /**
     * Atualiza status de parcelas vencidas para "atrasado"
     */
    public function atualizarParcelasVencidas(int $academiaId): int
    {
        return Parcela::where('academia_id', $academiaId)
            ->where('status', 'pendente')
            ->where('data_vencimento', '<', now()->startOfDay())
            ->update(['status' => 'atrasado']);
    }

    /**
     * Retorna resumo financeiro do mês
     */
    public function getResumoMensal(int $academiaId, ?int $mes = null, ?int $ano = null): array
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;

        // Atualiza parcelas vencidas
        $this->atualizarParcelasVencidas($academiaId);

        // Entradas do mês (parcelas de movimentações tipo 'entrada')
        $entradasPagas = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'entrada'))
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mes)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor');

        $entradasPendentes = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'entrada'))
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->sum('valor');

        // Adiciona mensalidades às entradas
        $mensalidadesPagas = \App\Models\Mensalidade::where('academia_id', $academiaId)
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mes)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor');

        $mensalidadesPendentes = \App\Models\Mensalidade::where('academia_id', $academiaId)
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->sum('valor');

        $entradasPagas += $mensalidadesPagas;
        $entradasPendentes += $mensalidadesPendentes;

        // Saídas do mês
        $saidasPagas = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'saida'))
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mes)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor');

        $saidasPendentes = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'saida'))
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->sum('valor');

        // Saldo (apenas valores efetivamente recebidos/pagos)
        $saldoRealizado = $entradasPagas - $saidasPagas;
        $saldoPrevisto = $saldoRealizado; // Mesmo valor, pois considera apenas recebidos/pagos

        return [
            'mes' => $mes,
            'ano' => $ano,
            'entradas' => [
                'recebido' => (float) $entradasPagas,
                'a_receber' => (float) $entradasPendentes,
                'total' => (float) ($entradasPagas + $entradasPendentes),
            ],
            'saidas' => [
                'pago' => (float) $saidasPagas,
                'a_pagar' => (float) $saidasPendentes,
                'total' => (float) ($saidasPagas + $saidasPendentes),
            ],
            'saldo' => [
                'realizado' => (float) $saldoRealizado,
                'previsto' => (float) $saldoPrevisto,
            ],
        ];
    }

    /**
     * Retorna parcelas pendentes/atrasadas
     */
    public function getParcelasPendentes(int $academiaId, ?string $tipo = null, int $limit = 20): array
    {
        $this->atualizarParcelasVencidas($academiaId);

        $query = Parcela::with(['movimentacao.categoria'])
            ->where('academia_id', $academiaId)
            ->whereIn('status', ['pendente', 'atrasado']);

        if ($tipo) {
            $query->whereHas('movimentacao', fn($q) => $q->where('tipo', $tipo));
        }

        return $query->orderBy('data_vencimento', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Gera despesas recorrentes do mês
     */
    public function gerarRecorrentes(int $academiaId, ?int $mes = null, ?int $ano = null): int
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        $dataCompetencia = Carbon::createFromDate($ano, $mes, 1);

        $recorrentes = Movimentacao::where('academia_id', $academiaId)
            ->where('recorrente', true)
            ->get();

        $criadas = 0;

        foreach ($recorrentes as $modelo) {
            // Verifica se já existe para este mês
            $existe = Movimentacao::where('academia_id', $academiaId)
                ->where('categoria_id', $modelo->categoria_id)
                ->where('descricao', $modelo->descricao)
                ->whereMonth('data_competencia', $mes)
                ->whereYear('data_competencia', $ano)
                ->exists();

            if (!$existe) {
                $nova = $modelo->replicate();
                $nova->data_competencia = $dataCompetencia;
                $nova->recorrente = false; // A cópia não é recorrente
                $nova->save();

                // Replica a estrutura de parcelas
                foreach ($modelo->parcelas as $parcela) {
                    $novaParcela = $parcela->replicate();
                    $novaParcela->movimentacao_id = $nova->id;
                    $novaParcela->data_vencimento = Carbon::parse($parcela->data_vencimento)
                        ->setMonth($mes)
                        ->setYear($ano);
                    $novaParcela->data_pagamento = null;
                    $novaParcela->status = 'pendente';
                    $novaParcela->save();
                }

                $criadas++;
            }
        }

        return $criadas;
    }
}
