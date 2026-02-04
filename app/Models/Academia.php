<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Academia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'slug',
        'cnpj',
        'telefone',
        'email',
        'endereco',
        'ativo',
        'cor_primaria',
        'cor_secundaria',
        'logo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Academia $academia) {
            if (empty($academia->slug)) {
                $academia->slug = Str::slug($academia->nome);
            }
        });
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'academia_id');
    }
}
