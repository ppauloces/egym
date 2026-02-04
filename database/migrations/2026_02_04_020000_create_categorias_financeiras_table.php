<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categorias_financeiras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academia_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'saida']);
            $table->string('nome', 100);
            $table->string('cor', 7)->default('#6B7280'); // Hex color
            $table->string('icone', 50)->nullable(); // Nome do ícone (heroicons)
            $table->boolean('sistema')->default(false); // Categorias do sistema não podem ser excluídas
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            $table->unique(['academia_id', 'tipo', 'nome']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias_financeiras');
    }
};
