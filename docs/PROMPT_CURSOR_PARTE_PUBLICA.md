# Prompt para o Cursor (Parte 2) — UI pública inspirada no El Preguntero

## Sobre este prompt

Este prompt **complementa e substitui** a Feature 4 (modo demo) do prompt anterior `PROMPT_CURSOR.md`. As Features 1, 2 e 3 daquele prompt continuam válidas e **devem ter sido executadas antes** deste — este aqui depende da hierarquia Faculdade → Agrupamento → Matéria → Cátedra já existir no banco.

**Objetivo:** redesenhar **só a parte pública** do site (landing + demo gratuito) inspirando-se nas seções do site El Preguntero (referência visual nos prints anexos), mas **mantendo 100% a identidade do Banco de Choices** — cor primária `#6a0392` (roxo), tipografia Inter/Poppins, tokens `--app-*` já existentes. **Não copiar paleta azul/cinza do El Preguntero.**

A parte autenticada (após login) **não muda nada** neste prompt — continua exatamente como está hoje. Mexe-se só em `layouts/public` e nas views públicas.

## Princípios visuais

1. **Modo escuro como padrão** na landing pública (background `#0b0b0f` / `#14141a`, igual ao tema dark já existente em `theme-tokens.css`)
2. **Acentos roxos `#6a0392`** em CTAs, badges, números de destaque, ícones — substituindo o azul do El Preguntero em todos os pontos onde ele aparece
3. **CTA principal laranja** (cor `#e87722` ou similar, como nos prints) — é a única cor "quente" da página, usada só no botão "Practicar ahora" / "Probá gratis" pra dar destaque máximo
4. **Tipografia:** Inter pra corpo, Poppins pra títulos grandes (já configurado)
5. **Espaçamento generoso** seguindo o padrão dos prints — seções com 80-120px de padding vertical
6. **Inspiração em layout, não em cores**: usar a estrutura de seções do El Preguntero, mas com a paleta do Banco de Choices

## Stack e convenções (revisar antes de começar)

- Laravel 12, Blade, Bootstrap 5, Vite
- Layout público: `resources/views/layouts/public.blade.php`
- CSS por área em `public/assets/css/`
- i18n: `lang/es_AR.json` (padrão), `lang/pt_BR.json`, `lang/en_US.json` — toda string nova precisa de chave nos 3
- Trabalhar com hierarquia já criada nas Features 1-3: `faculdades`, `agrupamentos`, `materias`, `catedras`, `questoes` com `parcial`, `tema`, `is_demo`

---

## 1. Landing nova — `pages/index.blade.php`

Reestruturar a home (`/`) em seções verticais, na ordem abaixo. **Substituir** o conteúdo atual da landing.

### 1.1 Topbar pública

- Logo Banco de Choices à esquerda
- Centro: links âncora (`#modalidades`, `#como-funciona`, `#planes`, `#faq`)
- Direita: botão **"Iniciar sesión"** (link `/login`, estilo outline roxo) + botão **"Obtener Premium"** ou **"Practicar ahora"** (sólido, gradiente roxo)
- Sticky no topo, com `backdrop-filter: blur(12px)` e fundo semi-transparente `rgba(11, 11, 15, 0.85)` quando scrolla
- Mobile: hambúrguer com offcanvas (Bootstrap 5)

### 1.2 Hero

Estrutura idêntica ao print 3, **adaptada à marca**:

- **Lado esquerdo:**
  - Badge pequeno "BANCO DE PREGUNTAS" (fundo escuro, borda sutil, texto roxo claro `#a855f7` ou similar — **não azul**)
  - Título grande: **"Preparate en menos tiempo"** — palavra "Preparate" em itálico/destaque com gradiente roxo (`linear-gradient(135deg, #a855f7, #6a0392)`)
  - Subtítulo: "Cambiamos tu manera de preparar exámenes. Disfrutá de **47.000+ preguntas** explicadas, seguí tu progreso paso a paso y controlá cómo venís de forma inteligente." — número e palavras-chave em **bold** com cor roxa
  - **CTAs duplos:**
    - **"Practicar ahora"** (botão laranja sólido — leva pra `/probar-gratis`, novo)
    - **"Ver planes"** (botão outline roxo — leva pra `#planes`)
