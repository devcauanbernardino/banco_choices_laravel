# Banco de Choices — Documentação do projeto

Este documento descreve o produto, a stack, a arquitetura, as paletas de cor, os fluxos e os pontos de configuração da aplicação **Banco de Choices** (código em Laravel no repositório `banco_choices_laravel`).

---

## 1. O que é o projeto

**Banco de Choices** é uma plataforma web de estudo para **medicina** (foco em preparação com **questões comentadas** e **simulados**). O utilizador escolhe **matérias**, contrata **planos de acesso** (mensal, semestral, anual ou compra adicional de matérias), paga via **Mercado Pago** e acede a:

- **Painel (dashboard)** com resumo e atalhos;
- **Banco de questões** — configurar simulados (disciplina, quantidade, modo estudo vs exame);
- **Questionário** — responder ao simulado;
- **Resultado** — revisão após concluir;
- **Histórico de simulados** e **estatísticas**;
- **Perfil** — dados da conta, palavra-passe, tema claro/escuro.

A **landing** (`/`) apresenta o produto; o **cadastro** pode ser feito por fluxo guiado (matérias → plano → checkout) ou por formulário na home; o **login** autentica utilizadores com e-mail e senha (campo na base de dados: `senha`, com `getAuthPassword()` no modelo `User`).

---

## 2. Domínio de negócio (resumo)

| Conceito | Descrição |
|----------|-----------|
| **Matérias** | Disciplinas de conteúdo (ex.: biologia, microbiologia). O utilizador só vê o que comprou ou lhe foi atribuído na pivot `usuarios_materias`. |
| **Planos** | `monthly` (30 dias), `semester` (180 dias), `annual` (365 dias). Preços e dias vêm de `config/signup.php`. |
| **Pedidos** | Registos em `pedidos` com estado (`awaiting_payment`, `completed`, etc.) e referência externa tipo `ORDER-…` em `stripe_payment_id` (nome histórico; hoje usado com Mercado Pago). |
| **Itens do pedido** | `pedidos_itens` liga pedido a matéria, plano, preço e data de expiração. |
| **Simulados** | `historico_simulados` guarda tentativas; o fluxo passa por `SimulationController` e `ResultController`. |
| **Pagamentos** | Preferência **Checkout Pro** Mercado Pago; notificação IPN/webhook e/ou sincronização na página `/payment-success` com `payment_id` ou pesquisa por `external_reference`. |
| **Addon** | Utilizador autenticado pode comprar **matérias extra** (`AddonController`) com fluxo semelhante ao checkout. |

---

## 3. Stack técnica

| Camada | Tecnologia |
|--------|------------|
| Backend | **PHP 8.2+**, **Laravel 12** |
| Base de dados | Por defeito **SQLite** (`.env`); compatível com **MySQL**/MariaDB em produção |
| Frontend | **Blade**, **Bootstrap 5**, **Bootstrap Icons**, **Vite** (assets onde aplicável) |
| Pagamentos | SDK oficial **`mercadopago/dx-php`** |
| Internacionalização | JSON em `lang/` — `es_AR` (predefinido na sessão), `pt_BR`, `en_US` |
| Outros | `league/iso3166` (países no checkout), sessões em BD (`sessions`), tokens de reset de palavra-passe |

Comandos úteis: `php artisan serve`, `php artisan migrate`, `php artisan config:clear`.

---

## 4. Estrutura de pastas (alto nível)

```
app/
  Http/Controllers/     # Páginas, checkout, simulação, perfil, webhook MP, auth
  Http/Middleware/    # SetLocale, etc.
  Models/               # User, Materia, Pedido, PedidoItem, HistoricoSimulado, …
  Mail/                 # WelcomeNewUser, AccessGrantedExistingUser
  Services/MercadoPago/ # Fulfillment, validação de assinatura webhook
  Support/              # Branding, CheckoutDraftSession, Countries, SignupFlow, …
config/                 # signup, mercadopago, auth, branding, …
database/migrations/
lang/                   # pt_BR.json, en_US.json, es_AR.json
public/assets/css/      # Estilos por área (landing, login, tema, painel)
resources/views/        # Blade por layout (public, app) e partials
routes/web.php
```

