<?php

namespace App\Console\Commands;

use App\Models\Academia;
use App\Services\MensalidadeService;
use Illuminate\Console\Command;

class AtualizarMensalidadesVencidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mensalidades:atualizar-vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o status de mensalidades vencidas para "atrasado"';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Atualizando mensalidades vencidas...');

        $mensalidadeService = new MensalidadeService();
        $totalAtualizado = 0;

        // Para cada academia, atualiza as mensalidades vencidas
        Academia::all()->each(function ($academia) use ($mensalidadeService, &$totalAtualizado) {
            $atualizado = $mensalidadeService->atualizarMensalidadesVencidas($academia->id);
            $totalAtualizado += $atualizado;
            
            if ($atualizado > 0) {
                $this->line("Academia {$academia->nome}: {$atualizado} mensalidade(s) atualizada(s)");
            }
        });

        $this->info("Total: {$totalAtualizado} mensalidade(s) marcada(s) como atrasada(s)");

        return Command::SUCCESS;
    }
}
