<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Gestor\AlunoController;
use App\Http\Controllers\Api\Gestor\CategoriaFinanceiraController;
use App\Http\Controllers\Api\Gestor\DashboardController;
use App\Http\Controllers\Api\Gestor\MensalidadeController;
use App\Http\Controllers\Api\Gestor\MovimentacaoController;
use App\Http\Controllers\Api\Gestor\ParcelaController;
use App\Http\Controllers\Api\Gestor\PlanoController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Gestor routes
    Route::prefix('gestor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Planos
        Route::apiResource('planos', PlanoController::class);

        // Alunos
        Route::apiResource('alunos', AlunoController::class);
        Route::post('/alunos/{aluno}/toggle-status', [AlunoController::class, 'toggleStatus']);

        // Mensalidades
        Route::get('/mensalidades/pendentes', [MensalidadeController::class, 'pendentes']);
        Route::post('/mensalidades/{mensalidade}/pagar', [MensalidadeController::class, 'registrarPagamento']);

        // Categorias Financeiras
        Route::get('categorias-financeiras', [CategoriaFinanceiraController::class, 'index']);
        Route::get('categorias-financeiras/{tipo}', [CategoriaFinanceiraController::class, 'porTipo']);
        Route::post('categorias-financeiras', [CategoriaFinanceiraController::class, 'store']);
        Route::put('categorias-financeiras/{id}', [CategoriaFinanceiraController::class, 'update']);
        Route::delete('categorias-financeiras/{id}', [CategoriaFinanceiraController::class, 'destroy']);

        // Movimentações
        Route::get('movimentacoes', [MovimentacaoController::class, 'index']);
        Route::post('movimentacoes', [MovimentacaoController::class, 'store']);
        Route::get('movimentacoes/resumo', [MovimentacaoController::class, 'resumoMensal']);
        Route::get('movimentacoes/extrato', [MovimentacaoController::class, 'extrato']);
        Route::get('movimentacoes/{id}', [MovimentacaoController::class, 'show']);
        Route::delete('movimentacoes/{id}', [MovimentacaoController::class, 'destroy']);

        // Parcelas
        Route::get('parcelas/pendentes', [ParcelaController::class, 'pendentes']);
        Route::post('parcelas/{id}/pagar', [ParcelaController::class, 'pagar']);
        Route::post('parcelas/{id}/cancelar', [ParcelaController::class, 'cancelar']);
    });
});
