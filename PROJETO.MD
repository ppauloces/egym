# E-GYM — Plataforma de Gestão para Academias

## Visão Geral

O **E-GYM** é uma plataforma web monolítica para **gestão de academias**, desenvolvida com **Laravel + Vue 3**, com foco em **uso mobile**, operação rápida e arquitetura preparada para **white-label e multi-tenant**.

O sistema nasce atendendo uma única academia, mas desde o início é projetado para escalar como **SaaS**, permitindo múltiplas academias isoladas logicamente dentro do mesmo banco de dados.

---

## Objetivos do Projeto

- Gestão operacional simples e rápida para academias
- Interface mobile-first (uso principal via celular)
- Painel administrativo global para gestão do SaaS
- Base sólida para evolução futura (PWA, planos, cobranças, etc.)
- Código organizado, previsível e fácil de manter

---

## Stack Técnica

### Backend
- **Laravel 11+** (monólito)
- **Laravel Sanctum** (autenticação)
- **FilamentPHP** (Painel Admin / Master)
- Banco de dados relacional (MySQL ou PostgreSQL)

### Frontend (Aplicação da Academia)
- **Vue 3**
- **Vue Router**
- **Vite**
- **Tailwind CSS v3**
- **shadcn-vue**
- SPA interna servida pelo próprio Laravel

---

## Arquitetura Geral

- Backend e frontend no **mesmo repositório**
- Laravel responsável por **toda a regra de negócio**
- Vue atua apenas como **camada de visualização**
- Comunicação via **API interna**
- SPA renderizada em `resources/views/app.blade.php`

---

## Multi-Tenant / White-label

O sistema utiliza **multi-tenant por coluna**.

### Conceito
- Cada academia é um **tenant**
- Todas as tabelas de domínio possuem a coluna `academia_id`
- O isolamento é feito no backend via middleware e validações

### Regras
- Usuários gestores só acessam dados da própria academia
- Usuários master acessam todas as academias
- O frontend **não controla segurança**
- O backend sempre valida `academia_id` e permissões

---

## Tipos de Usuário

### MASTER
Administrador global do sistema (SaaS).

Pode:
- Acessar o **Painel Admin (Filament)**
- Cadastrar, editar e desativar academias
- Cadastrar usuários
- Criar outros usuários master
- Ativar/desativar usuários e academias

Não pertence a nenhuma academia.

---

### GESTOR
Usuário vinculado a uma academia.

Pode:
- Acessar apenas o **Painel da Academia (Vue)**
- Gerenciar alunos
- Registrar presenças
- Acompanhar informações financeiras básicas

Não pode:
- Acessar o painel admin
- Ver dados de outras academias

---

## Painel Admin (Master) — FilamentPHP

O **Painel Admin** é desenvolvido **exclusivamente com FilamentPHP** e é responsável por toda a gestão do SaaS.

### Funcionalidades
- Dashboard global
- CRUD de academias
- CRUD de usuários
- Ativação/desativação de academias
- Ativação/desativação de usuários

### Observações
- Nenhuma lógica sensível fica no frontend
- Filament atua diretamente sobre os models do Laravel
- Totalmente separado da SPA Vue

---

## Painel da Academia (Gestor)

Aplicação SPA desenvolvida em **Vue 3**, com foco em operação rápida no celular.

### Funcionalidades do MVP
- Dashboard da academia
- Gestão de alunos
- Controle simples de presença (entrada/saída)
- Visão financeira básica

---

## Autenticação e Autorização

- Login via email e senha
- Autenticação com **Laravel Sanctum**
- Após login, o backend retorna:
  - `id`
  - `role`
  - `academia_id` (quando aplicável)
  - dados básicos da academia

### Middlewares
- Autenticação (`auth:sanctum`)
- Autorização por role
- Middleware de tenant para isolamento por academia

---

## Organização do Frontend

```text
resources/js/
 ├─ app.ts
 ├─ App.vue
 ├─ router/
 │   ├─ index.ts
 │   ├─ admin.ts
 │   └─ app.ts
 ├─ layouts/
 │   ├─ AdminLayout.vue
 │   └─ AppLayout.vue
 ├─ views/
 │   ├─ login/
 │   ├─ admin/
 │   └─ app/
 └─ components/
     ├─ ui/        # shadcn-vue
     └─ shared/
