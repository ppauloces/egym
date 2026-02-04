<?php

namespace Database\Seeders;

use App\Models\Academia;
use App\Models\Plano;
use Illuminate\Database\Seeder;

class AcademiaTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar ou atualizar academia de teste
        $academia = Academia::updateOrCreate(
            ['id' => 1],
            [
                 'nome' => 'Academia Fitness Plus',
                'cnpj' => '12.345.678/0001-90',
                'telefone' => '(11) 3333-4444',
                'email' => 'contato@fitnessplus.com',
                'endereco' => 'Rua das Academias, 123',
                'bairro' => 'Centro',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01000-000',
                'ativo' => true,
                'cor_primaria' => '#1EB4F0',
                'cor_secundaria' => '#10B981',
                'logo' => null,
                'favicon' => null,
            ]
        );

        $this->command->info('✅ Academia criada: ' . $academia->nome);

        // Criar planos para a academia
        $planos = [
            [
                'nome' => 'Mensal',
                'descricao' => 'Acesso total por 1 mês',
                'valor' => 99.90,
                'duracao_dias' => 30,
            ],
            [
                'nome' => 'Trimestral',
                'descricao' => 'Acesso total por 3 meses',
                'valor' => 269.90,
                'duracao_dias' => 90,
            ],
            [
                'nome' => 'Semestral',
                'descricao' => 'Acesso total por 6 meses',
                'valor' => 499.90,
                'duracao_dias' => 180,
            ],
            [
                'nome' => 'Anual',
                'descricao' => 'Acesso total por 12 meses',
                'valor' => 899.90,
                'duracao_dias' => 365,
            ],
        ];

        foreach ($planos as $planoData) {
            Plano::updateOrCreate(
                [
                    'academia_id' => $academia->id,
                    'nome' => $planoData['nome'],
                ],
                [
                    'descricao' => $planoData['descricao'],
                    'valor' => $planoData['valor'],
                    'duracao_dias' => $planoData['duracao_dias'],
                    'ativo' => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($planos) . ' planos criados');
    }
}
