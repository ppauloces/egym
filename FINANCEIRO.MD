Módulo Financeiro Completo - E-GYM
Visão Geral
Implementar um módulo financeiro completo para gestão de academias com controle de entradas (receitas) e saídas (despesas), suportando pagamentos à vista, parcelados no cartão de crédito, e combinações de entrada + parcelamento.

1. Estrutura do Banco de Dados
1.1 Migration: categorias_financeiras

Schema::create('categorias_financeiras', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academia_id')->constrained()->cascadeOnDelete();
    $table->enum('tipo', ['entrada', 'saida']);
    $table->string('nome', 100);
    $table->string('cor', 7)->default('#6B7280'); // Hex color
    $table->string('icone', 50)->nullable(); // Nome do ícone (heroicons)
    $table->boolean('sistema')->default(false); // Categorias do sistema não podem ser excluídas
    $table->boolean('ativo')->default(true);
    $table->timestamps();
    
    $table->unique(['academia_id', 'tipo', 'nome']);
});
Categorias pré-definidas (seeder):


// ENTRADAS
['tipo' => 'entrada', 'nome' => 'Mensalidades', 'cor' => '#10B981', 'icone' => 'currency-dollar', 'sistema' => true],
['tipo' => 'entrada', 'nome' => 'Taxa de Matrícula', 'cor' => '#3B82F6', 'icone' => 'user-plus', 'sistema' => false],
['tipo' => 'entrada', 'nome' => 'Personal/Avulso', 'cor' => '#8B5CF6', 'icone' => 'user', 'sistema' => false],
['tipo' => 'entrada', 'nome' => 'Venda de Produtos', 'cor' => '#F59E0B', 'icone' => 'shopping-bag', 'sistema' => false],
['tipo' => 'entrada', 'nome' => 'Outros', 'cor' => '#6B7280', 'icone' => 'dots-horizontal', 'sistema' => false],

// SAÍDAS
['tipo' => 'saida', 'nome' => 'Aluguel', 'cor' => '#EF4444', 'icone' => 'home', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Energia Elétrica', 'cor' => '#F59E0B', 'icone' => 'lightning-bolt', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Água', 'cor' => '#3B82F6', 'icone' => 'beaker', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Internet', 'cor' => '#8B5CF6', 'icone' => 'wifi', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Salários', 'cor' => '#10B981', 'icone' => 'users', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Equipamentos', 'cor' => '#EC4899', 'icone' => 'cube', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Manutenção', 'cor' => '#F97316', 'icone' => 'wrench', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Produtos de Limpeza', 'cor' => '#14B8A6', 'icone' => 'sparkles', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Marketing', 'cor' => '#A855F7', 'icone' => 'speakerphone', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Impostos/Taxas', 'cor' => '#64748B', 'icone' => 'document-text', 'sistema' => false],
['tipo' => 'saida', 'nome' => 'Outros', 'cor' => '#6B7280', 'icone' => 'dots-horizontal', 'sistema' => false],
1.2 Migration: movimentacoes

Schema::create('movimentacoes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('academia_id')->constrained()->cascadeOnDelete();
    $table->foreignId('categoria_id')->constrained('categorias_financeiras')->restrictOnDelete();
    $table->enum('tipo', ['entrada', 'saida']);
    $table->string('descricao', 255);
    $table->decimal('valor_total', 10, 2);
    $table->date('data_competencia'); // Mês/ano de referência
    $table->text('observacao')->nullable();
    $table->boolean('recorrente')->default(false); // Para despesas fixas mensais
    $table->timestamps();
    
    $table->index(['academia_id', 'tipo', 'data_competencia']);
});
1.3 Migration: parcelas

Schema::create('parcelas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('movimentacao_id')->constrained()->cascadeOnDelete();
    $table->foreignId('academia_id')->constrained()->cascadeOnDelete();
    $table->integer('numero'); // 0 = entrada, 1+ = parcelas
    $table->decimal('valor', 10, 2);
    $table->date('data_vencimento');
    $table->date('data_pagamento')->nullable();
    $table->enum('metodo_pagamento', ['dinheiro', 'pix', 'cartao_credito', 'cartao_debito', 'boleto', 'transferencia'])->nullable();
    $table->enum('status', ['pendente', 'pago', 'atrasado', 'cancelado'])->default('pendente');
    $table->text('observacao')->nullable();
    $table->timestamps();
    
    $table->unique(['movimentacao_id', 'numero']);
    $table->index(['academia_id', 'status', 'data_vencimento']);
});
2. Models
2.1 CategoriaFinanceira.php

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
2.2 Movimentacao.php

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

    public function academia(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaFinanceira::class, 'categoria_id');
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
        $parcelas = $this->parcelas;
        
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
2.3 Parcela.php

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
3. Service Layer
3.1 FinanceiroService.php

