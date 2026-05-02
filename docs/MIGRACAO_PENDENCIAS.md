# Pendências da Migração PHP Puro → Laravel

**Projeto:** Banco de Choices — plataforma de simulados de Microbiologia/Biologia com pagamento MercadoPago.
**Contexto:** Migração do projeto PHP puro (`C:\xampp\htdocs\banco_choices`) para Laravel 12 (`C:\xampp\htdocs\banco_choices_laravel`). A migração foi feita de forma incompleta e com vários erros que este documento cataloga.

Última atualização: 2026-04-15

---

## Triagem 2026-04-28 (produção / código atual)

Vários itens deste documento **já foram cobertos no código** desde a auditoria original (rotas de addon, `AddonController`, fluxo checkout addon, chaves `result.*` em `pt_BR.json`, etc.). Use este ficheiro sobretudo para **UX/conteúdo** e comparação com o PHP antigo; para checklist operacional ver [`PRODUCAO_PENDENCIAS.md`](PRODUCAO_PENDENCIAS.md).

---

## 1. Resumo executivo

| Métrica | Valor |
|---|---|
| Views totais auditadas | 18 |
| Views OK | 6 |
| Views parciais (funcionam, mas incompletas) | 4 |
| Views quebradas (chaves erradas, quebram visualmente) | 5 |
| Views **faltando completamente** | 3 |
| Chaves de tradução faltando nos JSONs do Laravel | 60+ |
| Fluxos de negócio faltando | 1 (compra de matérias extras / addon) |
| Funções/helpers PHP não migrados | 3 (addon pricing helpers) |
| Métodos de model não migrados | 1 (`buscarUltimoPlanoIdParaUsuarioId`) |

---

## 2. Problemas sistemáticos (padrões que se repetem)

Estes são erros que aparecem em várias views — entender eles ajuda a corrigir em lote.

### 2.1. Chaves de tradução inventadas com underscore em vez de ponto

A view usa `__('xxx.yyy_zzz')` (com underscore) quando a chave correta no JSON usa ponto: `xxx.yyy.zzz`.

**Exemplo:** Laravel usa `index.hero_title`, JSON tem `index.hero.title`.

**Já corrigido:** `pages/index.blade.php` (commit `2d97cf8`).

### 2.2. Namespace errado nas chaves

A view inventa um prefixo (`checkout.*`, `result.*`) para chaves que no original estão em outro namespace (`signup.checkout.*`, `signup.payment.*`).

**Exemplo:** `checkout/success.blade.php` usa `checkout.status_approved_title` quando deveria ser `signup.payment.confirmed_h1`.

### 2.3. Views drasticamente simplificadas

Algumas views Blade têm 1/4 do conteúdo do original — seções educacionais, cards de ajuda, estados vazios, dicas foram removidos.

**Exemplo:** `signup/select-materias.blade.php` tem ~208 linhas; o original tem ~799.

### 2.4. Fluxo de addon (compra extra) ausente

Toda a funcionalidade de "usuário logado compra matérias adicionais" foi esquecida na migração. 3 views, 1 controller, 3 helpers de config, 1 método de model, 40+ chaves de i18n, 4 rotas — tudo faltando.

---

## 3. Checklist priorizado

### 🔴 CRÍTICO — quebra de funcionalidade ou UX

- [ ] **Corrigir namespace de chaves em `checkout/show.blade.php`** (substituir `checkout.*` por `signup.checkout.*`)
- [ ] **Corrigir namespace de chaves em `checkout/success.blade.php`** (substituir `checkout.*` por `signup.payment.*` / `signup.page_title.payment_*`)
- [ ] **Corrigir namespace de chaves em `simulation/result.blade.php`** (adicionar prefixo correto ou criar chaves `result.*` no JSON)
- [ ] **Reescrever `signup/select-materias.blade.php`** fielmente ao `selecionar-materias.php` original (adicionar: what_title, how1-3, tip, empty state, contagem JS)
- [ ] **Completar `signup/select-plano.blade.php`** (adicionar resumo das matérias selecionadas, chips, todas as features dos planos)
- [ ] **Reescrever `checkout/show.blade.php`** fielmente ao `checkout-mercadopago.php` original (termos de uso, notas legais, nota de acesso pós-pagamento)
- [ ] **Reescrever `checkout/success.blade.php`** fielmente ao `payment-success.php` original (3 próximos passos, tratamento dos estados approved/pending/rejected)
- [ ] **Criar fluxo de addon (compra de matérias extras)**:
  - [ ] Rota + controller `AddonController` com 4 métodos (`selecionarMaterias`, `storeMaterias`, `selecionarPlano`, `storePlano`)
  - [ ] View `resources/views/addon/materias.blade.php`
  - [ ] View `resources/views/addon/plano.blade.php`
  - [ ] View `resources/views/addon/checkout.blade.php`
  - [ ] Rotas: `/comprar-materias`, `/comprar-plano`, `/checkout-addon`, `POST /process-payment-addon`
  - [ ] Handler no `CheckoutController` (ou novo controller) para processar pagamento addon
  - [ ] Processamento do webhook para inserir em `pedidos_itens` corretamente em caso de addon
  - [ ] Link no menu/sidebar para `/comprar-materias` (quando autenticado)
