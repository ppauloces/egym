<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aluno extends Model
{
    use HasFactory;

    protected $fillable = [
        'academia_id',
        'plano_id',
        'nome',
        'cpf',
        'email',
        'telefone',
        'data_nascimento',
        'data_matricula',
        'matricula_retroativa',
        'data_proxima_mensalidade',
        'status',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_matricula' => 'date',
        'matricula_retroativa' => 'boolean',
        'data_proxima_mensalidade' => 'date',
    ];

    public function academia(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class);
    }

    public function mensalidades(): HasMany
    {
        return $this->hasMany(Mensalidade::class);
    }

    public function scopeAtivos($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeInativos($query)
    {
        return $query->where('status', 'inativo');
    }
}