- **Lado direito:** placeholder pra mockup. Por enquanto criar SVG/HTML decorativo de "tablet com app" usando CSS puro (gradientes + bordas arredondadas). Documentar no código como **"// TODO: substituir por screenshot real do app quando disponível"**.

Toda copy em **es_AR como padrão**, com chaves i18n para pt_BR e en_US.

### 1.3 Banner de stats

Faixa horizontal logo abaixo do hero, com 4 colunas separadas por borda fina vertical (igual ao print 3):

| Stat | Label |
|------|-------|
| **47.000+** | Preguntas justificadas |
| **Excelencia** | Fuentes oficiales |
| **Seguimiento** | Detallado e inteligente |
| **2.000+ alumnos** | Comunidad activa |

Números em roxo gradiente, label em texto muted (`#94a3b8`). Fundo levemente diferente da seção (ex: `#0e0e14`) pra criar separação visual.

**Observação:** os números devem ser configuráveis em `config/landing.php` (criar arquivo novo). Default: os 4 valores acima.

### 1.4 Seção "Modalidades" — cards de faculdades

Título: **"Banco de preguntas: del CBC al examen de residencia"** (igual ao print 3, adaptar)

Subtítulo pequeno: badge "MODALIDADES" acima do título, em roxo claro.

**Grid de cards** (2 colunas em desktop, 1 em mobile) — **um card por faculdade ativa no banco**, gerado dinamicamente do model `Faculdade::where('ativo', true)->orderBy('ordem')->get()`. Cada card:

- Fundo `rgba(255, 255, 255, 0.02)` com borda sutil `rgba(255, 255, 255, 0.06)`
- Hover: borda fica roxa, leve elevação (`transform: translateY(-2px)`)
- Padding generoso (32px)
- **Conteúdo:**
  - Nome da faculdade em destaque (ex: "Medicina UBA")
  - Texto pequeno descritivo (configurar em coluna nova `faculdades.descricao_curta` — migration nova `alter_faculdades_add_descricao`)
  - Lista compacta de agrupamentos (ex: "Ciclo Biomédico • Ciclo Clínico")
  - Seta `→` no canto superior direito
- Card inteiro clicável → leva pra `/probar-gratis?faculdade={slug}` (já com a faculdade pré-selecionada no demo)

**Cards a renderizar dinamicamente** (já existentes do seeder da Feature 1):
- UBA → "Medicina UBA"
- La Plata → "Medicina UNLP"
- Barceló → "Medicina Barceló"
- CBC → "Ciclo Básico Común / UBA XXI"

Adicione um 5º card estático "Residencias médicas" com badge **"Próximamente"** (cinza, sem clique) — pra deixar pronto pra expansão futura.

### 1.5 Seção "Cómo funciona"

Título: **"Cómo funciona"** com badge "PASO A PASO".

3 colunas, cada uma com:
- Ícone roxo grande (use Bootstrap Icons já disponível — sugestões: `bi-mortarboard`, `bi-list-check`, `bi-graph-up-arrow`)
- Número grande de passo (1, 2, 3) em roxo gradiente, opacidade 0.4, posicionado atrás do ícone
- Título curto
- Descrição 2 linhas

**Conteúdo:**

1. **Elegí tu carrera** — "Seleccioná tu facultad y las materias que estás cursando."
2. **Practicá sin límites** — "Resolvé preguntas justificadas con citas a fuentes oficiales."
3. **Seguí tu progreso** — "Estadísticas detalladas por materia, parcial y tema."

### 1.6 Seção "Planes"

Título: **"Elegí tu plan"** com badge "PRECIOS".

3 cards de plano, lendo dinamicamente de `config/signup.php` (já existe):
- **1 mes** — preço mensal
- **6 meses** — preço com `/mes` calculado, badge sutil "Ahorrás X%"
- **12 meses** — preço com `/mes` calculado, badge destaque **"Recomendado"** (fundo roxo gradiente)

Layout inspirado no print 2 (modal de planos do El Preguntero), mas em grid horizontal 3 colunas e com paleta roxa do Banco de Choices.

