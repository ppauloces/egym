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
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movimentacao_id')->constrained('movimentacoes')->cascadeOnDelete();
            $table->foreignId('academia_id')->constrained()->cascadeOnDelete();
            $table->integer('numero'); // 0 = entrada, 1+ = parcelas
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->enum('metodo_pagamento', ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'])->nullable();
            $table->enum('status', ['pendente', 'pago', 'atrasado', 'cancelado'])->default('pendente');
            $table->text('observacao')->nullable();
            $table->timestamps();
            
            $table->unique(['movimentacao_id', 'numero']);
            $table->index(['academia_id', 'status', 'data_vencimento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};
