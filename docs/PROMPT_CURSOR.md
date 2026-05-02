# Prompt para o Cursor — Banco de Choices (Laravel 12)

## Contexto do projeto

Você vai trabalhar no repositório **`banco_choices_laravel`**. Antes de começar, leia o arquivo `PROJETO.md` da raiz para entender a stack e convenções existentes. Pontos críticos do projeto que **não pode quebrar**:

- Laravel 12 + PHP 8.2, Blade + Bootstrap 5, SQLite em dev / MySQL em prod
- Campo de senha do `User` se chama **`senha`** (não `password`), com `getAuthPassword()` no model
- Em `pedidos`, o campo `stripe_payment_id` é nome legado e guarda referência do **Mercado Pago**
- Webhook `/webhook-mercadopago` é exceção de CSRF e tem idempotência via `mp_payment_processed`
- i18n em 3 locales: `es_AR` (padrão), `pt_BR`, `en_US` — toda string visível precisa de chave JSON em `lang/`
- Cor primária da marca: `#6a0392`; tema claro/escuro com `data-theme="dark"`
- Layouts: `layouts/public` (público) e `layouts/app` (autenticado)
- SQL deve ser compatível com **SQLite e MySQL** (sem features exclusivas)

Trabalhe em **migrations novas** (nunca edite as antigas), siga os padrões de controllers/views já existentes, e adicione traduções nos 3 locales.

---

## Objetivo geral

Implementar 4 mudanças grandes:

1. **Reestruturação hierárquica das matérias** por faculdade
2. **Filtros de parcial e tema** nas questões de cada matéria
3. **Sistema de cupom de indicação** (referral) com crédito pra quem indica e desconto pra quem usa
4. **Modo demo gratuito** (5 questões por matéria sem login) com paywall

Cada feature está detalhada abaixo. **Implemente na ordem** — feature 1 é base pras outras.

---

## Feature 1 — Reestruturação hierárquica das matérias

### Modelo lógico

```
Faculdade (ex: UBA, La Plata, Barcelo, CBC)
  └── Agrupamento (ex: "Ciclo Biomédico", "Ciclo Clínico", "1º Año", "2º Año")
        └── Matéria (ex: Histologia, Microbiologia)
              └── Cátedra [OPCIONAL] (ex: "Cátedra I", "Cátedra II")
                    └── Questões
```

**Regra crítica:** matérias podem **ter cátedras ou não**. Quando não têm, as questões ficam direto na matéria. Quando têm, as questões são vinculadas à cátedra. Não force cátedra obrigatória.

### Schema (novas migrations)

Crie em `database/migrations/`:

- **`faculdades`**: `id`, `nome`, `slug` (único), `ordem` (int, pra ordenar na UI), `ativo` (bool), timestamps
- **`agrupamentos`**: `id`, `faculdade_id` (FK), `nome` (ex: "Ciclo Biomédico"), `slug`, `ordem`, `tipo` (enum string: `ciclo` | `ano` | `outro`), timestamps
- **`catedras`**: `id`, `materia_id` (FK), `nome` (ex: "Cátedra I"), `slug`, `ordem`, timestamps

E **altere** a tabela `materias` (em migration nova `alter_materias_add_hierarchy`):
- adicionar `agrupamento_id` (FK nullable inicialmente, pra permitir backfill)
- adicionar `ordem` (int)
- manter colunas existentes intactas

### Models e relações

- `Faculdade` hasMany `Agrupamento`
- `Agrupamento` belongsTo `Faculdade`, hasMany `Materia`
- `Materia` belongsTo `Agrupamento`, hasMany `Catedra`
- `Catedra` belongsTo `Materia`
- A pivot `usuarios_materias` continua igual (acesso é por matéria) — **não mexa nela** nessa feature

### Seeder de catálogo

Crie `database/seeders/CatalogoSeeder.php` que popula a estrutura abaixo. Use slugs em kebab-case. Marque as matérias com cátedras conforme indicado:

**UBA**
- *Ciclo Biomédico* (`tipo: ciclo`)
  1. Histologia
  2. Embriologia
  3. Biología Molecular y Genética
  4. Fisiología y Biofísica
  5. Bioquímica
  6. Inmunología Humana → cátedras: `Cátedra I`, `Cátedra II`
  7. Microbiología y Parasitología → cátedras: `Cátedra I`, `Cátedra II`