Cada card mostra:
- Tempo (ex: "1 mes")
- Preço cortado original (em cinza com `text-decoration: line-through`) — campo opcional, configurável em `config/signup.php` como `precio_original` por plano
- Preço atual em destaque grande
- Lista de benefícios com `✓` roxo:
  - "Acceso a todas las materias compradas"
  - "Análisis detallado del rendimiento"
  - "Simulador de exámenes"
  - "Filtros por parcial y tema"
  - "Estadísticas semanales"
- CTA "Suscribirme" → leva pra `/selecionar-materias` com plano pré-selecionado via `?plano={monthly|semester|annual}`

Plano "12 meses" tem destaque visual: borda roxa mais grossa, leve glow `box-shadow: 0 0 40px rgba(106, 3, 146, 0.3)`.

### 1.7 Seção FAQ

Título: **"Preguntas frecuentes"** com badge **"?"** redondo roxo (igual ao print 1/2 mas em roxo).

Layout em 2 colunas (desktop):
- **Esquerda:** ícone grande `?` em círculo roxo + título "Preguntas frecuentes" + descrição "Todo lo que necesitás saber sobre la plataforma, el contenido y los planes disponibles."
- **Direita:** accordion Bootstrap 5 com perguntas (mínimo 6):

Perguntas e respostas (criar como chaves i18n, **não hardcoded em Blade**):
1. **¿Qué materias están incluidas?** — "Cubrimos las principales materias de UBA, UNLP, Barceló y CBC. Podés ver el detalle eligiendo tu facultad arriba."
2. **¿Las preguntas están actualizadas y justificadas?** — "Sí, todas las preguntas tienen justificación con cita a fuente oficial y se actualizan periódicamente."
3. **¿Cómo funciona el seguimiento del rendimiento?** — "Tenemos estadísticas por materia, parcial, tema y evolución temporal en tu panel."
4. **¿Cómo se accede a la plataforma?** — "Comprás un plan, te creás cuenta y accedés inmediatamente a las materias contratadas."
5. **¿Puedo probar antes de pagar?** — "Sí, ofrecemos 5 preguntas gratis por materia para que puedas conocer el sistema."
6. **¿Puedo cancelar mi suscripción?** — "Sí, podés gestionar tu suscripción desde tu perfil en cualquier momento."

Cada item do accordion tem `+` que rotaciona pra `×` ao abrir, fundo escuro, borda sutil roxa quando aberto.

### 1.8 CTA final "Empezá hoy"

Faixa horizontal centrada:
- Badge **"EMPEZÁ HOY"** roxo claro, texto small uppercase
- Título: **"Descubrí todo lo que Banco de Choices puede hacer por *vos*."** (palavra "vos" em itálico com cor roxa gradiente)
- Subtítulo: "Ganá tiempo practicando de la forma correcta."
- Botão laranja grande: **"Practicar ahora"** → `/probar-gratis`
- Decoração de fundo: SVGs sutis de "círculos concêntricos" ou linhas topográficas em opacidade baixa (`opacity: 0.04`), igual ao print 1/2

### 1.9 Footer

Inspirado no print 2:

- **Coluna 1:** Logo + ícones sociais (WhatsApp, Instagram) — links configuráveis em `config/branding.php`
- **Coluna 2 — "NAVEGACIÓN":** Inicio, Planes, Practicar, Simulacros, Referí y ganá, Ayuda
- **Coluna 3 — "NUESTRO OBJETIVO":** texto descritivo (configurável em `config/landing.php` como `footer.objetivo`):
  > "Poner a disposición la mayor cantidad de choices para que puedas aprobar con la nota más alta y con tranquilidad. Siempre cuidando cada choice para que sea 100% fiel a las bibliografías recomendadas."
- **Linha inferior:** © 2026 Banco de Choices · Todos los derechos reservados (ano dinâmico via `{{ date('Y') }}`)

Fundo `#0a0a0d`, borda superior fina roxa com opacidade 0.1.

---

## 2. Demo gratuito — substitui Feature 4 do prompt anterior

A Feature 4 original previa demo simples. Agora vamos fazer **mais robusta**, replicando o gerador de simulacros do print 4.

