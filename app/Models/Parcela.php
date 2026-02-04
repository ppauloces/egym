<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcela extends Model
{
    use HasFactory;

    protected $fillable = [
        'movimentacao_id',
        'academia_id',
        'numero',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'metodo_pagamento',
        'status',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
    ];

    public function movimentacao(): BelongsTo
    {
        return $this->belongsTo(Movimentacao::class);
    }

    public function academia(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['pendente', 'atrasado']);
    }

    public function scopePagas($query)
    {
        return $query->where('status', 'pago');
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('status', 'atrasado');
    }

    public function scopeDoMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;

        return $query->whereMonth('data_vencimento', $mes)
                     ->whereYear('data_vencimento', $ano);
    }

    public function getDiasAtrasoAttribute(): int
    {
        if ($this->status === 'pago') {
            return 0;
        }

        $hoje = Carbon::now()->startOfDay();
        $vencimento = Carbon::parse($this->data_vencimento)->startOfDay();
        
        $diff = $hoje->diffInDays($vencimento, false);
        
        return -$diff; // Positivo = atraso, Negativo = dias até vencer
    }

    public function getIsEntradaAttribute(): bool
    {
        return $this->numero === 0;
    }

    public function getDescricaoParcelaAttribute(): string
    {
        if ($this->numero === 0) {
            return 'Entrada';
        }
        
        $total = $this->movimentacao->total_parcelas;
        $parcelasNumeradas = $total - ($this->movimentacao->parcelas()->where('numero', 0)->exists() ? 1 : 0);
        
        return "Parcela {$this->numero}/{$parcelasNumeradas}";
    }
}
