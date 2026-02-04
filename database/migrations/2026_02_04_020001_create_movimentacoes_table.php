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
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academia_id')->constrained()->cascadeOnDelete();
            $table->foreignId('aluno_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias_financeiras')->restrictOnDelete();
            $table->enum('tipo', ['entrada', 'saida']);
            $table->string('descricao', 255);
            $table->decimal('valor_total', 10, 2);
            $table->date('data_competencia'); // Mês/ano de referência
            $table->text('observacao')->nullable();
            $table->boolean('recorrente')->default(false); // Para despesas fixas mensais
            $table->timestamps();
            
            $table->index(['academia_id', 'tipo', 'data_competencia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