- [ ] **Adicionar helpers no `config/signup.php`**:
  - [ ] `addon_price_per_materia(): float` — preço fixo por matéria extra (default 29.90)
  - [ ] `addon_plan_fallback_id(): string` — plano padrão quando conta nova (default `semester`)
  - [ ] `addon_resolve_plan_for_extra_materias(?string $planId): array` — decide duração/plano da próxima compra
- [ ] **Adicionar método no `User` model**: `buscarUltimoPlanoIdParaUsuarioId(int $id): ?string` (JOIN em `pedidos` + `pedidos_itens`)
- [ ] **Adicionar 40+ chaves `addon.*`** em `lang/pt_BR.json`, `lang/es_AR.json`, `lang/en_US.json` (ver apêndice)
- [ ] **Adicionar 11 chaves `result.*`** (ou substituir pelo namespace correto no template)

### 🟠 MÉDIO — funciona mas incompleto

- [ ] **Completar `auth/login.blade.php`**: adicionar 4 chaves `login.sidebar_*` e bloco de sidebar com social proof
- [ ] **Adicionar 5 chaves `quiz.*`** (correct, incorrect, finish, prev, next) em todos os JSONs
- [ ] **Adicionar 4 chaves extras `sidebar.*`** (theme_group_aria, theme_light_aria, theme_dark_aria, theme_switch_to_dark/light)
- [ ] **Adicionar 2 chaves `nav.*`** (buy_subjects, buy_subjects_short) — necessárias para o link do addon no menu
- [ ] **Adicionar chave `dashboard.buy_more_cta`** e renderizar o CTA "Quer mais matérias? Compre pelo painel" no dashboard
- [ ] **Migrar `config/test_users.php`** (lista de e-mails de teste que pulam as matérias default no login)
- [ ] **Extrair `SimulationTimer` como classe dedicada** em `app/Support/SimulationTimer.php` (hoje está inline no controller)
- [ ] **Portar as ~175 chaves restantes faltantes** do `pt_BR.php` original para os 3 JSONs

### 🟢 BAIXO — cosmético / qualidade

- [ ] **Comparar e sincronizar** as 3 línguas (`pt_BR.json`, `es_AR.json`, `en_US.json`) — garantir paridade de chaves
- [ ] **Sidebar/Topbar** — revisar fidelidade ao original (estrutura, itens de menu, aria-labels)
- [ ] **Revisar dados dinâmicos** nos controllers (dashboard stats, history filters, stats KPIs) — garantir que todos os valores estão sendo passados e renderizados
- [ ] **Assets** — verificar se todos os CSS/JS referenciados existem em `public/` (ex: `assets/css/buttons-global.css`, `assets/css/index.css`)

---

## 4. Detalhamento view-por-view

Legenda: ✅ OK · ⚠️ Parcial · ❌ Quebrado · 🚫 Faltando

### ✅ [OK] Views já funcionais

| # | View Laravel | Original | Obs. |
|---|---|---|---|
| 1 | `pages/index.blade.php` | `public/index.php` | **Corrigida em `2d97cf8`** |
| 2 | `dashboard/index.blade.php` | `App/Views/dashboard.php` | Falta só CTA `dashboard.buy_more_cta` |
| 3 | `questionbank/index.blade.php` | `App/Views/bancoperguntas.php` | — |
| 4 | `history/index.blade.php` | `App/Views/simulados.php` | — |
| 5 | `stats/index.blade.php` | `App/Views/estatisticas.php` | — |
| 6 | `components/sidebar.blade.php` | `App/Views/includes/sidebar.php` | Faltam 4 chaves aria |
| 7 | `components/topbar.blade.php` | `App/Views/includes/app-private-toolbar.php` | — |

### ⚠️ [Parcial] Views que funcionam mas estão incompletas

#### `auth/login.blade.php`

**Problema:** Sidebar do login usa 4 chaves que não existem no JSON.

**Chaves inventadas:**
- `login.email_placeholder`
- `login.sidebar_heading`
- `login.sidebar_lead`
- `login.sidebar_social_proof`

**Ação:** Adicionar as chaves no JSON OU reescrever a view usando as chaves originais (se não houver equivalente, adicionar textos hard-coded no original em `lang/pt_BR.php`).

---

#### `profile/show.blade.php`