<?php

namespace App\Services;

use App\Models\Movimentacao;
use App\Models\Parcela;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceiroService
{
    /**
     * Cria uma movimentação com suas parcelas
     * 
     * @param array $dados [
     *   'academia_id' => int,
     *   'categoria_id' => int,
     *   'tipo' => 'entrada'|'saida',
     *   'descricao' => string,
     *   'valor_total' => float,
     *   'data_competencia' => string (Y-m-d),
     *   'observacao' => ?string,
     *   'recorrente' => bool,
     *   'pagamento' => [
     *     'entrada' => ?[
     *       'valor' => float,
     *       'metodo' => string,
     *       'data' => string (Y-m-d),
     *       'pago' => bool
     *     ],
     *     'parcelas' => ?[
     *       'quantidade' => int,
     *       'metodo' => string,
     *       'primeira_data' => string (Y-m-d),
     *       'valor_parcela' => ?float (calculado se não informado)
     *     ]
     *   ]
     * ]
     */
    public function criarMovimentacao(array $dados): Movimentacao
    {
        return DB::transaction(function () use ($dados) {
            // Criar movimentação
            $movimentacao = Movimentacao::create([
                'academia_id' => $dados['academia_id'],
                'categoria_id' => $dados['categoria_id'],
                'tipo' => $dados['tipo'],
                'descricao' => $dados['descricao'],
                'valor_total' => $dados['valor_total'],
                'data_competencia' => $dados['data_competencia'],
                'observacao' => $dados['observacao'] ?? null,
                'recorrente' => $dados['recorrente'] ?? false,
            ]);

            $pagamento = $dados['pagamento'];
            $valorRestante = $dados['valor_total'];

            // Criar parcela de entrada (se houver)
            if (!empty($pagamento['entrada']) && $pagamento['entrada']['valor'] > 0) {
                $entrada = $pagamento['entrada'];
                $valorEntrada = $entrada['valor'];
                $valorRestante -= $valorEntrada;

                Parcela::create([
                    'movimentacao_id' => $movimentacao->id,
                    'academia_id' => $dados['academia_id'],
                    'numero' => 0, // 0 = entrada
                    'valor' => $valorEntrada,
                    'data_vencimento' => $entrada['data'],
                    'data_pagamento' => $entrada['pago'] ? $entrada['data'] : null,
                    'metodo_pagamento' => $entrada['metodo'],
                    'status' => $entrada['pago'] ? 'pago' : 'pendente',
                ]);
            }

            // Criar parcelas (se houver)
            if (!empty($pagamento['parcelas']) && $pagamento['parcelas']['quantidade'] > 0) {
                $parcelas = $pagamento['parcelas'];
                $qtdParcelas = $parcelas['quantidade'];
                $valorParcela = $parcelas['valor_parcela'] ?? round($valorRestante / $qtdParcelas, 2);
                $primeiraData = Carbon::parse($parcelas['primeira_data']);

                // Ajuste para centavos na última parcela
                $somaCalculada = $valorParcela * $qtdParcelas;
                $diferenca = $valorRestante - $somaCalculada;

                for ($i = 1; $i <= $qtdParcelas; $i++) {
                    $valorParcAtual = $valorParcela;
                    
                    // Adiciona diferença de centavos na última parcela
                    if ($i === $qtdParcelas && $diferenca != 0) {
                        $valorParcAtual += $diferenca;
                    }

                    $dataVencimento = $primeiraData->copy()->addMonths($i - 1);

                    Parcela::create([
                        'movimentacao_id' => $movimentacao->id,
                        'academia_id' => $dados['academia_id'],
                        'numero' => $i,
                        'valor' => $valorParcAtual,
                        'data_vencimento' => $dataVencimento,
                        'metodo_pagamento' => $parcelas['metodo'],
                        'status' => 'pendente',
                    ]);
                }
            }

            // Se não tem entrada nem parcelas, é pagamento à vista único
            if (empty($pagamento['entrada']) && empty($pagamento['parcelas'])) {
                Parcela::create([
                    'movimentacao_id' => $movimentacao->id,
                    'academia_id' => $dados['academia_id'],
                    'numero' => 1,
                    'valor' => $dados['valor_total'],
                    'data_vencimento' => $dados['data_competencia'],
                    'metodo_pagamento' => $pagamento['metodo'] ?? 'dinheiro',
                    'status' => 'pendente',
                ]);
            }

            return $movimentacao->load('parcelas', 'categoria');
        });
    }

    /**
     * Registra pagamento de uma parcela
     */
    public function registrarPagamento(
        Parcela $parcela,
        string $metodoPagamento,
        ?string $dataPagamento = null,
        ?string $observacao = null
    ): Parcela {
        $parcela->update([
            'status' => 'pago',
            'data_pagamento' => $dataPagamento ?? now(),
            'metodo_pagamento' => $metodoPagamento,
            'observacao' => $observacao,
        ]);

        return $parcela->fresh();
    }

    /**
     * Atualiza status de parcelas vencidas para "atrasado"
     */
    public function atualizarParcelasVencidas(int $academiaId): int
    {
        return Parcela::where('academia_id', $academiaId)
            ->where('status', 'pendente')
            ->where('data_vencimento', '<', now()->startOfDay())
            ->update(['status' => 'atrasado']);
    }

    /**
     * Retorna resumo financeiro do mês
     */
    public function getResumoMensal(int $academiaId, ?int $mes = null, ?int $ano = null): array
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;

        // Atualiza parcelas vencidas
        $this->atualizarParcelasVencidas($academiaId);

        // Entradas do mês (parcelas de movimentações tipo 'entrada')
        $entradasPagas = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'entrada'))
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mes)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor');

        $entradasPendentes = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'entrada'))
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->sum('valor');

        // Saídas do mês
        $saidasPagas = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'saida'))
            ->where('status', 'pago')
            ->whereMonth('data_pagamento', $mes)
            ->whereYear('data_pagamento', $ano)
            ->sum('valor');

        $saidasPendentes = Parcela::where('academia_id', $academiaId)
            ->whereHas('movimentacao', fn($q) => $q->where('tipo', 'saida'))
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->sum('valor');

        // Saldo
        $saldoRealizado = $entradasPagas - $saidasPagas;
        $saldoPrevisto = ($entradasPagas + $entradasPendentes) - ($saidasPagas + $saidasPendentes);

        return [
            'mes' => $mes,
            'ano' => $ano,
            'entradas' => [
                'pago' => (float) $entradasPagas,
                'pendente' => (float) $entradasPendentes,
                'total' => (float) ($entradasPagas + $entradasPendentes),
            ],
            'saidas' => [
                'pago' => (float) $saidasPagas,
                'pendente' => (float) $saidasPendentes,
                'total' => (float) ($saidasPagas + $saidasPendentes),
            ],
            'saldo' => [
                'realizado' => (float) $saldoRealizado,
                'previsto' => (float) $saldoPrevisto,
            ],
        ];
    }

    /**
     * Retorna parcelas pendentes/atrasadas
     */
    public function getParcelasPendentes(int $academiaId, ?string $tipo = null, int $limit = 20): array
    {
        $this->atualizarParcelasVencidas($academiaId);

        $query = Parcela::with(['movimentacao.categoria'])
            ->where('academia_id', $academiaId)
            ->whereIn('status', ['pendente', 'atrasado']);

        if ($tipo) {
            $query->whereHas('movimentacao', fn($q) => $q->where('tipo', $tipo));
        }

        return $query->orderBy('data_vencimento', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Gera despesas recorrentes do mês
     */
    public function gerarRecorrentes(int $academiaId, ?int $mes = null, ?int $ano = null): int
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        $dataCompetencia = Carbon::createFromDate($ano, $mes, 1);

        $recorrentes = Movimentacao::where('academia_id', $academiaId)
            ->where('recorrente', true)
            ->get();

        $criadas = 0;

        foreach ($recorrentes as $modelo) {
            // Verifica se já existe para este mês
            $existe = Movimentacao::where('academia_id', $academiaId)
                ->where('categoria_id', $modelo->categoria_id)
                ->where('descricao', $modelo->descricao)
                ->whereMonth('data_competencia', $mes)
                ->whereYear('data_competencia', $ano)
                ->exists();

            if (!$existe) {
                $nova = $modelo->replicate();
                $nova->data_competencia = $dataCompetencia;
                $nova->recorrente = false; // A cópia não é recorrente
                $nova->save();

                // Replica a estrutura de parcelas
                foreach ($modelo->parcelas as $parcela) {
                    $novaParcela = $parcela->replicate();
                    $novaParcela->movimentacao_id = $nova->id;
                    $novaParcela->data_vencimento = Carbon::parse($parcela->data_vencimento)
                        ->setMonth($mes)
                        ->setYear($ano);
                    $novaParcela->data_pagamento = null;
                    $novaParcela->status = 'pendente';
                    $novaParcela->save();
                }

                $criadas++;
            }
        }

        return $criadas;
    }
}
4. API Controllers
4.1 CategoriaFinanceiraController.php