---

## 5. Rotas e áreas da aplicação

### Público
- `/` — Home (`PageController`)
- `/login`, `/logout`, `/cadastro`
- **Esqueci a palavra-passe**: `/esqueci-senha`, `/redefinir-senha/{token}`
- **Fluxo de registo**: `/selecionar-materias`, `/selecionar-plano`
- **Checkout**: `GET /checkout-mercadopago`, `POST /process-payment-mp`
- **Sucesso pagamento**: `GET /payment-success`
- `/set-locale` — idioma e região

### Webhook (sem CSRF)
- `POST /webhook-mercadopago`

### Autenticado (`auth` middleware)
- Dashboard, banco de questões, criar/mostrar/processar simulado, resultado
- Histórico, estatísticas, perfil
- Comprar matérias/plano (addon): `/comprar-materias`, `/checkout-addon`, `POST /process-payment-addon`

### Páginas (telas) do projeto — o que existe e para que serve

Abaixo estão as **rotas que devolvem uma página HTML** (views Blade), agrupadas por área. Endpoints só **POST** (login, criar simulado, webhook, etc.) não são “páginas” no sentido de ecrã navegável; ficam descritos noutras secções.

| Rota (exemplo) | Nome da rota | Finalidade |
|----------------|--------------|------------|
| `/` | `home` | **Landing**: apresentação do produto, preços, cadastro (POST para `/cadastro`), link para login. |
| `/login` | `login` | **Entrar**: e-mail e senha; redireciona para o painel após sucesso. |
| `/esqueci-senha` | `password.request` | **Esqueci a senha**: pedir link de redefinição por e-mail. |
| `/redefinir-senha/{token}` | `password.reset` | **Nova senha**: definir palavra-passe após clicar no link do e-mail. |
| `/selecionar-materias` | `signup.materias` | **Registo (passo 1)**: escolher matérias antes do plano (fluxo guiado). |
| `/selecionar-plano` | `signup.plano` | **Registo (passo 2)**: escolher plano de acesso antes do checkout. |
| `/checkout-mercadopago` | `checkout.show` | **Checkout Mercado Pago**: dados e revisão antes de ir ao pagamento (novo utilizador / fluxo signup). |
| `/payment-success` | `checkout.success` | **Retorno do pagamento**: estado (sucesso, pendente, erro) e sincronização com o Mercado Pago. |
| `/comprar-materias` | `addon.materias` | **Comprar matérias extra** (autenticado): listar matérias ainda não adquiridas e seguir para o checkout addon. |
| `/checkout-addon` | `addon.checkout` | **Checkout de addon**: resumo e pagamento de matérias adicionais para quem já tem conta. |
| `/comprar-plano` | `addon.plano` | **Não é uma página**: redireciona para `addon.materias` ou `addon.checkout` conforme sessão (URL legada). |
| `/dashboard` | `dashboard` | **Painel**: resumo, atalhos e visão geral após login. |
| `/bancoperguntas` | `questionbank` | **Banco de questões**: escolher matéria, quantidade, modo (estudo/exame) e iniciar simulado. |
| `/questionario` | `simulation.show` | **Questionário**: responder às questões do simulado em curso (sessão). |
| `/resultado` | `result.show` | **Resultado do simulado atual**: revisão logo após terminar (dados em sessão). |
| `/resultado/historico/{historico}` | `simulation.result` | **Resultado do histórico**: rever um simulado guardado (apenas do próprio utilizador). |
| `/simulados` | `history` | **Histórico de simulados**: lista de tentativas anteriores e link para rever resultados. |
| `/estatisticas` | `stats` | **Estatísticas**: KPIs globais (questões, acertos, simulados), média, melhor matéria, desempenho por matéria, evolução recente e resumo semanal. |
| `/perfil` | `profile.show` | **Perfil**: dados da conta, alteração de senha e preferências (ex.: tema). |