**Problema:** Chaves existem mas verificar completude vs `App/Views/perfil.php`.

**Ação:** Ler ambos e comparar campos (nome, e-mail, trocar senha, matérias vinculadas, stats, botão de logout).

---

### ❌ [Quebrado] Views com chaves de tradução erradas

#### `signup/select-materias.blade.php`

**Chaves inventadas:**
- `signup.materias.lead` (deveria ser 3 chaves separadas: `lead_before`, `lead_strong`, `lead_after`)
- `signup.materias.continue` (correto é `signup.btn.continue_plan`)

**Seções faltando vs original:**
- Bloco "O que você faz neste passo?" (`signup.materias.what_title`, `what_p`)
- Card "Como funciona?" com 3 passos (`signup.how1.title/p`, `signup.how2.title/p`, `signup.how3.title/p`)
- Dica (`signup.materias.tip`)
- Estado vazio (`signup.empty.title`, `empty.p`, `empty.btn`)
- Card de resumo com contagem dinâmica (JS)

**Severidade:** Crítico — é uma das primeiras telas do fluxo de cadastro.

---

#### `signup/select-plano.blade.php`

**Chaves inventadas:**
- `signup.back_plan` (correto é `signup.back_materias`)

**Seções faltando:** Resumo das matérias selecionadas, chips de matérias, features completas de cada plano (f1-f6 por plano).

**Severidade:** Crítico.

---

#### `checkout/show.blade.php`

**Problema sistêmico:** Usa namespace `checkout.*` inventado. Todas as chaves corretas estão em `signup.checkout.*`.

**Chaves inventadas (13):**
- `checkout.your_data`
- `checkout.label_email` → `signup.checkout.email`
- `checkout.label_name` → `signup.checkout.name`
- `checkout.label_country` → `signup.checkout.country`
- `checkout.label_postal` → `signup.checkout.postal`
- `checkout.placeholder_email` → (não existe no original; hint usa `signup.checkout.email_hint`)
- `checkout.placeholder_name`
- `checkout.placeholder_postal` → `signup.checkout.postal_ph`
- `checkout.summary_title` → `signup.checkout.summary_title`
- `checkout.secure_note` → `signup.checkout.secure`
- `checkout.pay_btn` → `signup.checkout.submit_mp`
- `checkout.heading`
- `checkout.subheading`

**Seções faltando:**
- Termos e condições (`signup.checkout.terms_before/link/after`)
- Info de redirecionamento ao MP (`signup.checkout.mp_info`)
- Nota de acesso pós-pagamento (`signup.checkout.access_note`)
- Nota "após pagamento receberá e-mail com credenciais" (`signup.checkout.after_pay_note`)

**Severidade:** Crítico.

---

#### `checkout/success.blade.php`

**Problema sistêmico:** Mesma coisa — namespace `checkout.*` inventado. Correto é `signup.payment.*`.

**Chaves inventadas (14):**
- `checkout.success_title` → `signup.page_title.payment_ok`
- `checkout.status_approved_title` → `signup.payment.confirmed_h1`
- `checkout.status_approved_text` → (usar `signup.payment.success_p`)
- `checkout.status_pending_title` → `signup.payment.received_h1`
- `checkout.status_pending_text` → `signup.payment.processing_p`
- `checkout.status_failed_title` → `signup.payment.error_h1`
- `checkout.status_failed_text` → `signup.payment.error_sub`
- `checkout.order_email` → `signup.payment.order_email`
- `checkout.order_id` — nova, precisa criar
- `checkout.next_steps_title` → `signup.payment.next_title`
- `checkout.next_step_1` → `signup.payment.next1`
- `checkout.next_step_2` → `signup.payment.next2`
- `checkout.next_step_3` → `signup.payment.next3`
- `checkout.go_login` → `signup.payment.btn_login`

**Severidade:** Crítico.

---

#### `simulation/show.blade.php`

**Chaves inventadas:**
- `quiz.correct`
- `quiz.incorrect`
- `quiz.finish`
- `quiz.prev`
- `quiz.next`

**Severidade:** Médio a crítico — afeta feedback do modo estudo e navegação do quiz.

---

#### `simulation/result.blade.php`

**Problema:** Namespace `result.*` inventado, nenhuma das chaves existe no JSON.

**Chaves inventadas (11):**
- `result.page_title`
- `result.approved`
- `result.failed`
- `result.correct`
- `result.details_title`
- `result.th_question`
- `result.th_your_answer`
- `result.th_correct`
- `result.th_status`
- `result.new_quiz`
- `result.back_dashboard`

**Ação:** Ou criar essas chaves nos 3 JSONs, ou portar da view original (`App/Views/resultado.php`) e usar chaves já existentes.

**Severidade:** Crítico.

---

### 🚫 [Faltando] Views que não foram migradas

