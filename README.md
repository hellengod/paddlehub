# PaddleHub

PaddleHub é uma aplicação web em desenvolvimento para a comunidade de canoagem e remada. O projeto foi pensado para reunir, em um único ambiente, autenticação de usuários, navegação por áreas temáticas e recursos voltados à descoberta de rios, organização de encontros e construção de comunidade.

## Visão geral

No estado atual, o projeto já possui uma base fullstack funcional com:

- cadastro, login, logout e recuperação do usuário autenticado
- área interna protegida por autenticação
- layout principal com sidebar, topbar e navegação entre telas
- tela de perfil com edição local de bio, rio base, capa e recorte de avatar
- testes de API para o fluxo de autenticação

As áreas de mapa, eventos, rios e comunidade já existem na navegação, mas ainda estão em fase inicial de implementação.

## Stack

### Frontend

- Vue 3
- TypeScript
- Vite
- Vue Router
- Axios

### Backend

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- PHPUnit

## Estrutura do repositório

```text
Paddlehub/
  backend/   API Laravel, autenticação, rotas e testes
  frontend/  aplicação Vue com interface e consumo da API
  docs/      documentação de estudo e decisões de interface
```

## Funcionalidades já presentes

### Autenticação

O backend já expõe endpoints para cadastro, login, logout e consulta do usuário autenticado. No frontend, esse fluxo é consumido por um composable dedicado, com controle de estado e proteção de rotas.

### Área autenticada

Depois do login, o usuário acessa uma área interna com:

- home
- mapa
- eventos
- rios
- comunidade
- perfil

### Perfil

A tela de perfil é o trecho mais avançado da interface neste momento. Ela já permite:

- exibir nome, bio, rio base, avatar e capa
- abrir modal de edição do perfil
- selecionar capa com pré-visualização local
- selecionar avatar e ajustar o recorte antes de aplicar

Neste estágio, as alterações visuais do perfil ainda ficam no frontend e não são persistidas no backend.

## Endpoints disponíveis

```text
POST /api/register
POST /api/login
POST /api/logout
GET  /api/user
```

## Como executar o projeto

### 1. Backend

Na pasta `backend/`:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Se preferir usar o fluxo definido no `composer.json`, também é possível iniciar o ambiente com:

```bash
composer dev
```

Antes de rodar, ajuste o arquivo `.env` com a configuração de banco de dados da sua máquina.

### 2. Frontend

Na pasta `frontend/`:

```bash
npm install
npm run dev
```

O frontend consome a API usando a variável `VITE_API_BASE_URL`. Se necessário, configure esse valor no `.env` do frontend apontando para a URL do backend local, por exemplo:

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/
```

## Documentação interna

O repositório já possui materiais de apoio para entendimento da arquitetura e da interface:

- `backend/README.md`
- `docs/profile-view-study-spec.md`
- `docs/profile-avatar-crop-study-spec.md`
- `docs/profile-avatar-crop-modal-study-spec.md`

## Próximas etapas

Os próximos blocos naturais de evolução do projeto são:

- persistência real dos dados de perfil no backend
- implementação das telas de rios, mapa, eventos e comunidade
- expansão da cobertura de testes além da autenticação

## Licença

Projeto em desenvolvimento para estudo, prática e portfólio.