**Resumo:** são **18 ecrãs** distintos listados na tabela (incluindo a landing e o retorno de pagamento); `/comprar-plano` é só redirecionamento.

**Views Blade principais associadas** (para cruzar com o código): `pages/index`, `auth/login`, `auth/forgot-password`, `auth/reset-password`, `signup/select-materias`, `signup/select-plano`, `checkout/show`, `checkout/success`, `addon/materias`, `addon/checkout`, `dashboard/index`, `questionbank/index`, `simulation/show`, `simulation/result`, `history/index`, `stats/index`, `profile/show`. Layouts: `layouts/public` (público) e `layouts/app` (área autenticada).

---

## 6. Paletas de cores e identidade visual

A marca gira em torno do **roxo institucional** e de **fundos neutros**; há **tema claro** e **escuro** no painel (`data-theme="dark"` em `theme-tokens.css` / `theme-app.css`).

### Cores principais (marca)
| Uso | Hex | Notas |
|-----|-----|--------|
| **Primária (Bootstrap)** | `#6a0392` | `--bs-primary`, botões, links de marca |
| RGB equivalente | `106, 3, 146` | `--bs-primary-rgb` |
| **Primária escura** | `#4a0072` | `--primary-dark`, gradientes landing |
| **Roxo gradiente** | `#2c003e`, `#1a0026` | Fim dos gradientes longos (landing/index) |
| **Sidebar ativo / acento** | `#7c3aed` | Item ativo na barra lateral (`--sidebar-active`) |
| **Roxo utilitário painel** | `#6a0392` | `--bc-purple` em `private-app.css` |

### Superfícies e texto (tema claro — painel)
| Token / uso | Valor |
|-------------|--------|
| Fundo app | `#f0f2f7` (`--app-bg`) |
| Superfície cartões | `#ffffff` (`--app-surface`) |
| Superfície secundária | `#f8f9fc` (`--app-surface-2`) |
| Texto principal | `#1c1c1f` (`--app-text`) |
| Texto muted | `#6b7280` (`--app-muted`) |
| Bordas | `rgba(15, 23, 42, 0.08)` (`--app-border`) |

### Tema escuro (painel)
| Token | Valor |
|-------|--------|
| Fundo | `#0b0b0f` |
| Superfície | `#14141a` / `#1a1a22` |
| Texto | `#ececf1` |
| Borda | `rgba(255, 255, 255, 0.08)` |

### Barra lateral (sidebar)
- Gradiente escuro: `linear-gradient(165deg, #1a0b24 0%, #0f0f14 55%, #0a0a0d 100%)`
- Offcanvas mobile: fundo `#0f0f14`
- Links: branco com opacidade; destaque roxo/violeta nos ativos

### Login / registo (`login.css`)
| Token | Valor |
|-------|--------|
| Superfície formulário | `#faf8fc` (`--login-surface`) |
| Borda inputs | `#e4dce8` |
| Fundo inputs suave | `#f7f4f9` |
| Fundo página | `#f0eef3` |
| Texto muted | `#6b7280` |
| Anel foco | `rgba(106, 3, 146, 0.22)` |

### Landing / index (`index.css`, `landing-page.css`)
- Mesma primária `#6a0392` e gradiente animado em hero
- Secções claras: `#f8fafc`, cards brancos, texto slate (`#334155`, `#64748b`, `#94a3b8`)

### Checkout / signup (views inline)
- Roxo acento: `#6a0392` (`--accent-purple`)
- Sucesso: verde tipo `#10b981`
- Pendente: âmbar `#f59e0b`
- Erro: vermelho `#ef4444`