### 2.1 Schema (revisão)

Já criado na Feature 4 anterior (mantém):
- `questoes.is_demo` (bool indexada)
- `questoes.parcial` (string nullable)
- `questoes.tema` (string nullable)
- Tabela `demo_attempts` (anti-abuso)

**Adicione** (novo nesta versão):
- `questoes.plano` (string nullable, ex: "Plan nuevo", "Plan viejo") — pra UBA que tem planos diferentes (visto no print 4: "Bioquímica · Cátedra II · Plan nuevo · Temas del 2do parcial")
- Migration `alter_questoes_add_plano`

### 2.2 Comando de marcação

Atualize o comando da Feature 4:

```bash
php artisan banco:marcar-demo --por-materia=5
```

Marca 5 questões aleatórias por matéria como `is_demo = true`. **Idempotente** — se já tem 5 marcadas, pula. Se a matéria não tem questões ainda (banco em construção), pula silenciosamente e loga "matéria X sem questões — pulada".

### 2.3 Fluxo do demo (rotas)

**Todas em `routes/web.php` fora do middleware `auth`**, no grupo com `throttle:60,1`:

| Rota | Nome | Função |
|------|------|--------|
| `GET /probar-gratis` | `demo.show` | Tela de seleção de faculdade (cards visuais) |
| `GET /probar-gratis/configurar` | `demo.configurar` | Gerador de simulacros (réplica do print 4 em modo demo) |
| `POST /probar-gratis/iniciar` | `demo.iniciar` | Cria sessão demo, valida limite, redireciona pra primeira questão |
| `GET /probar-gratis/pregunta/{n}` | `demo.pregunta` | Renderiza questão N (1..5) |
| `POST /probar-gratis/responder/{n}` | `demo.responder` | Grava resposta, retorna gabarito + comentário (JSON) |
| `GET /probar-gratis/resultado` | `demo.resultado` | Tela final com paywall |

### 2.4 Tela `/probar-gratis` (seleção de faculdade)

Layout **público com `layouts/public`**, fundo escuro:

- Header: "Probá gratis" + subtítulo "Resolvé 5 preguntas reales antes de suscribirte. Sin tarjeta, sin compromiso."
- Grid 2x2 (desktop) com cards das 4 faculdades ativas — mesmo componente da seção "Modalidades" da landing, reutilizar
- Cada card clicável → `/probar-gratis/configurar?faculdade={slug}`
- Pode chegar com `?faculdade={slug}` na URL (vindo da landing) e já avança direto pro próximo passo

### 2.5 Tela `/probar-gratis/configurar` (gerador de simulacros — réplica do print 4)

**Esta é a tela mais importante.** Replicar exatamente o layout do print 4, mas com paleta roxa Banco de Choices.

Card central com:

**Título do card:** "Generador de simulacros" + badge sutil "DEMO · 5 preguntas" no canto direito.

**Campo 1: "Elegí tu facultad"**
- Select dropdown nativo, pré-selecionado se vier de query string
- Estilo: fundo `#1a1a22`, borda `rgba(255,255,255,0.08)`, foco com anel roxo
- Ao mudar, recarrega lista de matérias via AJAX (sem reload da página)

**Campo 2: "Elegí las materias (máximo 1 en demo)"**
- Lista scrollável de checkboxes (max-height ~280px), igual print 4
- Cada item mostra: **Nome da matéria** + texto cinza pequeno com metadata: `Cátedra · Plano · "Temas del N parcial"` ou `Cátedra · "Temas del N parcial"`
- Em modo demo: **só pode marcar 1 matéria** (radio behavior simulado com checkbox + JS)
- Item selecionado: fundo `rgba(106, 3, 146, 0.15)`, texto roxo claro
- Cada combinação distinta (matéria + cátedra + plano + parcial) é uma linha — ex:
  - "Bioquímica" `Cátedra II · Plan nuevo · Temas del 1er parcial`
  - "Bioquímica" `Cátedra II · Plan nuevo · Temas del 2do parcial`
  - "Bioquímica" `Cátedra II · Temas del 1er parcial`
  - etc.
