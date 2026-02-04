<?php

namespace Database\Seeders;

use App\Models\Academia;
use App\Models\Aluno;
use App\Models\CategoriaFinanceira;
use App\Services\FinanceiroService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MovimentacoesTestSeeder extends Seeder
{
    protected FinanceiroService $financeiroService;

    public function __construct(FinanceiroService $financeiroService)
    {
        $this->financeiroService = $financeiroService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academia = Academia::find(1);
        
        if (!$academia) {
            $this->command->error('❌ Academia não encontrada!');
            return;
        }

        $alunos = Aluno::where('academia_id', $academia->id)->get();
        
        // Buscar categorias
        $categoriaAluguel = CategoriaFinanceira::where('nome', 'Aluguel')->first();
        $categoriaAgua = CategoriaFinanceira::where('nome', 'Água')->first();
        $categoriaLuz = CategoriaFinanceira::where('nome', 'Luz')->first();
        $categoriaInternet = CategoriaFinanceira::where('nome', 'Internet')->first();
        $categoriaEquipamentos = CategoriaFinanceira::where('nome', 'Equipamentos')->first();
        $categoriaProdutos = CategoriaFinanceira::where('nome', 'Venda de Produtos')->first();

        // ============================================
        // DESPESAS RECORRENTES (Pagas)
        // ============================================
        
        // Aluguel - Janeiro (Pago)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaAluguel->id,
            'tipo' => 'saida',
            'descricao' => 'Aluguel - Janeiro',
            'valor_total' => 3500.00,
            'data_competencia' => Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d'),
            'observacao' => 'Aluguel pago em dia',
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'transferencia',
                'pago' => true,
            ],
        ]);

        // Aluguel - Fevereiro (Pago)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaAluguel->id,
            'tipo' => 'saida',
            'descricao' => 'Aluguel - Fevereiro',
            'valor_total' => 3500.00,
            'data_competencia' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'observacao' => null,
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'transferencia',
                'pago' => true,
            ],
        ]);

        // Água - Janeiro (Pago)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaAgua->id,
            'tipo' => 'saida',
            'descricao' => 'Conta de Água - Janeiro',
            'valor_total' => 450.00,
            'data_competencia' => Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d'),
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'pix',
                'pago' => true,
            ],
        ]);

        // Luz - Janeiro (Pago)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaLuz->id,
            'tipo' => 'saida',
            'descricao' => 'Conta de Luz - Janeiro',
            'valor_total' => 850.00,
            'data_competencia' => Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d'),
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'pix',
                'pago' => true,
            ],
        ]);

        // Internet - Janeiro (Pago)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaInternet->id,
            'tipo' => 'saida',
            'descricao' => 'Internet Fibra 500MB - Janeiro',
            'valor_total' => 199.90,
            'data_competencia' => Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d'),
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'cartao',
                'pago' => true,
            ],
        ]);

        // ============================================
        // DESPESAS PENDENTES (Mês Atual)
        // ============================================

        // Água - Fevereiro (Pendente)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaAgua->id,
            'tipo' => 'saida',
            'descricao' => 'Conta de Água - Fevereiro',
            'valor_total' => 480.00,
            'data_competencia' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'pix',
                'pago' => false,
            ],
        ]);

        // Luz - Fevereiro (Pendente)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaLuz->id,
            'tipo' => 'saida',
            'descricao' => 'Conta de Luz - Fevereiro',
            'valor_total' => 920.00,
            'data_competencia' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'pix',
                'pago' => false,
            ],
        ]);

        // Internet - Fevereiro (Pendente)
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaInternet->id,
            'tipo' => 'saida',
            'descricao' => 'Internet Fibra 500MB - Fevereiro',
            'valor_total' => 199.90,
            'data_competencia' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'recorrente' => true,
            'pagamento' => [
                'tipo' => 'vista',
                'metodo' => 'cartao',
                'pago' => false,
            ],
        ]);

        // ============================================
        // DESPESAS ÚNICAS (Equipamentos)
        // ============================================

        // Esteira Profissional - Parcelado 10x
        $this->financeiroService->criarMovimentacao([
            'academia_id' => $academia->id,
            'categoria_id' => $categoriaEquipamentos->id,
            'tipo' => 'saida',
            'descricao' => 'Esteira Profissional Athletic',
            'valor_total' => 8500.00,
            'data_competencia' => Carbon::now()->subMonths(2)->format('Y-m-d'),
            'observacao' => 'Equipamento novo para área cardio',
            'recorrente' => false,
            'pagamento' => [
                'tipo' => 'parcelado',
                'parcelas' => [
                    'quantidade' => 10,
                    'metodo' => 'cartao',
                    'primeira_data' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                ],
            ],
        ]);

        // ============================================
        // RECEITAS (Venda de Produtos)
        // ============================================

        if ($alunos->count() > 0) {
            // Creatina - João Silva
            $alunoJoao = $alunos->where('nome', 'João Silva')->first();
            if ($alunoJoao) {
                $this->financeiroService->criarMovimentacao([
                    'academia_id' => $academia->id,
                    'aluno_id' => $alunoJoao->id,
                    'categoria_id' => $categoriaProdutos->id,
                    'tipo' => 'entrada',
                    'descricao' => 'Creatina 300g',
                    'valor_total' => 90.00,
                    'data_competencia' => Carbon::now()->subDays(5)->format('Y-m-d'),
                    'observacao' => 'Venda de suplemento',
                    'recorrente' => false,
                    'pagamento' => [
                        'tipo' => 'vista',
                        'metodo' => 'pix',
                        'pago' => true,
                    ],
                ]);
            }

            // Whey Protein - Maria Santos
            $alunoMaria = $alunos->where('nome', 'Maria Santos')->first();
            if ($alunoMaria) {
                $this->financeiroService->criarMovimentacao([
                    'academia_id' => $academia->id,
                    'aluno_id' => $alunoMaria->id,
                    'categoria_id' => $categoriaProdutos->id,
                    'tipo' => 'entrada',
                    'descricao' => 'Whey Protein 900g',
                    'valor_total' => 150.00,
                    'data_competencia' => Carbon::now()->subDays(3)->format('Y-m-d'),
                    'observacao' => 'Venda de suplemento',
                    'recorrente' => false,
                    'pagamento' => [
                        'tipo' => 'vista',
                        'metodo' => 'cartao',
                        'pago' => true,
                    ],
                ]);
            }

            // Barras de Proteína - Carlos Oliveira (Pendente)
            $alunoCarlos = $alunos->where('nome', 'Carlos Oliveira')->first();
            if ($alunoCarlos) {
                $this->financeiroService->criarMovimentacao([
                    'academia_id' => $academia->id,
                    'aluno_id' => $alunoCarlos->id,
                    'categoria_id' => $categoriaProdutos->id,
                    'tipo' => 'entrada',
                    'descricao' => 'Caixa Barras de Proteína (12 unidades)',
                    'valor_total' => 60.00,
                    'data_competencia' => Carbon::now()->subDays(1)->format('Y-m-d'),
                    'observacao' => 'Aguardando pagamento',
                    'recorrente' => false,
                    'pagamento' => [
                        'tipo' => 'vista',
                        'metodo' => 'dinheiro',
                        'pago' => false,
                    ],
                ]);
            }
        }

        $this->command->info('✅ Movimentações financeiras criadas com sucesso!');
    }
}
