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
        Schema::table('alunos', function (Blueprint $table) {
            $table->boolean('matricula_retroativa')->default(false)->after('data_matricula');
            $table->date('data_proxima_mensalidade')->nullable()->after('matricula_retroativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alunos', function (Blueprint $table) {
            $table->dropColumn(['matricula_retroativa', 'data_proxima_mensalidade']);
        });
    }
};