- *Ciclo Clínico* (`tipo: ciclo`)
  1. Patología
  2. Farmacología I
  3. Farmacología II
  4. Medicina I

**La Plata**
- *1º Año* (`tipo: ano`) — deixar matérias placeholder vazio (`// TODO: completar`)

**Barceló**
- *1º Año* (`tipo: ano`) — placeholder
- *2º Año* (`tipo: ano`) — placeholder

**CBC**
- *1º Año* (`tipo: ano`) — placeholder
- *2º Año* (`tipo: ano`) — placeholder

Registre o seeder em `DatabaseSeeder` mas **idempotente** (use `firstOrCreate` por slug, nunca duplique).

### Migração de dados (não destrutiva)

Crie um comando artisan `php artisan banco:migrate-materias` (em `app/Console/Commands/`) que:
1. Cria uma faculdade `UBA` se não existir
2. Cria um agrupamento `Ciclo Biomédico` se não existir
3. Atribui todas as `materias` antigas que ainda têm `agrupamento_id` nulo a esse agrupamento (fallback seguro)
4. Loga quantas matérias foram migradas

Isso garante que dados antigos não quebrem em produção.

### UI — Banco de questões

Atualize a tela `/bancoperguntas` (`questionbank/index.blade.php`) e o `QuestionBankController`:

- Antes da escolha de matéria, agora tem **dois selects em cascata**: **Faculdade** → **Agrupamento** → **Matéria**
- Se a matéria selecionada **tem cátedras**, mostrar select extra **Cátedra** (obrigatório)
- Carregue agrupamentos/matérias/cátedras via endpoint AJAX (`GET /api/catalogo/agrupamentos?faculdade_id=`, `GET /api/catalogo/materias?agrupamento_id=`, `GET /api/catalogo/catedras?materia_id=`) — protegidos por `auth` middleware. Use rotas em `routes/web.php` ou `routes/api.php` conforme padrão do projeto.
- Mantenha lógica de modo (estudo/exame) e quantidade de questões existente
- O usuário só vê faculdades/agrupamentos/matérias que **possui acesso** (já validado pela pivot `usuarios_materias`). Filtre os selects pra só listar opções com matéria comprada — não mostre opções vazias.

### UI — Fluxo de signup (passo 1)

Atualize `/selecionar-materias` (`signup/select-materias.blade.php`) pra usar a mesma navegação Faculdade → Agrupamento → Matéria → Cátedra. O usuário pode adicionar matérias de **qualquer faculdade** ao carrinho. Mantenha o resto do fluxo (passo 2 plano, checkout) inalterado.

### UI — Addon (`/comprar-materias`)

Mesma reestruturação do signup: hierarquia visual de Faculdade → Agrupamento → Matéria → Cátedra, mostrando só o que ainda **não** foi comprado.

---

## Feature 2 — Filtros de parcial e tema nas questões

### Schema

- Adicione na tabela de questões existente (descobrir nome — provavelmente `questoes` ou similar) duas colunas via migration `alter_questoes_add_parcial_tema`:
  - `parcial` (string nullable, valores típicos: `1` | `2` | `3` | `final` — string pra flexibilidade)
  - `tema` (string nullable, ex: "Sistema cardiovascular")
- Crie índice composto `(materia_id, parcial)` e `(materia_id, tema)` pra performance dos filtros

### Lógica de filtro

No `QuestionBankController` ao montar o simulado:
- Aceitar parâmetros opcionais `parcial[]` (multi-select: `1`, `2`, `3`, `final`) e `tema[]` (multi-select)
- Se `parcial` inclui `final`, retornar questões de **todos os parciais** dessa matéria (final cobra tudo)
- Se nenhum filtro, comportamento atual (todas as questões da matéria)
- Validar que filtros aplicados existem pra essa matéria (evita query vazia silenciosa)

### UI

Na tela `/bancoperguntas`, depois de selecionar matéria/cátedra, mostrar:

- Grupo de checkboxes **"Parciales"**: `1º Parcial`, `2º Parcial`, `3º Parcial` (mostrar só os que existem pra matéria), `Final` (sempre mostrar se houver questões)
- Multi-select **"Temas"** (carregado via AJAX `GET /api/catalogo/temas?materia_id=&catedra_id=`) — campo searchable
- Texto de ajuda: "El examen final cubre temas de todos los parciales" (com chave i18n)

