<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class MasterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ppaulo.developer@gmail.com'],
            [
                'name' => 'Paulo Prandini',
                'password' => 'password',
                'role' => 'master',
                'academia_id' => null,
                'ativo' => true,
            ]
        );
    }
}
