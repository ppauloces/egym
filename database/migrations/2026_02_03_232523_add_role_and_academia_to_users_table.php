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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['master', 'gestor'])->default('gestor')->after('email');
            $table->foreignId('academia_id')->nullable()->after('role')->constrained('academias')->nullOnDelete();
            $table->boolean('ativo')->default(true)->after('academia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['academia_id']);
            $table->dropColumn(['role', 'academia_id', 'ativo']);
        });
    }
};
