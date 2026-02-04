<?php

namespace App\Console\Commands;

use App\Models\Aluno;
use App\Services\MensalidadeService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessarMatriculasRetroativas extends Command
{
    protected $signature = 'mensalidades:processar-retroativas {--force : Reprocessar todos os alunos, mesmo os que já têm mensalidades}';
    protected $description = 'Processa matrículas retroativas e gera mensalidades pendentes/atrasadas';

    public function handle(): int
    {
        $this->info('🔄 Processando matrículas retroativas...');
        $this->newLine();

        $mensalidadeService = new MensalidadeService();
        $processados = 0;
        $erros = 0;

        $query = Aluno::whereNotNull('plano_id')
            ->whereNotNull('data_matricula')
            ->where('status', 'ativo');

        // Se não forçar, processa apenas alunos sem mensalidades
        if (!$this->option('force')) {
            $query->whereDoesntHave('mensalidades');
        }

        $alunos = $query->get();

        if ($alunos->isEmpty()) {
            $this->warn('Nenhum aluno encontrado para processar.');
            return Command::SUCCESS;
        }

        $this->info("Total de alunos a processar: {$alunos->count()}");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($alunos->count());
        $progressBar->start();

        foreach ($alunos as $aluno) {
            try {
                $dataMatricula = Carbon::parse($aluno->data_matricula);
                $plano = $aluno->plano;
                
                if (!$plano) {
                    $progressBar->advance();
                    continue;
                }

                $primeiroVencimento = $dataMatricula->copy()->addDays($plano->duracao_dias);

                // Só processa se for retroativa (matrícula no passado E primeiro vencimento já passou)
                if ($dataMatricula->isPast() && $primeiroVencimento->isPast()) {
                    // Se forçar, deleta mensalidades existentes
                    if ($this->option('force')) {
                        $aluno->mensalidades()->delete();
                    }

                    $mensalidadeService->gerarMensalidadesRetroativas($aluno);
                    $processados++;
                }

            } catch (\Exception $e) {
                $erros++;
                $this->newLine();
                $this->error("Erro ao processar aluno {$aluno->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->newLine();

        // Resumo
        $this->info('✅ Processamento concluído!');
        $this->table(
            ['Métrica', 'Quantidade'],
            [
                ['Alunos processados', $processados],
                ['Erros', $erros],
                ['Total de alunos', $alunos->count()],
            ]
        );

        // Atualiza status de mensalidades vencidas
        if ($processados > 0) {
            $this->newLine();
            $this->info('🔄 Atualizando status de mensalidades vencidas...');
            $this->call('mensalidades:atualizar-vencidas');
        }

        return Command::SUCCESS;
    }
}