<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\CategoriaFinanceira;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaFinanceiraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categorias = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->where('ativo', true)
            ->orderBy('tipo')
            ->orderBy('nome')
            ->get();

        return response()->json($categorias);
    }

    public function porTipo(Request $request, string $tipo): JsonResponse
    {
        $categorias = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->where('tipo', $tipo)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return response()->json($categorias);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo' => 'required|in:entrada,saida',
            'nome' => 'required|string|max:100',
            'cor' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
        ]);

        $categoria = CategoriaFinanceira::create([
            'academia_id' => $request->user()->academia_id,
            ...$validated,
        ]);

        return response()->json($categoria, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $categoria = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($categoria->sistema) {
            return response()->json(['message' => 'Categorias do sistema não podem ser alteradas.'], 403);
        }

        $validated = $request->validate([
            'nome' => 'sometimes|string|max:100',
            'cor' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
            'ativo' => 'sometimes|boolean',
        ]);

        $categoria->update($validated);

        return response()->json($categoria);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $categoria = CategoriaFinanceira::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($categoria->sistema) {
            return response()->json(['message' => 'Categorias do sistema não podem ser excluídas.'], 403);
        }

        if ($categoria->movimentacoes()->exists()) {
            return response()->json(['message' => 'Categoria possui movimentações vinculadas.'], 422);
        }

        $categoria->delete();

        return response()->json(null, 204);
    }
}
4.2 MovimentacaoController.php