- Lógica de geração das linhas: query distinct em `questoes` agrupando por `materia_id, catedra_id, plano, parcial` onde `is_demo = true`

**Campo 3: "Elegí los temas"**
- Lista de checkboxes carregada após selecionar matéria/cátedra/parcial
- **Primeiro item especial:** **"Mandale cualquier tema (todos)"** com fundo destacado e fonte bold — quando marcado, desabilita os outros checkboxes
- Demais itens: lista de temas distintos disponíveis pra essa combinação (query distinct em `questoes.tema`)
- Mostra "Aminoácidos en particular", "Bases púricas y piramidicas", "Metabolismo de proteínas", etc.
- Item marcado: ícone `✓` roxo + texto roxo claro

**Campo 4: "Tiempo total estimado (en minutos)"**
- Input numérico, **read-only em modo demo** (calculado: `quantidade × 1 minuto`)
- Default: 5 (porque demo são 5 questões)

**Campo 5: "¿Cuántas preguntas querés?"**
- Select dropdown, **fixo em 5 e desabilitado em modo demo** (mostrar tooltip ao hover: "En el modo demo el límite es 5. Suscribite para practicar sin límites.")

**Botões:**
- **"Armar simulacro"** (botão azul/roxo principal — usar roxo `#6a0392`)
- **"Limpiar"** (botão secundário, fundo `#2a2a32`)

**Fundo da página:** padrão de linhas topográficas sutil (igual print 4) — usar SVG ou imagem PNG em `public/img/topographic-bg.png` com `opacity: 0.05`, posicionada como `background` da página.

### 2.6 Tela `/probar-gratis/pregunta/{n}` (questão)

Layout limpo, focado:

- **Topo:** progress bar fina + texto "Pregunta {n} de 5 · DEMO"
- **Card central** com:
  - Enunciado da questão
  - 4-5 alternativas em radio buttons grandes, fáceis de clicar
  - Estado vazio: alternativas neutras
  - Após responder: alternativa correta vira **verde**, errada (se selecionada) vira **vermelha**, demais ficam apagadas
- **Bloco de explicação** (aparece após responder):
  - Badge "Justificación"
  - Texto da justificativa (campo `questoes.justificativa` — adicione na migration se ainda não existir)
  - Citação da fonte se disponível (campo `questoes.fonte`)
- **Botões:**
  - Antes de responder: "Responder" (laranja, full width)
  - Depois de responder: "Siguiente pregunta →" (laranja) + "Ver justificación" (já visível, expansível)
- **Barra inferior fixa** discreta com link "Salir del demo" (volta pra `/probar-gratis`)

Comportamento JS:
- Ao clicar "Responder": `POST /probar-gratis/responder/{n}` → grava em `demo_attempts` → recebe JSON com `correta`, `justificativa`, `fonte` → atualiza UI inline (sem reload)
- Após mostrar justificativa, botão muda pra "Siguiente"
- Na 5ª questão, botão "Siguiente" vira "Ver resultado"

### 2.7 Tela `/probar-gratis/resultado` (paywall)

- **Topo:** ícone grande de troféu/medalha em roxo
- **Título:** "¡Terminaste el demo!"
- **Stats grandes:** "{acertos} de 5 correctas · {percentual}%"
- **Card de paywall** com:
  - Título: "Te gustó? Desbloqueá todas las preguntas."
  - Lista de benefícios (checks roxos):
    - "Acceso a 47.000+ preguntas justificadas"
    - "Todas las materias de tu facultad"
    - "Estadísticas detalladas por parcial y tema"
    - "Simulador de exámenes ilimitado"
  - **CTA principal laranja:** "Ver planes" → `/selecionar-materias?demo=1` (passa flag pra UI lembrar que veio do demo)
  - **CTA secundário:** "Ya tengo cuenta · Iniciar sesión" → `/login`
- **Card secundário:** "Compartí con un amigo · Ganá descuento" → leva pra explicação rápida do programa de cupons (Feature 3) com CTA "Crear cuenta y obtener mi cupón"

### 2.8 Anti-abuso (revisão da Feature 4)

