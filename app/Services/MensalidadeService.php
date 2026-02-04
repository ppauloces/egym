<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Mensalidade;
use Carbon\Carbon;

class MensalidadeService
{
    /**
     * Gera a primeira mensalidade para um aluno recém-matriculado
     */
    public function gerarPrimeiraMensalidade(Aluno $aluno): ?Mensalidade
    {
        // Se não tem plano ou não tem data de matrícula, não gera mensalidade
        if (!$aluno->plano_id || !$aluno->data_matricula) {
            return null;
        }

        $plano = $aluno->plano;

        // Se for matrícula retroativa, usa a data informada pelo gestor
        if ($aluno->matricula_retroativa && $aluno->data_proxima_mensalidade) {
            $dataVencimento = Carbon::parse($aluno->data_proxima_mensalidade);
            $status = $dataVencimento->isPast() ? 'atrasado' : 'pendente';

            return Mensalidade::create([
                'aluno_id' => $aluno->id,
                'academia_id' => $aluno->academia_id,
                'plano_id' => $aluno->plano_id,
                'valor' => $plano->valor,
                'data_vencimento' => $dataVencimento,
                'status' => $status,
            ]);
        }

        // Matrícula normal - calcula baseado na data de matrícula
        $dataMatricula = Carbon::parse($aluno->data_matricula);
        $status = $dataMatricula->isPast() ? 'atrasado' : 'pendente';

        return Mensalidade::create([
            'aluno_id' => $aluno->id,
            'academia_id' => $aluno->academia_id,
            'plano_id' => $aluno->plano_id,
            'valor' => $plano->valor,
            'data_vencimento' => $dataMatricula,
            'status' => $status,
            'observacao' => 'Primeira mensalidade gerada',
        ]);
    }

    /**
     * Gera todas as mensalidades retroativas desde a data de matrícula até hoje
     */
    public function gerarMensalidadesRetroativas(Aluno $aluno): ?Mensalidade
    {
        if (!$aluno->plano_id || !$aluno->data_matricula) {
            return null;
        }

        $plano = $aluno->plano;
        $dataMatricula = Carbon::parse($aluno->data_matricula);
        $hoje = Carbon::today();
        
        $mensalidades = [];
        $dataVencimento = $dataMatricula->copy()->addDays($plano->duracao_dias);
        
        // Gera mensalidades até que o vencimento seja no futuro
        while ($dataVencimento->lte($hoje->copy()->addDays($plano->duracao_dias))) {
            // Verifica se já não existe essa mensalidade
            $existe = Mensalidade::where('aluno_id', $aluno->id)
                ->where('data_vencimento', $dataVencimento->format('Y-m-d'))
                ->exists();

            if (!$existe) {
                // Define status correto: atrasado se já venceu, pendente se ainda não
                $status = $dataVencimento->isPast() ? 'atrasado' : 'pendente';

                $mensalidade = Mensalidade::create([
                    'aluno_id' => $aluno->id,
                    'academia_id' => $aluno->academia_id,
                    'plano_id' => $aluno->plano_id,
                    'valor' => $plano->valor,
                    'data_vencimento' => $dataVencimento->copy(),
                    'status' => $status,
                ]);

                $mensalidades[] = $mensalidade;
            }

            // Próximo vencimento
            $dataVencimento->addDays($plano->duracao_dias);
        }

        // Retorna a primeira mensalidade criada (ou null se nenhuma foi criada)
        return $mensalidades[0] ?? null;
    }

    /**
     * Gera a próxima mensalidade após o pagamento da anterior
     */
    public function gerarProximaMensalidade(Mensalidade $mensalidadePaga): ?Mensalidade
    {
        $aluno = $mensalidadePaga->aluno;
        
        // Verifica se o aluno está ativo e tem plano
        if ($aluno->status !== 'ativo' || !$aluno->plano_id) {
            return null;
        }

        $plano = $aluno->plano;
        
        // A próxima mensalidade vence N dias após o vencimento da anterior
        $proximoVencimento = Carbon::parse($mensalidadePaga->data_vencimento)
            ->addDays($plano->duracao_dias);

        // Verifica se já não existe uma mensalidade pendente para essa data
        $existe = Mensalidade::where('aluno_id', $aluno->id)
            ->where('data_vencimento', $proximoVencimento)
            ->exists();

        if ($existe) {
            return null;
        }

        return Mensalidade::create([
            'aluno_id' => $aluno->id,
            'academia_id' => $aluno->academia_id,
            'plano_id' => $aluno->plano_id,
            'valor' => $plano->valor,
            'data_vencimento' => $proximoVencimento,
            'status' => 'pendente',
        ]);
    }

    /**
     * Atualiza status de mensalidades vencidas
     */
    public function atualizarMensalidadesVencidas(int $academiaId): int
    {
        return Mensalidade::where('academia_id', $academiaId)
            ->where('status', 'pendente')
            ->where('data_vencimento', '<', now())
            ->update(['status' => 'atrasado']);
    }

    /**
     * Registra o pagamento de uma mensalidade
     */
    public function registrarPagamento(
        Mensalidade $mensalidade,
        string $metodoPagamento,
        ?string $observacao = null
    ): Mensalidade {
        $mensalidade->update([
            'status' => 'pago',
            'data_pagamento' => now(),
            'metodo_pagamento' => $metodoPagamento,
            'observacao' => $observacao,
        ]);

        // Gera a próxima mensalidade automaticamente
        $this->gerarProximaMensalidade($mensalidade);

        return $mensalidade->fresh();
    }
}