| # | View original | Função |
|---|---|---|
| 1 | `App/Views/comprar-materias.php` | Usuário logado escolhe matérias extras |
| 2 | `App/Views/comprar-plano.php` | Escolhe plano para essas matérias extras (se primeira compra) |
| 3 | `App/Views/checkout-addon.php` | Checkout MercadoPago para o addon |

---

## 5. Apêndice — Chaves de tradução faltando nos JSONs

### 5.1. `addon.*` (40+ chaves) — CRÍTICO

Não existem no `lang/pt_BR.json` / `es_AR.json` / `en_US.json`. Existem no `lang/pt_BR.php` original (ver `lang/pt_BR.php` linhas 21–39).

Principais:
- `addon.page_title_materias`
- `addon.page_title_plano`
- `addon.page_title_checkout`
- `addon.intro`
- `addon.empty`
- `addon.empty_hint`
- `addon.select_min`
- `addon.continue_plano`
- `addon.continue_checkout`
- `addon.plano_intro`
- `addon.back_materias`
- `addon.summary`
- `addon.checkout_intro`
- `addon.email_note`
- `addon.checkout_back`
- `addon.login_required`
- `addon.payment.next_title`
- `addon.payment.next_p`
- `addon.payment.btn_panel`

### 5.2. `sidebar.*` extras (4-5 chaves)

- `sidebar.theme_group_aria`
- `sidebar.theme_light_aria`
- `sidebar.theme_dark_aria`
- `sidebar.theme_switch_to_dark`
- `sidebar.theme_switch_to_light`

### 5.3. `nav.*` extras (2 chaves)

- `nav.buy_subjects`
- `nav.buy_subjects_short`

### 5.4. `dashboard.*` extras

- `dashboard.buy_more_cta`

### 5.5. `quiz.*` extras (5 chaves)

- `quiz.correct`
- `quiz.incorrect`
- `quiz.finish`
- `quiz.prev`
- `quiz.next`

### 5.6. `result.*` (11 chaves) — se optar por criar namespace novo

Ver lista completa na seção 4, subview `simulation/result.blade.php`.

### 5.7. Chaves de `signup.checkout.*` existentes no JSON mas não usadas

- `signup.checkout.country_ph`
- `signup.checkout.postal_location_prefix`
- `signup.checkout.postal_ok`
- `signup.checkout.mp_settlement_ars`

### 5.8. Fonte da verdade

O arquivo `C:\xampp\htdocs\banco_choices\lang\pt_BR.php` tem **400 chaves** completas. O `lang/pt_BR.json` do Laravel tem **~175**. Diferença: 225+ chaves a portar.

---

## 6. Outros itens técnicos não relacionados a views

### 6.1. Funções helper faltando em `config/signup.php`

O Laravel tem apenas um array simples; falta a lógica do original (`config/signup_flow.php`):

- `signup_flow_plans_display(): array` — formata planos para a view (preço, duração, features)
- `signup_flow_addon_helpers()` — resolve plano de addon

### 6.2. Método faltando no model `User`

```php
public function buscarUltimoPlanoIdParaUsuarioId(int $id): ?string
```
JOIN entre `pedidos` e `pedidos_itens` para achar o plano mais recente comprado pelo usuário.

### 6.3. Script CLI não migrado

- `scripts/criar-usuario-teste.php` → virar artisan command `php artisan user:create-test`
- `scripts/criar-usuario-teste-sem-materia.php` → idem
- `scripts/build-checkout-postal-js.php` → avaliar se ainda é necessário

### 6.4. Verificar integridade de assets em `public/`

O original referencia:
- `public/assets/css/buttons-global.css`
- `public/assets/css/index.css`
- `public/assets/css/public-language-selector.css`
- `public/assets/img/logo-bd-transparente.png`
- etc.

Alguns podem estar em `public/assets/` e outros em `public/img/` no Laravel — garantir consistência nos `asset()` das views.

---

## 7. Status / Progresso

| Data | Commit | O que foi feito |
|---|---|---|
| 2026-04-15 | `0b584a9` | Commit inicial do repositório |
| 2026-04-15 | `2d97cf8` | Reescrita de `pages/index.blade.php` (landing page) fielmente ao original |

---

## 8. Como usar este documento

1. Pegue um item da seção 🔴 CRÍTICO de cada vez
2. Implemente + teste (usar `php artisan serve` + navegador)
3. Commite com mensagem descrevendo a correção
4. Marque o item como `[x]` neste doc e avance para o próximo
5. Quando todos os críticos acabarem, siga para 🟠 MÉDIO e 🟢 BAIXO

**Dica:** use o projeto original (`C:\xampp\htdocs\banco_choices`) como fonte da verdade para HTML, CSS e lógica. O JSON de tradução `lang/pt_BR.php` do original tem todos os textos certos.
