<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensalidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'aluno_id',
        'academia_id',
        'plano_id',
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

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function academia(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['pendente', 'atrasado']);
    }

    public function scopeAtrasadas($query)
    {
        return $query->where('status', 'atrasado');
    }

    public function scopePagas($query)
    {
        return $query->where('status', 'pago');
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

        // Calcula a diferença em dias (positivo = atraso, negativo = futuro, 0 = hoje)
        $hoje = Carbon::now()->startOfDay();
        $vencimento = Carbon::parse($this->data_vencimento)->startOfDay();
        
        $diffInDays = $hoje->diffInDays($vencimento, false);
        
        // diffInDays retorna negativo se hoje > vencimento (atraso)
        // e positivo se vencimento > hoje (futuro)
        // Precisamos inverter o sinal
        return -$diffInDays;
    }
}