Mantém regras anteriores mas com ajustes:
- Cookie `bc_demo_session` (UUID, 30 dias)
- Tabela `demo_attempts`: `id`, `session_uuid`, `ip`, `user_agent_hash`, `materia_id`, `catedra_id` (nullable), `questao_id`, `acertou`, `created_at`
- **Limite por matéria:** 5 questões por sessão por matéria nas últimas 24h. Se atingir, redireciona pra paywall.
- **Limite por IP:** 3 sessões demo por IP por dia.
- **Limite global:** mesmo IP+UA não pode iniciar mais de 5 sessões em 7 dias (anti-fingerprint trivial).
- Throttle Laravel `throttle:60,1` no grupo de rotas.

---

## 3. Sidebar pública (versão "tour")

Em mobile e em telas internas do demo, mostrar offcanvas inspirado no print 1, **mas pra usuário não-logado**:

Itens:
- **Inicio** → `/`
- **Practicar (demo)** → `/probar-gratis`
- **Planes** → `/#planes`
- **Simulacros** → `/#modalidades`
- **Referí y ganá** → `/#como-funciona` (não tem tela própria pra deslogado, manda pra explicação)
- **Ayuda** → `/#faq`
- Separador
- **Iniciar sesión** → `/login`
- **Crear cuenta** → `/selecionar-materias`

Estilo:
- Fundo gradiente escuro `linear-gradient(165deg, #1a0b24 0%, #0f0f14 55%, #0a0a0d 100%)` (já existe nos tokens)
- Item ativo destacado em roxo `#7c3aed`
- Ícones Bootstrap Icons, brancos com opacidade 0.7

---

## 4. Configurações novas

### `config/landing.php` (criar)

```php
return [
    'stats' => [
        ['numero' => '47.000+', 'label' => 'Preguntas justificadas'],
        ['numero' => 'Excelencia', 'label' => 'Fuentes oficiales'],
        ['numero' => 'Seguimiento', 'label' => 'Detallado e inteligente'],
        ['numero' => '2.000+ alumnos', 'label' => 'Comunidad activa'],
    ],
    'footer' => [
        'objetivo' => 'Poner a disposición la mayor cantidad de choices...',
    ],
    'demo' => [
        'questoes_por_materia' => env('DEMO_QUESTOES_POR_MATERIA', 5),
        'limite_sessoes_por_ip_dia' => env('DEMO_LIMITE_IP_DIA', 3),
    ],
];
```

### `alter_faculdades_add_descricao` (migration)

Adicionar `descricao_curta` (string 200, nullable) em `faculdades`. Atualizar seeder pra preencher:

- UBA: "Universidad de Buenos Aires · Ciclo Biomédico y Clínico"
- La Plata: "Universidad Nacional de La Plata"
- Barceló: "Universidad Barceló"
- CBC: "Ciclo Básico Común · UBA XXI"

### `alter_questoes_add_justificativa_fonte_plano`

Adicionar:
- `justificativa` (text nullable)
- `fonte` (string 500 nullable)
- `plano` (string nullable)

Se essas colunas já existirem, pular.

---

## 5. Estrutura de arquivos esperada

Crie/atualize:

```
resources/views/
  layouts/
    public.blade.php                    [atualizar topbar + offcanvas + footer]
  pages/
    index.blade.php                     [refazer com seções 1.1–1.9]
    partials/
      topbar.blade.php                  [novo]
      footer.blade.php                  [novo]
      faq-accordion.blade.php           [novo]
      faculdade-card.blade.php          [novo, reutilizado em landing e demo]
      planes-grid.blade.php             [novo]
  demo/
    show.blade.php                      [seleção de faculdade]
    configurar.blade.php                [gerador de simulacros — print 4]
    pregunta.blade.php                  [tela de questão]
    resultado.blade.php                 [paywall final]

public/assets/css/
  landing-v2.css                        [novo, todos estilos da landing nova]
  demo.css                              [novo, estilos do fluxo demo]

app/Http/Controllers/
  PageController.php                    [atualizar method home() pra carregar dados dinâmicos]
  DemoController.php                    [novo, com 6 actions]

app/Console/Commands/
  MarcarQuestoesDemoCommand.php         [já criado na F4, revisar]

config/
  landing.php                           [novo]

database/migrations/
  YYYY_MM_DD_alter_faculdades_add_descricao.php
  YYYY_MM_DD_alter_questoes_add_justificativa_fonte_plano.php

lang/
  es_AR.json                            [adicionar todas chaves novas]
  pt_BR.json                            [traduzir]
  en_US.json                            [traduzir]
```