<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Movimentacao;
use App\Services\FinanceiroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovimentacaoController extends Controller
{
    public function __construct(
        private FinanceiroService $financeiroService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Movimentacao::with(['categoria', 'parcelas'])
            ->where('academia_id', $request->user()->academia_id);

        // Filtros
        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('mes') && $request->has('ano')) {
            $query->whereMonth('data_competencia', $request->mes)
                  ->whereYear('data_competencia', $request->ano);
        }

        $movimentacoes = $query->orderBy('data_competencia', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($movimentacoes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias_financeiras,id',
            'tipo' => 'required|in:entrada,saida',
            'descricao' => 'required|string|max:255',
            'valor_total' => 'required|numeric|min:0.01',
            'data_competencia' => 'required|date',
            'observacao' => 'nullable|string',
            'recorrente' => 'boolean',
            
            // Pagamento
            'pagamento.entrada.valor' => 'nullable|numeric|min:0',
            'pagamento.entrada.metodo' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'pagamento.entrada.data' => 'nullable|date',
            'pagamento.entrada.pago' => 'nullable|boolean',
            
            'pagamento.parcelas.quantidade' => 'nullable|integer|min:1|max:48',
            'pagamento.parcelas.metodo' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'pagamento.parcelas.primeira_data' => 'nullable|date',
            'pagamento.parcelas.valor_parcela' => 'nullable|numeric|min:0.01',
            
            'pagamento.metodo' => 'nullable|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
        ]);

        $validated['academia_id'] = $request->user()->academia_id;

        $movimentacao = $this->financeiroService->criarMovimentacao($validated);

        return response()->json($movimentacao, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $movimentacao = Movimentacao::with(['categoria', 'parcelas'])
            ->where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        return response()->json($movimentacao);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $movimentacao = Movimentacao::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        // Não permite excluir se há parcelas pagas
        if ($movimentacao->parcelas()->where('status', 'pago')->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir movimentação com parcelas pagas.'
            ], 422);
        }

        $movimentacao->delete(); // Cascade deleta parcelas

        return response()->json(null, 204);
    }

    public function resumoMensal(Request $request): JsonResponse
    {
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        $resumo = $this->financeiroService->getResumoMensal(
            $request->user()->academia_id,
            $mes,
            $ano
        );

        return response()->json($resumo);
    }
}
4.3 ParcelaController.php

