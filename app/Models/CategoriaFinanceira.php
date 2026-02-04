<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaFinanceira extends Model
{
    use HasFactory;

    protected $table = 'categorias_financeiras';

    protected $fillable = [
        'academia_id',
        'tipo',
        'nome',
        'cor',
        'icone',
        'sistema',
        'ativo',
    ];

    protected $casts = [
        'sistema' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function academia(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class, 'categoria_id');
    }

    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }

    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }
}
