<?php

namespace Database\Seeders;

use App\Models\Academia;
use App\Models\CategoriaFinanceira;
use Illuminate\Database\Seeder;

class CategoriaFinanceiraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            // ENTRADAS
            ['tipo' => 'entrada', 'nome' => 'Mensalidades', 'cor' => '#10B981', 'icone' => 'currency-dollar', 'sistema' => true],
            ['tipo' => 'entrada', 'nome' => 'Taxa de Matrícula', 'cor' => '#3B82F6', 'icone' => 'user-plus', 'sistema' => false],
            ['tipo' => 'entrada', 'nome' => 'Personal/Avulso', 'cor' => '#8B5CF6', 'icone' => 'user', 'sistema' => false],
            ['tipo' => 'entrada', 'nome' => 'Venda de Produtos', 'cor' => '#F59E0B', 'icone' => 'shopping-bag', 'sistema' => false],
            ['tipo' => 'entrada', 'nome' => 'Outros', 'cor' => '#6B7280', 'icone' => 'dots-horizontal', 'sistema' => false],

            // SAÍDAS
            ['tipo' => 'saida', 'nome' => 'Aluguel', 'cor' => '#EF4444', 'icone' => 'home', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Energia Elétrica', 'cor' => '#F59E0B', 'icone' => 'lightning-bolt', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Água', 'cor' => '#3B82F6', 'icone' => 'beaker', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Internet', 'cor' => '#8B5CF6', 'icone' => 'wifi', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Salários', 'cor' => '#10B981', 'icone' => 'users', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Equipamentos', 'cor' => '#EC4899', 'icone' => 'cube', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Manutenção', 'cor' => '#F97316', 'icone' => 'wrench', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Produtos de Limpeza', 'cor' => '#14B8A6', 'icone' => 'sparkles', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Marketing', 'cor' => '#A855F7', 'icone' => 'speakerphone', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Impostos/Taxas', 'cor' => '#64748B', 'icone' => 'document-text', 'sistema' => false],
            ['tipo' => 'saida', 'nome' => 'Outros', 'cor' => '#6B7280', 'icone' => 'dots-horizontal', 'sistema' => false],
        ];

        // Criar categorias para todas as academias existentes
        $academias = Academia::all();

        foreach ($academias as $academia) {
            foreach ($categorias as $categoria) {
                CategoriaFinanceira::firstOrCreate(
                    [
                        'academia_id' => $academia->id,
                        'tipo' => $categoria['tipo'],
                        'nome' => $categoria['nome'],
                    ],
                    [
                        'cor' => $categoria['cor'],
                        'icone' => $categoria['icone'],
                        'sistema' => $categoria['sistema'],
                        'ativo' => true,
                    ]
                );
            }
        }

        $this->command->info('Categorias financeiras criadas com sucesso!');
    }
}