### Stats

Em `/estatisticas`, adicione gráfico de **desempenho por parcial** dentro de cada matéria (% acerto agrupado por parcial). Mantenha o resto da tela.

---

## Feature 3 — Sistema de cupom de indicação (referral)

### Modelo de recompensa (regra de negócio)

- **Quem indica (referrer):** ganha **crédito em R$/AR$** equivalente a uma % do valor pago pelo indicado (defina em `config/referral.php` — sugestão: 10%). Crédito acumula em `users.saldo_credito`. Pode ser usado como abatimento em compras futuras OU sacado a partir de mínimo configurável (sugestão: $10.000 ARS, configurável em `config/referral.php` como `minimo_saque`).
- **Quem usa (referido):** ganha **% de desconto** na compra (sugestão: 10%, configurável em `config/referral.php` como `desconto_referido_percent`).
- **Antifraude obrigatório:**
  - Usuário não pode usar o **próprio** cupom
  - Mesmo IP/email não pode ser referido mais de 1x (constraint `referrals.referido_email` único)
  - Crédito só é creditado quando o pedido entra em `completed` (no fulfillment), não em `awaiting_payment`

### Schema

Migrations novas:

- **`alter_users_add_referral`**: adicionar em `users`:
  - `codigo_cupom` (string única, nullable — gerado on-demand quando o usuário acessa a tela de referidos pela 1ª vez)
  - `saldo_credito` (decimal 10,2, default 0)
  - `referido_por_codigo` (string nullable — guarda o código que ele usou ao se cadastrar, pra rastreabilidade)

- **`referrals`** (registro de cada indicação que se concretizou):
  - `id`
  - `referrer_user_id` (FK users)
  - `referido_user_id` (FK users, nullable até pagamento confirmar)
  - `referido_email` (string, índice — pra detectar duplicatas antes do user existir)
  - `codigo_usado` (string)
  - `pedido_id` (FK pedidos, nullable)
  - `valor_credito_gerado` (decimal 10,2)
  - `status` enum: `pending` | `credited` | `paid_out` | `cancelled`
  - timestamps

- **`credito_movimentos`** (extrato pra UI e auditoria):
  - `id`, `user_id`, `tipo` (enum: `referral_credit` | `purchase_use` | `withdrawal` | `adjustment`), `valor` (decimal — positivo entrada, negativo saída), `referencia_tipo` + `referencia_id` (morphs opcionais), `descricao`, timestamps

### Geração de código

- Formato: `BC-XXXXXX` (BC = Banco Choices, 6 chars alfanuméricos uppercase)
- Função `User::gerarCupomUnico()` em service `app/Services/Referral/CodigoService.php` — tenta até 5x com retry em colisão
- Código é gerado **lazy** (na primeira vez que o user abre a tela de referidos), não no signup, pra não poluir o BD com códigos nunca usados

### Fluxos a implementar

#### a) Tela `/referidos` (autenticada)

Crie controller `ReferralController@show` e view `referral/show.blade.php`. Inspirada na imagem 3 que o usuário enviou (referência visual: card central com código + botão copiar, ícones de redes sociais Instagram/WhatsApp; cards laterais "Cómo funciona" + "Total/Saldo" com gift icon; seção "Condiciones" abaixo). Adapte ao tema/cores do Banco de Choices (`#6a0392`), **não copie estilo do El Preguntero**.

Conteúdo:
- **Card hero:** código do usuário + botão copiar + botões de share (WhatsApp via `https://wa.me/?text=...`, Instagram com instrução "copiar e colar no story")
- **Card "Cómo funciona":** 3 passos (Compartir → Descuento → Ganás)
- **Card "Total":** saldo atual + CTA "Histórico" que abre modal/página com `credito_movimentos`
- **Seção "Condiciones":** termos (criar 1 chave i18n grande com o texto completo, baseado no rascunho do usuário — gerar lista de regras: 1 cupom por compra, não acumulável com outros descontos, não pode autoreferir, mínimo X pra saque, etc.)

#### b) Aplicação do cupom no checkout

