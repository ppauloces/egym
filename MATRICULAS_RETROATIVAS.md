# 📅 Sistema de Matrículas Retroativas

## Como Funciona

O gestor **escolhe** se quer gerar histórico de mensalidades antigas ou começar apenas com a próxima mensalidade.

---

## 🎯 Cenários Suportados

### 1️⃣ **Matrícula Normal (Não Retroativa)**

**Exemplo:**
- Hoje: 03/02/2026
- Gestor cadastra aluno com data de matrícula: 03/02/2026
- Plano: Mensal (30 dias)
- ☐ Matrícula Retroativa (desmarcado)

**O que acontece:**
```
✅ Sistema calcula: 03/02 + 30 dias = 05/03
✅ Gera 1 mensalidade:
   - Venc: 05/03/2026 | Status: pendente
```

### 2️⃣ **Matrícula Retroativa (Com Controle)**

**Exemplo:**
- Hoje: 03/02/2026
- Gestor cadastra aluno com data de matrícula: 02/01/2026
- Plano: Mensal (30 dias)
- ☑ Matrícula Retroativa (marcado)
- Data Próxima Mensalidade: 05/03/2026

**O que acontece:**
```
✅ Sistema NÃO gera mensalidades antigas
✅ Gera apenas 1 mensalidade:
   - Venc: 05/03/2026 | Status: pendente
✅ Evita cobranças retroativas desnecessárias
```

### 2️⃣ **Implantação do Sistema (Academia já existe)**

**Exemplo:**
- Academia tem 50 alunos ativos
- Matrículas variadas (algumas de meses atrás)
- Sistema sendo implantado hoje

**Comando para processar:**
```bash
php artisan mensalidades:processar-retroativas
```

**O que acontece:**
```
✅ Processa todos os alunos ativos
✅ Detecta quais têm matrícula retroativa
✅ Gera todas as mensalidades desde a matrícula até hoje
✅ Define status correto (atrasado/pendente)
✅ Atualiza contadores automaticamente
```

### 3️⃣ **Correção de Dados**

Se precisar reprocessar um aluno específico:
```bash
php artisan mensalidades:processar-retroativas --force
```

⚠️ **Atenção:** `--force` deleta e recria todas as mensalidades!

---

## 🔄 Lógica de Geração

### Cálculo Automático:

```
Data Matrícula: 02/01/2026
Plano: Mensal (30 dias)
Hoje: 03/02/2026

Mensalidades Geradas:
1. Venc: 01/02/2026 (02/01 + 30 dias) → Status: atrasado ❌
2. Venc: 03/03/2026 (01/02 + 30 dias) → Status: pendente ⏳
3. Venc: 02/04/2026 (03/03 + 30 dias) → Status: pendente ⏳
```

### Regras:

1. **Gera até** o vencimento ser no futuro
2. **Atrasado** se vencimento < hoje
3. **Pendente** se vencimento >= hoje
4. **Não duplica** mensalidades existentes

---

## 🚀 Uso no Sistema

### Automático (Recomendado):

✅ Ao cadastrar aluno com data retroativa → **Gera tudo automaticamente**
✅ Ao acessar Dashboard/Mensalidades → **Atualiza status**
✅ Ao marcar como pago → **Gera próxima mensalidade**

### Manual (Manutenção):

```bash
# Processar novos alunos sem mensalidades
php artisan mensalidades:processar-retroativas

# Reprocessar todos (cuidado!)
php artisan mensalidades:processar-retroativas --force

# Atualizar status de vencidas
php artisan mensalidades:atualizar-vencidas
```

---

## 📊 Exemplos Práticos

### Exemplo 1: Aluno Novo Retroativo

```php
// Cadastro via frontend
POST /api/gestor/alunos
{
  "nome": "João Silva",
  "data_matricula": "2026-01-02",  // 32 dias atrás
  "plano_id": 1  // Mensal
}

// Sistema gera automaticamente:
✅ Mensalidade 1: 01/02 (atrasado)
✅ Mensalidade 2: 03/03 (pendente)
```

### Exemplo 2: Implantação em Academia Existente

```bash
# Academia com 50 alunos ativos
# Matrículas de 1 a 6 meses atrás

$ php artisan mensalidades:processar-retroativas

🔄 Processando matrículas retroativas...
Total de alunos a processar: 50
[████████████████████████████] 100%

✅ Processamento concluído!
┌────────────────────┬────────────┐
│ Métrica            │ Quantidade │
├────────────────────┼────────────┤
│ Alunos processados │ 50         │
│ Erros              │ 0          │
│ Total de alunos    │ 50         │
└────────────────────┴────────────┘

🔄 Atualizando status de mensalidades vencidas...
Total: 127 mensalidade(s) marcada(s) como atrasada(s)
```

---

## ⚙️ Configuração Automática (Opcional)

Para processar automaticamente todo dia:

**1. Adicione ao `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule)
{
    // Processa retroativas diariamente (meia-noite)
    $schedule->command('mensalidades:processar-retroativas')
             ->daily();
    
    // Atualiza vencidas (a cada 6 horas)
    $schedule->command('mensalidades:atualizar-vencidas')
             ->everySixHours();
}
```

**2. Configure o cron no servidor:**

```bash
* * * * * cd /caminho/para/egym && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 Vantagens

✅ **Zero trabalho manual** - Sistema detecta e gera automaticamente
✅ **Histórico completo** - Todas as mensalidades desde a matrícula
✅ **Status correto** - Atrasado/Pendente definido automaticamente
✅ **Sem duplicatas** - Verifica antes de criar
✅ **Seguro** - Não sobrescreve mensalidades pagas

---

## 🔍 Monitoramento

**Verificar mensalidades de um aluno:**

```bash
# No Filament Admin
Academia → Alunos → [Ver Aluno] → Aba "Mensalidades"

# Ou via Tinker
php artisan tinker
> $aluno = App\Models\Aluno::find(1);
> $aluno->mensalidades;
```

---

## ⚠️ Importante

1. **Mensalidades pagas** nunca são recriadas (mesmo com --force)
2. **Alunos inativos** não são processados
3. **Sem plano** não gera mensalidades
4. **Sem data de matrícula** não gera mensalidades

---

## 💡 Dicas

- Use o comando `--force` apenas em casos excepcionais
- Para corrigir um único aluno, edite-o no Filament e remova as mensalidades manualmente
- O sistema é idempotente: pode rodar múltiplas vezes sem problemas
- A geração automática acontece no cadastro, não precisa rodar comando toda hora