---

## 6. Restrições / não fazer

- **Não** alterar nada da área autenticada (`layouts/app`, dashboard, banco de perguntas autenticado, perfil, estatísticas) — escopo é só público
- **Não** copiar o azul, o nome ou o logo do El Preguntero — inspiração só de **estrutura/seções**
- **Não** criar imagens de mockup placeholder em PNG — usar SVG/CSS puro com TODO comentado
- **Não** hardcodar nenhum texto em Blade — toda string visível em chave i18n nos 3 locales
- **Não** quebrar a Feature 1 (hierarquia) — esta UI **depende** dos models já criados
- **Não** quebrar fluxo `/login`, `/selecionar-materias`, `/checkout-mercadopago` — apenas linkar a partir da landing
- **Não** assumir que existem questões no banco — todas as queries devem lidar com lista vazia gracefully (mostrar "Esta materia aún no tiene preguntas disponibles" em vez de erro)
- **Manter** cor primária `#6a0392`, tokens `--app-*`, fontes Inter/Poppins
- **Manter** SQL compatível SQLite + MySQL

---

## 7. Checklist de entrega

- [ ] `pages/index.blade.php` redesenhado com 9 seções (1.1 a 1.9)
- [ ] Topbar pública sticky com blur backdrop
- [ ] Hero com gradiente roxo + CTA duplo (laranja + outline roxo)
- [ ] Banner de stats lendo de `config/landing.php`
- [ ] Seção Modalidades renderizando faculdades dinamicamente do BD
- [ ] Seção "Cómo funciona" com 3 passos
- [ ] Seção Planes lendo de `config/signup.php`
- [ ] FAQ accordion com 6+ perguntas em chaves i18n
- [ ] CTA final "Empezá hoy"
- [ ] Footer com 3 colunas + links sociais configuráveis
- [ ] Offcanvas pública com itens de tour
- [ ] `DemoController` com 6 actions
- [ ] Tela seleção de faculdade (`demo.show`)
- [ ] Gerador de simulacros (`demo.configurar`) replicando print 4 com paleta roxa
- [ ] Tela de questão com gabarito inline e justificativa
- [ ] Paywall final com 2 CTAs
- [ ] Anti-abuso (cookie + IP + UA hash + throttle)
- [ ] Comando `banco:marcar-demo` idempotente
- [ ] Migrations novas (descricao, justificativa, fonte, plano)
- [ ] Config `landing.php` criada
- [ ] Traduções nos 3 locales pra todas as strings novas
- [ ] CSS isolado em `landing-v2.css` e `demo.css`
- [ ] Lida com banco vazio sem erro (estado "ainda sem questões")
- [ ] Testes Feature: rota pública responde 200, demo respeita limite de 5, paywall aparece
- [ ] Mobile: testar em viewport 380px (topbar collapse, hero stack, cards 1 coluna)

---

## 8. Ordem de execução

1. Migrations + seeder atualizado (descricao, justificativa, fonte, plano)
2. `config/landing.php` + tradução de stats/FAQ/copy nos 3 locales
3. Layout `public.blade.php` + topbar + footer + offcanvas
4. `pages/index.blade.php` seção por seção (hero → stats → modalidades → como-funciona → planes → faq → cta-final)
5. `DemoController` esqueleto com as 6 rotas
6. Tela `demo/show` (seleção faculdade) — reusa componente `faculdade-card`
7. Tela `demo/configurar` (gerador) — a mais complexa
8. Tela `demo/pregunta` + JS de resposta inline
9. Tela `demo/resultado` (paywall)
10. Anti-abuso (rate limit, demo_attempts, throttle middleware)
11. Comando `banco:marcar-demo` (revisar)
12. Testes manuais em viewport mobile/desktop
13. Testes Feature mínimos

Pergunte se algum requisito visual estiver ambíguo antes de chutar.