No `CheckoutController` (signup) e `AddonController`:
- Adicionar campo opcional **"Código de cupón"** no form de checkout
- Validar via `ReferralService::validarCodigo($codigo, $emailUsuario)`:
  - código existe?
  - não é o do próprio usuário (compara por user_id se autenticado, ou por email se signup)
  - email ainda não foi referido antes (`referrals.referido_email`)
- Se válido: aplicar `desconto_referido_percent` no total **antes** de criar a preferência Mercado Pago
- Salvar `codigo_usado` no `pedido` (adicionar coluna `codigo_cupom_usado` em `pedidos` via migration)
- **Não credite o referrer ainda** — só quando o pedido virar `completed`

#### c) Crédito do referrer no fulfillment

Em `app/Services/MercadoPago/Fulfillment.php` (ou onde quer que o pedido seja marcado `completed`):
- Quando pedido vira `completed` E tem `codigo_cupom_usado`:
  - Buscar o `User` dono do código
  - Calcular crédito: `valor_pago * referrer_credit_percent`
  - Criar registro em `referrals` com status `credited`
  - Incrementar `users.saldo_credito` do referrer (use transação DB)
  - Inserir `credito_movimentos` tipo `referral_credit`
- Tudo dentro de `DB::transaction()` pra garantir consistência
- Idempotente: se já existe registro `referrals` pra esse `pedido_id`, pula (evita duplo crédito em retry de webhook)

#### d) Uso de crédito em compra futura

No checkout (signup e addon), adicionar checkbox **"Usar mi saldo de R$X,XX"** (só aparece se `saldo_credito > 0`):
- Se marcado, abater até o total da compra do `saldo_credito`
- Se cobre 100%, pular Mercado Pago (criar pedido `completed` direto, gerar fulfillment manualmente)
- Se cobre parcial, mandar pro MP só o que faltou
- Registrar `credito_movimentos` tipo `purchase_use` com valor negativo

### UI — copy

Use as 3 línguas. Termos sugeridos:
- pt_BR: "Indique e ganhe", "Seu cupom", "Saldo", "Histórico de bonificações"
- es_AR: "Referí y ganá", "Tu cupón", "Saldo", "Historial de bonificaciones"
- en_US: "Refer and earn", "Your coupon", "Balance", "Bonus history"

---

## Feature 4 — Modo demo gratuito (5 questões por matéria, sem login)

### Comportamento

- Rota pública nova: `GET /probar-gratis` (nome: `demo.show`) — acessível **sem login**
- Fluxo no usuário:
  1. Escolhe **Faculdade** (cards visuais com cores diferentes por faculdade — UBA azul, La Plata verde, Barceló laranja, CBC roxo, por exemplo)
  2. Escolhe **Agrupamento**
  3. Escolhe **Matéria** (e cátedra se aplicável)
  4. Vê tela de questionário com **até 5 questões aleatórias** dessa matéria
  5. Após responder cada questão, mostra **gabarito + comentário** imediatamente (modo estudo forçado)
  6. Ao terminar as 5 (ou se tentar acessar uma 6ª), aparece **paywall modal**:
     - Título: "Te gustó? Desbloqueá todas las preguntas"
     - CTA principal: "Ver planes" → leva pra `/selecionar-materias` com matéria pré-selecionada (passar via query string `?materia_id=X` que o passo 1 lê)
     - CTA secundário: "Iniciar sesión" → `/login`

### Schema

- Adicione em `questoes` coluna `is_demo` (boolean, default false, indexada). Marca quais questões podem aparecer no demo.
- Migration `alter_questoes_add_is_demo`
- Comando artisan `php artisan banco:marcar-demo --por-materia=5` que pra cada matéria marca 5 questões aleatórias como `is_demo = true` (idempotente — se já tem 5 marcadas, pula)

### Anti-abuso

- Limite por **sessão + cookie + IP** combinados:
  - Cookie `bc_demo_session` (UUID gerado na 1ª visita, 30 dias)
  - Tabela `demo_attempts`: `id`, `session_uuid`, `ip`, `materia_id`, `questao_id`, `acertou` (bool), `created_at`
  - Antes de servir cada questão, contar `demo_attempts` da combinação (`session_uuid` OR `ip`) na mesma matéria nas últimas 24h. Se ≥ 5, bloqueia e mostra paywall.