<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Controller;
use App\Models\Parcela;
use App\Services\FinanceiroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    public function __construct(
        private FinanceiroService $financeiroService
    ) {}

    public function pendentes(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo'); // 'entrada' ou 'saida'
        
        $parcelas = $this->financeiroService->getParcelasPendentes(
            $request->user()->academia_id,
            $tipo,
            50
        );

        return response()->json($parcelas);
    }

    public function pagar(Request $request, int $id): JsonResponse
    {
        $parcela = Parcela::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($parcela->status === 'pago') {
            return response()->json(['message' => 'Parcela já está paga.'], 422);
        }

        $validated = $request->validate([
            'metodo_pagamento' => 'required|in:dinheiro,pix,cartao_credito,cartao_debito,boleto,transferencia',
            'data_pagamento' => 'nullable|date',
            'observacao' => 'nullable|string',
        ]);

        $parcela = $this->financeiroService->registrarPagamento(
            $parcela,
            $validated['metodo_pagamento'],
            $validated['data_pagamento'] ?? null,
            $validated['observacao'] ?? null
        );

        return response()->json($parcela);
    }

    public function cancelar(Request $request, int $id): JsonResponse
    {
        $parcela = Parcela::where('academia_id', $request->user()->academia_id)
            ->findOrFail($id);

        if ($parcela->status === 'pago') {
            return response()->json(['message' => 'Não é possível cancelar parcela paga.'], 422);
        }

        $parcela->update(['status' => 'cancelado']);

        return response()->json($parcela);
    }
}
5. Rotas da API

// routes/api.php

Route::middleware('auth:sanctum')->prefix('gestor')->group(function () {
    // ... rotas existentes ...

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
    Route::get('movimentacoes/{id}', [MovimentacaoController::class, 'show']);
    Route::delete('movimentacoes/{id}', [MovimentacaoController::class, 'destroy']);

    // Parcelas
    Route::get('parcelas/pendentes', [ParcelaController::class, 'pendentes']);
    Route::post('parcelas/{id}/pagar', [ParcelaController::class, 'pagar']);
    Route::post('parcelas/{id}/cancelar', [ParcelaController::class, 'cancelar']);
});
6. Frontend Vue
6.1 Estrutura de Arquivos

resources/js/
├── views/gestor/
│   ├── Financeiro.vue          # Dashboard financeiro
│   ├── Despesas.vue            # Lista de saídas
│   └── Receitas.vue            # Lista de entradas (além de mensalidades)
├── components/gestor/
│   ├── FinanceiroResumoCard.vue
│   ├── MovimentacaoCard.vue
│   ├── ParcelaCard.vue
│   └── NovaMovimentacaoDialog.vue
6.2 NovaMovimentacaoDialog.vue (Componente Principal)

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { toast } from '@/components/ui/toast'

interface Categoria {
  id: number
  nome: string
  cor: string
  icone: string
}

const props = defineProps<{
  open: boolean
  tipo: 'entrada' | 'saida'
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
  'created': []
}>()

const categorias = ref<Categoria[]>([])
const salvando = ref(false)

// Form
const form = ref({
  categoria_id: undefined as string | undefined,
  descricao: '',
  valor_total: '',
  data_competencia: new Date().toISOString().split('T')[0],
  observacao: '',
  recorrente: false,
  
  // Pagamento
  tem_entrada: false,
  entrada_valor: '',
  entrada_metodo: 'pix' as string,
  entrada_data: new Date().toISOString().split('T')[0],
  entrada_pago: true,
  
  parcelar: false,
  parcelas_quantidade: 1,
  parcelas_metodo: 'cartao_credito' as string,
  parcelas_primeira_data: '',
})

// Computed
const valorTotal = computed(() => parseFloat(form.value.valor_total) || 0)
const valorEntrada = computed(() => parseFloat(form.value.entrada_valor) || 0)
const valorRestante = computed(() => valorTotal.value - valorEntrada.value)
const valorParcela = computed(() => {
  if (form.value.parcelas_quantidade > 0 && valorRestante.value > 0) {
    return valorRestante.value / form.value.parcelas_quantidade
  }
  return 0
})

const tituloModal = computed(() => 
  props.tipo === 'entrada' ? 'Nova Receita' : 'Nova Despesa'
)

