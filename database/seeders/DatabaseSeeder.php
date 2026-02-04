<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MasterUserSeeder::class,          // 1. Cria usuário master
            AcademiaTestSeeder::class,        // 2. Cria academia e planos
            CategoriaFinanceiraSeeder::class, // 3. Cria categorias financeiras
            MatriculasTestSeeder::class,      // 4. Cria gestor, alunos e mensalidades
            MovimentacoesTestSeeder::class,   // 5. Cria receitas e despesas
        ]);
    }
}
