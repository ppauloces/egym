<?php

namespace Database\Seeders;

use App\Models\Academia;
use App\Models\Aluno;
use App\Models\Mensalidade;
use App\Models\Plano;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MatriculasTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar academia existente (ID 1)
        $academia = Academia::find(1);
        
        if (!$academia) {
            $this->command->error('❌ Academia com ID 1 não encontrada!');
            return;
        }

        // Criar/atualizar gestor
        User::updateOrCreate(
            ['email' => 'gestor@test.com'],
            [
                'name' => 'Gestor Teste',
                'password' => 'password',
                'role' => 'gestor',
                'academia_id' => $academia->id,
                'ativo' => true,
            ]
        );

        // Buscar planos existentes da academia
        $planos = Plano::where('academia_id', $academia->id)
            ->where('ativo', true)
            ->get();

        if ($planos->isEmpty()) {
            $this->command->error('❌ Nenhum plano encontrado para a academia!');
            return;
        }

        $this->command->info('✅ Encontrados ' . $planos->count() . ' planos ativos');

        // Alunos com diferentes cenários
        $alunosData = [
            // ALUNOS COM MENSALIDADES EM DIA
            [
                'nome' => 'João Silva',
                'cpf' => '123.456.789-01',
                'telefone' => '(11) 91234-5678',
                'email' => 'joao@test.com',
                'data_matricula' => Carbon::now()->subMonths(6),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(5), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(4), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(10), 'pago' => false], // Próxima
                ],
            ],
            [
                'nome' => 'Maria Santos',
                'cpf' => '234.567.890-12',
                'telefone' => '(11) 92345-6789',
                'email' => 'maria@test.com',
                'data_matricula' => Carbon::now()->subMonths(4),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(15), 'pago' => false],
                ],
            ],
            [
                'nome' => 'Carlos Oliveira',
                'cpf' => '345.678.901-23',
                'telefone' => '(11) 93456-7890',
                'email' => 'carlos@test.com',
                'data_matricula' => Carbon::now()->subMonths(3),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(20), 'pago' => false],
                ],
            ],

            // ALUNOS COM MENSALIDADES ATRASADAS
            [
                'nome' => 'Ana Souza',
                'cpf' => '456.789.012-34',
                'telefone' => '(11) 94567-8901',
                'email' => 'ana@test.com',
                'data_matricula' => Carbon::now()->subMonths(5),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(4), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => false], // 60 dias atrasada
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => false], // 30 dias atrasada
                    ['vencimento' => Carbon::now()->subDays(5), 'pago' => false], // 5 dias atrasada
                ],
            ],
            [
                'nome' => 'Pedro Costa',
                'cpf' => '567.890.123-45',
                'telefone' => '(11) 95678-9012',
                'email' => 'pedro@test.com',
                'data_matricula' => Carbon::now()->subMonths(4),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subDays(15), 'pago' => false], // 15 dias atrasada
                    ['vencimento' => Carbon::now()->addDays(5), 'pago' => false],
                ],
            ],
            [
                'nome' => 'Juliana Lima',
                'cpf' => '678.901.234-56',
                'telefone' => '(11) 96789-0123',
                'email' => 'juliana@test.com',
                'data_matricula' => Carbon::now()->subMonths(6),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(5), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(4), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subDays(25), 'pago' => false], // 25 dias atrasada
                    ['vencimento' => Carbon::now()->addDays(5), 'pago' => false],
                ],
            ],
            [
                'nome' => 'Roberto Alves',
                'cpf' => '789.012.345-67',
                'telefone' => '(11) 97890-1234',
                'email' => 'roberto@test.com',
                'data_matricula' => Carbon::now()->subMonths(3),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subDays(10), 'pago' => false], // 10 dias atrasada
                    ['vencimento' => Carbon::now()->addDays(20), 'pago' => false],
                ],
            ],

            // ALUNOS COM MENSALIDADES URGENTES (Vencendo nos próximos dias)
            [
                'nome' => 'Fernanda Rocha',
                'cpf' => '890.123.456-78',
                'telefone' => '(11) 98901-2345',
                'email' => 'fernanda@test.com',
                'data_matricula' => Carbon::now()->subMonths(3),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(2), 'pago' => false, 'observacao' => 'Cliente pediu para ligar antes de cobrar'], // Vence em 2 dias
                ],
            ],
            [
                'nome' => 'Lucas Martins',
                'cpf' => '901.234.567-89',
                'telefone' => '(11) 99012-3456',
                'email' => 'lucas@test.com',
                'data_matricula' => Carbon::now()->subMonths(2),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(3), 'pago' => false], // Vence em 3 dias
                ],
            ],
            [
                'nome' => 'Patrícia Ferreira',
                'cpf' => '012.345.678-90',
                'telefone' => '(11) 99123-4567',
                'email' => 'patricia@test.com',
                'data_matricula' => Carbon::now()->subMonths(4),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(1), 'pago' => false], // Vence amanhã
                ],
            ],

            // ALUNOS NOVOS (Primeira mensalidade)
            [
                'nome' => 'Gabriel Ribeiro',
                'cpf' => '111.222.333-44',
                'telefone' => '(11) 91111-2222',
                'email' => 'gabriel@test.com',
                'data_matricula' => Carbon::now()->subDays(15),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->addDays(10), 'pago' => false],
                ],
            ],
            [
                'nome' => 'Amanda Cardoso',
                'cpf' => '222.333.444-55',
                'telefone' => '(11) 92222-3333',
                'email' => 'amanda@test.com',
                'data_matricula' => Carbon::now()->subDays(20),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->addDays(5), 'pago' => false],
                ],
            ],

            // ALUNOS COM HISTÓRICO LONGO E REGULAR
            [
                'nome' => 'Ricardo Mendes',
                'cpf' => '333.444.555-66',
                'telefone' => '(11) 93333-4444',
                'email' => 'ricardo@test.com',
                'data_matricula' => Carbon::now()->subMonths(12),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(11), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(10), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(9), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(8), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(7), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(6), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(5), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(4), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(15), 'pago' => false],
                ],
            ],
            [
                'nome' => 'Beatriz Santos',
                'cpf' => '444.555.666-77',
                'telefone' => '(11) 94444-5555',
                'email' => 'beatriz@test.com',
                'data_matricula' => Carbon::now()->subMonths(8),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(7), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(6), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(5), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(4), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => true],
                    ['vencimento' => Carbon::now()->addDays(12), 'pago' => false],
                ],
            ],

            // ALUNO COM MENSALIDADE MUITO ATRASADA
            [
                'nome' => 'Thiago Pereira',
                'cpf' => '555.666.777-88',
                'telefone' => '(11) 95555-6666',
                'email' => 'thiago@test.com',
                'data_matricula' => Carbon::now()->subMonths(6),
                'mensalidades' => [
                    ['vencimento' => Carbon::now()->subMonths(5), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(4), 'pago' => true],
                    ['vencimento' => Carbon::now()->subMonths(3), 'pago' => false], // 90 dias
                    ['vencimento' => Carbon::now()->subMonths(2), 'pago' => false], // 60 dias
                    ['vencimento' => Carbon::now()->subMonths(1), 'pago' => false], // 30 dias
                    ['vencimento' => Carbon::now()->subDays(5), 'pago' => false, 'observacao' => 'Cliente disse que vai pagar na sexta-feira'], // 5 dias
                ],
            ],
        ];

        foreach ($alunosData as $alunoData) {
            // Seleciona um plano aleatório
            $plano = $planos->random();

            // Cria o aluno
            $aluno = Aluno::updateOrCreate(
                [
                    'cpf' => $alunoData['cpf'],
                    'academia_id' => $academia->id,
                ],
                [
                    'nome' => $alunoData['nome'],
                    'telefone' => $alunoData['telefone'],
                    'email' => $alunoData['email'],
                    'data_nascimento' => Carbon::now()->subYears(rand(18, 50)),
                    'plano_id' => $plano->id,
                    'data_matricula' => $alunoData['data_matricula'],
                    'status' => 'ativo',
                ]
            );

            // Cria as mensalidades
            foreach ($alunoData['mensalidades'] as $mensalidadeData) {
                $mensalidade = Mensalidade::updateOrCreate(
                    [
                        'aluno_id' => $aluno->id,
                        'data_vencimento' => $mensalidadeData['vencimento']->format('Y-m-d'),
                    ],
                    [
                        'academia_id' => $academia->id,
                        'plano_id' => $plano->id,
                        'valor' => $plano->valor,
                        'data_pagamento' => $mensalidadeData['pago'] ? $mensalidadeData['vencimento']->copy()->subDays(rand(1, 3)) : null,
                        'metodo_pagamento' => $mensalidadeData['pago'] ? ['pix', 'dinheiro', 'cartao', 'boleto'][array_rand(['pix', 'dinheiro', 'cartao', 'boleto'])] : null,
                        'status' => $mensalidadeData['pago'] ? 'pago' : 'pendente',
                        'observacao' => $mensalidadeData['observacao'] ?? null,
                    ]
                );
            }
        }

        $this->command->info('✅ Gestor e ' . count($alunosData) . ' Alunos com mensalidades criados com sucesso!');
    }
}
