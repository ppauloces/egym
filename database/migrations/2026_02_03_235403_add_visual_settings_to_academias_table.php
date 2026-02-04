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
        Schema::table('academias', function (Blueprint $table) {
            $table->string('cor_primaria')->nullable()->after('ativo');
            $table->string('cor_secundaria')->nullable()->after('cor_primaria');
            $table->string('logo')->nullable()->after('cor_secundaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academias', function (Blueprint $table) {
            $table->dropColumn(['cor_primaria', 'cor_secundaria', 'logo']);
        });
    }
};