// Watch para calcular data da primeira parcela
watch(() => form.value.entrada_data, (novaData) => {
  if (novaData && !form.value.parcelas_primeira_data) {
    const data = new Date(novaData)
    data.setMonth(data.getMonth() + 1)
    form.value.parcelas_primeira_data = data.toISOString().split('T')[0]
  }
})

// Fetch categorias
async function fetchCategorias() {
  try {
    const response = await axios.get(`/api/gestor/categorias-financeiras/${props.tipo}`)
    categorias.value = response.data
  } catch (e) {
    console.error('Erro ao carregar categorias:', e)
  }
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    fetchCategorias()
    resetForm()
  }
})

function resetForm() {
  form.value = {
    categoria_id: undefined,
    descricao: '',
    valor_total: '',
    data_competencia: new Date().toISOString().split('T')[0],
    observacao: '',
    recorrente: false,
    tem_entrada: false,
    entrada_valor: '',
    entrada_metodo: 'pix',
    entrada_data: new Date().toISOString().split('T')[0],
    entrada_pago: true,
    parcelar: false,
    parcelas_quantidade: 1,
    parcelas_metodo: 'cartao_credito',
    parcelas_primeira_data: '',
  }
}

function incrementarParcelas() {
  if (form.value.parcelas_quantidade < 48) {
    form.value.parcelas_quantidade++
  }
}

function decrementarParcelas() {
  if (form.value.parcelas_quantidade > 1) {
    form.value.parcelas_quantidade--
  }
}

async function handleSubmit() {
  salvando.value = true

  try {
    const payload: any = {
      categoria_id: Number(form.value.categoria_id),
      tipo: props.tipo,
      descricao: form.value.descricao,
      valor_total: valorTotal.value,
      data_competencia: form.value.data_competencia,
      observacao: form.value.observacao || null,
      recorrente: form.value.recorrente,
      pagamento: {},
    }

    // Entrada
    if (form.value.tem_entrada && valorEntrada.value > 0) {
      payload.pagamento.entrada = {
        valor: valorEntrada.value,
        metodo: form.value.entrada_metodo,
        data: form.value.entrada_data,
        pago: form.value.entrada_pago,
      }
    }

    // Parcelas
    if (form.value.parcelar && form.value.parcelas_quantidade > 0) {
      payload.pagamento.parcelas = {
        quantidade: form.value.parcelas_quantidade,
        metodo: form.value.parcelas_metodo,
        primeira_data: form.value.parcelas_primeira_data,
      }
    }

    // Se não tem entrada nem parcelas, define método padrão
    if (!form.value.tem_entrada && !form.value.parcelar) {
      payload.pagamento.metodo = 'pix'
    }

    await axios.post('/api/gestor/movimentacoes', payload)
    
    toast({
      title: props.tipo === 'entrada' ? 'Receita cadastrada!' : 'Despesa cadastrada!',
      description: 'Movimentação registrada com sucesso.',
    })

    emit('update:open', false)
    emit('created')
  } catch (error: any) {
    console.error('Erro ao salvar:', error)
    toast({
      title: 'Erro ao salvar',
      description: error.response?.data?.message || 'Ocorreu um erro. Tente novamente.',
      variant: 'destructive',
    })
  } finally {
    salvando.value = false
  }
}

