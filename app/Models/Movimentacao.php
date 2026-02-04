<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movimentacao extends Model
{
    use HasFactory;

    protected $table = 'movimentacoes';

    protected $fillable = [
        'academia_id',
        'aluno_id',
        'categoria_id',
        'tipo',
        'descricao',
        'valor_total',
        'data_competencia',
        'observacao',
        'recorrente',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'data_competencia' => 'date',
        'recorrente' => 'boolean',
    ];

    protected $appends = [
        'status_geral',
        'valor_pago',
        'valor_pendente',
    ];

    public function academia(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id');
    }

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(Parcela::class)->orderBy('numero');
    }

    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }

    public function scopeDoMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;

        return $query->whereMonth('data_competencia', $mes)
                     ->whereYear('data_competencia', $ano);
    }

    // Atributos calculados
    public function getValorPagoAttribute(): float
    {
        return (float) $this->parcelas()->where('status', 'pago')->sum('valor');
    }

    public function getValorPendenteAttribute(): float
    {
        return (float) $this->parcelas()->whereIn('status', ['pendente', 'atrasado'])->sum('valor');
    }

    public function getStatusGeralAttribute(): string
    {
        // Force refresh to get latest status
        $parcelas = $this->parcelas()->get();
        
        if ($parcelas->isEmpty()) {
            return 'pendente';
        }
        
        if ($parcelas->every(fn($p) => $p->status === 'pago')) {
            return 'pago';
        }
        
        if ($parcelas->contains(fn($p) => $p->status === 'atrasado')) {
            return 'atrasado';
        }
        
        return 'pendente';
    }

    public function getTotalParcelasAttribute(): int
    {
        return $this->parcelas()->count();
    }

    public function getParcelasPagasAttribute(): int
    {
        return $this->parcelas()->where('status', 'pago')->count();
    }
}