### Tipografia
- **Inter** em landing, login e várias páginas públicas
- **Poppins** em títulos fortes (checkout, sucesso)
- **Segoe UI** mencionada em `private-app.css` para área privada (com fallback system-ui)

### Logo
- Configurável via `BRAND_LOGO` / `config/branding.php`
- Fallback: `App\Support\Branding` procura ficheiros `logo-bd-transparente` em `public/img` ou `public/assets/img`

---

## 7. Base de dados (migrations)

| Tabela | Função |
|--------|--------|
| `users` | Utilizadores (`nome`, `email`, `senha`) |
| `password_reset_tokens` | Tokens de recuperação de senha |
| `materias` | Catálogo de matérias |
| `usuarios_materias` | Matérias adquiridas por utilizador |
| `historico_simulados` | Registo de simulados realizados |
| `pedidos` | Encomendas / pagamentos |
| `pedidos_itens` | Linhas por matéria e expiração |
| `mp_payment_processed` | Idempotência de notificações Mercado Pago |
| `sessions` | Sessões Laravel |

---

## 8. Autenticação e segurança

- **Session guard** `web`; credenciais usam campo `senha` com `Hash` / `password_hash` conforme fluxo.
- **Reset de senha**: tabela `password_reset_tokens`, rotas nomeadas `password.*`, e-mail via `Mail` (ou log em dev).
- **CSRF** em formulários; exceção para `webhook-mercadopago`.
- **Webhook MP**: validação opcional de assinatura com `MP_WEBHOOK_SECRET`.

---

## 9. Mercado Pago (comportamento)

- Criação de **preferência** com itens, `payer`, `back_urls` (`success`, `failure`, `pending`), `external_reference` (ID do pedido), `metadata` (email, nome, plano, matérias, duração).
- `notification_url` só é enviada se a base URL **não** for localhost (requisito típico da API).
- `auto_return` só com **HTTPS** na base das URLs.
- **Fulfillment** ao aprovar pagamento: atualiza pedido, cria/atualiza utilizador, envia e-mails (`WelcomeNewUser` / `AccessGrantedExistingUser`), insere `pedidos_itens`.
- **Página de sucesso**: sincroniza pagamento por `payment_id` / `collection_id` ou pesquisa API por `external_reference`; SQL de fulfillment compatível **SQLite e MySQL**.

Variáveis relevantes: `MP_ACCESS_TOKEN`, `MP_PUBLIC_KEY`, `MP_CURRENCY_ID`, `SITE_URL` / `APP_URL`, `MP_CHECKOUT_BASE_URL` (túnel HTTPS em dev).

---

## 10. Internacionalização

- Locales: **es_AR** (padrão se cookie/sessão inválidos), **pt_BR**, **en_US**.
- Traduções em ficheiros JSON por chave (`login.*`, `signup.*`, `dashboard.*`, etc.).
- Middleware `SetLocale` aplica locale a partir de sessão e cookie `bclocale`.

---

## 11. Ficheiros de configuração úteis

| Ficheiro | Conteúdo |
|----------|----------|
| `.env` / `.env.example` | APP_URL, DB, MAIL, MP, SITE_URL |
| `config/signup.php` | Planos e preço addon |
| `config/mercadopago.php` | Tokens, moeda, URLs |
| `config/auth.php` | Guard, reset de senha |
| `config/branding.php` | Logo opcional |

---

## 12. Dependências PHP principais (composer)

- `laravel/framework`
- `mercadopago/dx-php`
- `league/iso3166`

---

## 13. O que este documento não substitui

- Políticas de privacidade e textos legais na landing (`#privacidad`, `#terminos`) — conteúdo nas views e traduções.
- Credenciais reais de produção — nunca versionadas.
- Detalhes de conteúdo pedagógico (banco de questões): depende dos dados importados na base.

---

*Última atualização: documento gerado para o repositório Banco de Choices Laravel; ajustar datas e rotas se o projeto evoluir.*