function formatarMoeda(valor: number): string {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(valor)
}
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-lg max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>{{ tituloModal }}</DialogTitle>
        <DialogDescription>
          Preencha os dados da {{ tipo === 'entrada' ? 'receita' : 'despesa' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 mt-4">
        <!-- Categoria -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Categoria *
          </label>
          <Select v-model="form.categoria_id">
            <SelectTrigger>
              <SelectValue placeholder="Selecione uma categoria" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="cat in categorias"
                :key="cat.id"
                :value="String(cat.id)"
              >
                <div class="flex items-center gap-2">
                  <div 
                    class="w-3 h-3 rounded-full" 
                    :style="{ backgroundColor: cat.cor }"
                  />
                  {{ cat.nome }}
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Descrição -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Descrição *
          </label>
          <Input
            v-model="form.descricao"
            placeholder="Ex: Conta de energia - Janeiro"
            required
          />
        </div>

        <!-- Valor Total e Data -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
              Valor Total *
            </label>
            <Input
              v-model="form.valor_total"
              type="number"
              step="0.01"
              min="0.01"
              placeholder="0,00"
              required
            />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
              Data de Referência
            </label>
            <Input
              v-model="form.data_competencia"
              type="date"
              required
            />
          </div>
        </div>

        <!-- Recorrente -->
        <div class="flex items-center gap-2 bg-gray-50 p-3 rounded-lg">
          <input
            v-model="form.recorrente"
            type="checkbox"
            id="recorrente"
            class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
          />
          <label for="recorrente" class="text-sm font-medium text-gray-700">
            Despesa recorrente (repete todo mês)
          </label>
        </div>

        <!-- Forma de Pagamento -->
        <div class="border border-gray-200 rounded-lg p-4 space-y-4">
          <h3 class="font-medium text-gray-900">Forma de Pagamento</h3>

          <!-- Entrada -->
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <input
                v-model="form.tem_entrada"
                type="checkbox"
                id="tem_entrada"
                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
              />
              <label for="tem_entrada" class="text-sm font-medium text-gray-700">
                Tem entrada
              </label>
            </div>

            <div v-if="form.tem_entrada" class="pl-6 space-y-3 border-l-2 border-gray-200">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Valor da entrada</label>
                  <Input
                    v-model="form.entrada_valor"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="0,00"
                  />
                </div>
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Método</label>
                  <Select v-model="form.entrada_metodo">
                    <SelectTrigger class="h-9">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="pix">PIX</SelectItem>
                      <SelectItem value="dinheiro">Dinheiro</SelectItem>
                      <SelectItem value="cartao_debito">Débito</SelectItem>
                      <SelectItem value="transferencia">Transferência</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Data</label>
                  <Input
                    v-model="form.entrada_data"
                    type="date"
                  />
                </div>
                <div class="flex items-end pb-1">
                  <div class="flex items-center gap-2">
                    <input
                      v-model="form.entrada_pago"
                      type="checkbox"
                      id="entrada_pago"
                      class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    />
                    <label for="entrada_pago" class="text-sm text-gray-700">
                      Já foi pago
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Parcelamento -->
          <div class="space-y-3">
            <div class="flex items-center gap-2">
              <input
                v-model="form.parcelar"
                type="checkbox"
                id="parcelar"
                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
              />
              <label for="parcelar" class="text-sm font-medium text-gray-700">
                Parcelar {{ form.tem_entrada ? 'restante' : '' }}
              </label>
            </div>

            <div v-if="form.parcelar" class="pl-6 space-y-3 border-l-2 border-gray-200">
              <!-- Quantidade de parcelas -->
              <div>
                <label class="text-xs text-gray-600 mb-1 block">Quantidade de parcelas</label>
                <div class="flex items-center gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="decrementarParcelas"
                    :disabled="form.parcelas_quantidade <= 1"
                    class="h-9 w-9 p-0"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                  </Button>
                  <span class="w-12 text-center font-semibold text-lg">
                    {{ form.parcelas_quantidade }}
                  </span>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="incrementarParcelas"
                    :disabled="form.parcelas_quantidade >= 48"
                    class="h-9 w-9 p-0"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">Método</label>
                  <Select v-model="form.parcelas_metodo">
                    <SelectTrigger class="h-9">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="cartao_credito">Cartão de Crédito</SelectItem>
                      <SelectItem value="boleto">Boleto</SelectItem>
                      <SelectItem value="pix">PIX</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <label class="text-xs text-gray-600 mb-1 block">1ª Parcela</label>
                  <Input
                    v-model="form.parcelas_primeira_data"
                    type="date"
                  />
                </div>
              </div>

              <!-- Preview das parcelas -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-800">
                  <span class="font-semibold">{{ form.parcelas_quantidade }}x</span> de 
                  <span class="font-semibold">{{ formatarMoeda(valorParcela) }}</span>
                </p>
                <p v-if="form.tem_entrada && valorEntrada > 0" class="text-xs text-blue-600 mt-1">
                  + Entrada de {{ formatarMoeda(valorEntrada) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Observação -->
        <div>
          <label class="text-sm font-medium text-gray-700 mb-1.5 block">
            Observação (opcional)
          </label>
          <textarea
            v-model="form.observacao"
            rows="2"
            placeholder="Anotações adicionais..."
            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          />
        </div>

        <!-- Botões -->
        <div class="flex gap-2 pt-2">
          <Button
            type="button"
            variant="outline"
            class="flex-1"
            @click="$emit('update:open', false)"
            :disabled="salvando"
          >
            Cancelar
          </Button>
          <Button
            type="submit"
            class="flex-1"
            :disabled="salvando || !form.categoria_id || !form.descricao || valorTotal <= 0"
          >
            {{ salvando ? 'Salvando...' : 'Salvar' }}
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>
6.3 Adicionar ao Router

// resources/js/router/index.ts

{
  path: '/gestor/financeiro',
  name: 'gestor.financeiro',
  component: () => import('@/views/gestor/Financeiro.vue'),
  meta: { requiresAuth: true }
},
{
  path: '/gestor/despesas',
  name: 'gestor.despesas',
  component: () => import('@/views/gestor/Despesas.vue'),
  meta: { requiresAuth: true }
},
6.4 Atualizar BottomNav
Adicionar ícone de "Financeiro" ou "Carteira" na navegação inferior.

7. Integração com Mensalidades Existentes
Para integrar as mensalidades existentes ao novo módulo financeiro, criar um comando Artisan:


<?php
// app/Console/Commands/SyncMensalidadesFinanceiro.php

namespace App\Console\Commands;

use App\Models\Academia;
use App\Models\CategoriaFinanceira;
use App\Models\Mensalidade;
use App\Models\Movimentacao;
use App\Models\Parcela;
use Illuminate\Console\Command;

class SyncMensalidadesFinanceiro extends Command
{
    protected $signature = 'financeiro:sync-mensalidades';
    protected $description = 'Sincroniza mensalidades pagas com o módulo financeiro';

    public function handle()
    {
        $academias = Academia::all();

        foreach ($academias as $academia) {
            $categoria = CategoriaFinanceira::firstOrCreate([
                'academia_id' => $academia->id,
                'tipo' => 'entrada',
                'nome' => 'Mensalidades',
            ], [
                'cor' => '#10B981',
                'icone' => 'currency-dollar',
                'sistema' => true,
            ]);

            // Mensalidades pagas que não estão no financeiro
            $mensalidades = Mensalidade::where('academia_id', $academia->id)
                ->where('status', 'pago')
                ->get();

            foreach ($mensalidades as $mensalidade) {
                // Criar movimentação correspondente
                $movimentacao = Movimentacao::create([
                    'academia_id' => $academia->id,
                    'categoria_id' => $categoria->id,
                    'tipo' => 'entrada',
                    'descricao' => "Mensalidade - {$mensalidade->aluno->nome}",
                    'valor_total' => $mensalidade->valor,
                    'data_competencia' => $mensalidade->data_vencimento,
                ]);

                Parcela::create([
                    'movimentacao_id' => $movimentacao->id,
                    'academia_id' => $academia->id,
                    'numero' => 1,
                    'valor' => $mensalidade->valor,
                    'data_vencimento' => $mensalidade->data_vencimento,
                    'data_pagamento' => $mensalidade->data_pagamento,
                    'metodo_pagamento' => $mensalidade->metodo_pagamento ?? 'dinheiro',
                    'status' => 'pago',
                ]);
            }

            $this->info("Academia {$academia->nome}: {$mensalidades->count()} mensalidades sincronizadas.");
        }
    }
}
8. Testes
8.1 Cenário: Compra de Equipamento R$ 10.000

// Payload da API
{
    "categoria_id": 6, // Equipamentos
    "tipo": "saida",
    "descricao": "Esteira Movement T900",
    "valor_total": 10000,
    "data_competencia": "2025-02-01",
    "observacao": "Compra parcelada na loja X",
    "recorrente": false,
    "pagamento": {
        "entrada": {
            "valor": 2000,
            "metodo": "pix",
            "data": "2025-02-01",
            "pago": true
        },
        "parcelas": {
            "quantidade": 8,
            "metodo": "cartao_credito",
            "primeira_data": "2025-03-05"
        }
    }
}
Resultado esperado:

1 movimentação com valor_total = 10000
9 parcelas:
Parcela 0 (entrada): R$ 2.000, pago, 01/02/2025
Parcela 1: R$ 1.000, pendente, 05/03/2025
Parcela 2: R$ 1.000, pendente, 05/04/2025
...
Parcela 8: R$ 1.000, pendente, 05/10/2025
9. Checklist de Implementação
 Criar migrations
 Criar models com relacionamentos
 Criar seeder de categorias
 Implementar FinanceiroService
 Criar controllers da API
 Registrar rotas
 Criar componente NovaMovimentacaoDialog.vue
 Criar view Financeiro.vue (dashboard)
 Criar view Despesas.vue
 Atualizar BottomNav com nova aba
 Atualizar router
 Testar fluxo completo
 Criar comando de sincronização (opcional)