- Rate limit: máximo 3 sessões demo por IP por dia (mesmo limpando cookie)
- Sem persistir estatísticas — demo é **stateless do ponto de vista do usuário** (não cria conta, não conta no histórico)

### Controller e rotas

`DemoController` com:
- `show()` → tela de seleção (Faculdade → Agrupamento → Matéria → Cátedra)
- `iniciar(Request)` → valida seleção, cria/recupera `bc_demo_session`, busca 5 questões `is_demo=true`, redireciona pra `demo.questao`
- `questao($index)` → renderiza questão N (1..5)
- `responder(Request)` → grava `demo_attempts`, retorna gabarito+comentário (JSON pra resposta inline)
- `paywall()` → tela final com CTAs

Rotas em `routes/web.php` **fora** do middleware `auth`. Adicionar throttle: `throttle:60,1` no grupo.

### UI

- View `demo/show.blade.php` — usar `layouts/public`
- Visual de cards de faculdade com gradientes coerentes com `#6a0392` mas variando hue por faculdade (não cores aleatórias estridentes)
- Tela de questão idêntica em estrutura à do simulação real, mas com badge fixa "DEMO — Pregunta X de 5"
- Paywall modal Bootstrap 5

### Landing

Adicionar CTA **"Probá gratis"** na home (`pages/index.blade.php`) acima do "Cadastre-se", linkando pra `/probar-gratis`. Não substituir o cadastro existente.

---

## Checklist final (entregáveis)

- [ ] Migrations novas (não editar antigas)
- [ ] Models com relações Eloquent corretas + `$fillable` definidos
- [ ] `CatalogoSeeder` idempotente populando estrutura UBA completa + placeholders das outras 3 faculdades
- [ ] Comando `banco:migrate-materias` para backfill seguro
- [ ] Comando `banco:marcar-demo --por-materia=5`
- [ ] Endpoints AJAX pra cascata Faculdade → Agrupamento → Matéria → Cátedra → Temas
- [ ] Filtros de parcial (multi) + tema (multi) no QuestionBank com regra "final cobra tudo"
- [ ] Tela `/referidos` com cupom + saldo + histórico + condições
- [ ] Aplicação de cupom no checkout (signup + addon) com validação antifraude
- [ ] Crédito do referrer no fulfillment, dentro de transação, idempotente
- [ ] Uso de saldo de crédito em compra (com handling de cobertura 100% ou parcial)
- [ ] Tela `/probar-gratis` com fluxo completo de 5 questões + paywall
- [ ] Anti-abuso do demo (cookie + IP + rate limit)
- [ ] Traduções em `lang/es_AR.json`, `lang/pt_BR.json`, `lang/en_US.json` pra TODA string nova
- [ ] `config/referral.php` com `referrer_credit_percent`, `desconto_referido_percent`, `minimo_saque`
- [ ] Testes Feature mínimos: cupom inválido, autoreferral bloqueado, demo limit, fulfillment com cupom

## Restrições / não fazer

- **Não** quebrar o fluxo atual de signup/checkout/addon — mudanças devem ser aditivas
- **Não** renomear `senha` ou `stripe_payment_id` (campos legados intencionais)
- **Não** usar features SQL exclusivas de MySQL (testar SQL em SQLite)
- **Não** colocar credenciais MP no código — sempre `.env`
- **Não** remover middleware CSRF do webhook (já é exceção isolada)
- **Não** apagar matérias antigas — só migrar pra hierarquia nova
- **Não** quebrar i18n — string sem chave JSON é bug
- Manter cor primária `#6a0392` e respeitar tokens `--app-*` / `data-theme="dark"`

## Ordem de execução sugerida

1. Migrations + Models + Seeder (Feature 1) → testar com `php artisan migrate:fresh --seed`
2. Endpoints AJAX + UI cascata (Feature 1)
3. Migrations questões (parcial, tema, is_demo) (Features 2 e 4)
4. Filtros parcial/tema no QuestionBank (Feature 2)
5. Tabelas referral + ReferralController + tela `/referidos` (Feature 3)
6. Aplicação de cupom no checkout + fulfillment com crédito (Feature 3)
7. DemoController + telas + anti-abuso (Feature 4)
8. CTA na landing + traduções finais
9. Testes Feature

Pergunte se algo do escopo estiver ambíguo antes de chutar uma decisão de arquitetura.
