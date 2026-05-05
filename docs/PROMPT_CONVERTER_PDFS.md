# Prompt — Converter PDFs de provas em JSON (padrão Banco de Choices)

Cole este prompt no início da nova sessão, junto com:
- Os PDFs de provas que você quer digitalizar
- O arquivo `questoes_microbiologia_refinado.json` (referência do padrão)

---

## CONTEXTO

Estou construindo um sistema de questões de medicina chamado **Banco de Choices** (Laravel 12, alvo: alunos de UBA, La Plata, Barceló, CBC). Vou te mandar **PDFs de provas resolvidas** (geralmente da UBA / Cátedra A, mas pode ser de outras faculdades/cátedras) e quero que você os **converta em JSON no padrão exato do sistema**.

O padrão do sistema é o que está no arquivo de referência `questoes_microbiologia_refinado.json` que estou anexando. **Estude a estrutura desse arquivo antes de começar.**

## SCHEMA OBRIGATÓRIO

```json
{
  "titulo": "Nome da Matéria - Tipo de Prova",
  "subtitulo": "Período - Cátedra X (Nome do titular) - Faculdade",
  "total_questoes": N,
  "questoes": [
    {
      "numero": 1,
      "pergunta": "Texto da pergunta exatamente como está no PDF",
      "opcoes": [
        {"letra": "A", "texto": "..."},
        {"letra": "B", "texto": "..."},
        {"letra": "C", "texto": "..."},
        {"letra": "D", "texto": "..."}
      ],
      "tipo": "multipla_escolha",
      "nota": null,
      "resposta_correta": "B",
      "feedback": "Resposta correta: B. [explicação da correta].\n\nAs demais alternativas estão incorretas:\nA) [por que A está errada]\nC) [por que C está errada]\nD) [por que D está errada]"
    }
  ]
}
```

### Regras dos campos

- **`numero`**: integer sequencial dentro do arquivo, começando em 1.
- **`pergunta`**: texto fiel ao PDF (não corrigir gramática nem reescrever).
- **`opcoes`**: sempre 4 alternativas com letras `A`, `B`, `C`, `D` (maiúsculas).
- **`tipo`**: sempre `"multipla_escolha"`.
- **`nota`**: auto-detectar pelo enunciado:
  - Se contém "INCORRECTA" ou "no es correcta" → `"Selecionar opção INCORRETA"`
  - Se contém "EXCEPTO" → `"Selecionar opção EXCETO (incorreta)"`
  - Caso contrário → `null`
- **`resposta_correta`**: string única `"A"` | `"B"` | `"C"` | `"D"` (a letra da alternativa correta).
- **`feedback`**: texto consolidado seguindo a estrutura abaixo, **sem aspas, sem markdown**:
  - Linha 1: `"Resposta correta: X. [justificativa da correta extraída do PDF]"`
  - Linha em branco
  - `"As demais alternativas estão incorretas:"`
  - Uma linha por alternativa errada: `"X) [justificativa do PDF]"`

## REGRAS DE PROCESSAMENTO

Antes de gerar qualquer JSON, faça **uma única rodada de perguntas pra mim** confirmando:

1. **TEMA 1 vs TEMA 2:** PDFs da UBA geralmente têm 2 versões da mesma prova (TEMA 1 e TEMA 2), que são permutações das mesmas perguntas com ordem/redação ligeiramente diferente. Default: importar **só TEMA 1** de cada PDF e tratar TEMA 2 como duplicata. Confirmar comigo se é isso mesmo.

2. **Marcação de tipo de prova:** os PDFs costumam ser "Final" ou "1º Parcial" (às vezes 2º Parcial). Quero que cada **tipo de prova** vire um arquivo JSON separado. Default: agrupar por tipo (todos os Finais num arquivo, todos os 1º Parciais em outro). Confirmar.

3. **Tipos de questão:** PDFs costumam ter:
   - **MCQ** (múltipla escolha com 4 alternativas) — incluir
   - **Esquemas** (identificar partes em uma imagem) — **excluir** por padrão
   - **Discursivas** (explicar conceito) — **excluir** por padrão
   - Confirmar se é só MCQ mesmo.

4. **Deduplicação entre turnos:** se há vários turnos da mesma prova (1º turno, 2º turno, 3º turno), tem questões repetidas entre eles. Default: deduplicar — se enunciado >85% similar entre turnos, manter só a primeira ocorrência (ordem dos turnos: 1º → 2º → 3º). Confirmar.

Se o usuário responder "tudo padrão" ou similar, aplicar todos os defaults acima sem repetir as perguntas.

## VALIDAÇÕES OBRIGATÓRIAS NO FINAL

Antes de entregar o JSON, **rodar estas validações** e reportar resultado:

1. Cada questão tem **exatamente 4 alternativas** com letras A, B, C, D
2. Cada questão tem **exatamente 1 alternativa marcada como correta** (`resposta_correta` válido)
3. `tipo` é sempre `"multipla_escolha"`
4. `numero` é sequencial sem gaps
5. `total_questoes` no header bate com o tamanho do array `questoes`
6. Todos os campos obrigatórios estão presentes em cada questão (`numero`, `pergunta`, `opcoes`, `tipo`, `nota`, `resposta_correta`, `feedback`)

Reportar no formato:
```
✅ Validação OK: N questões, 0 erros de schema
```

Se houver erros, listar quais e em quais questões.

## FIDELIDADE AO PDF

- **Manter texto em espanhol** (não traduzir pra português).
- **Não corrigir** erros tipográficos do PDF original — eles podem ser intencionais ou parte da redação oficial da cátedra.
- Se uma questão tem **gabarito inconsistente no PDF** (ex: opções não batem com a explicação), **pular essa questão** e reportar.
- Se uma questão tem **5 alternativas** ou outro formato fora do padrão MCQ tradicional, perguntar como tratar antes de pular.

## NOMEAÇÃO DOS ARQUIVOS

Padrão: `[materia_slug]_[faculdade_slug]_[tipo_prova]_[ano].json`

Exemplos:
- `biologia_cbc_final_dic2021.json`
- `biologia_cbc_1parcial_2022.json`
- `bioquimica_uba_2parcial_2023.json`
- `microbiologia_uba_final_2024.json`

## PROCESSO ESPERADO

1. Você lê os PDFs anexados e identifica:
   - Matéria
   - Faculdade / Cátedra
   - Tipo de prova (Final / 1º Parcial / 2º Parcial)
   - Período (mês/ano)
   - Quantos turnos por tipo de prova

2. Faz a rodada única de 4 perguntas de confirmação (seção REGRAS DE PROCESSAMENTO).

3. Processa os PDFs aplicando as regras confirmadas.

4. Gera **um arquivo JSON por tipo de prova** (Final num arquivo, Parcial em outro).

5. Roda as validações e reporta resultado.

6. Apresenta os arquivos finais na conversa.

## RESTRIÇÕES

- **Não inventar questões nem alternativas** que não estejam no PDF.
- **Não classificar tema/parcial/faculdade dentro do JSON** — esses campos não existem no padrão. Eles vêm da hierarquia do banco quando a importação for feita pelo comando artisan do Laravel.
- **Não incluir campos extras** (`tema`, `parcial`, `is_demo`, `faculdade_slug`, `id`, etc.) — só o que está no schema obrigatório acima.
- **Não fazer dois arquivos pra mesma prova** se podem ser consolidados (ex: se mandei 3 turnos do mesmo Parcial, vira 1 arquivo só com tudo deduplicado).
- **Não traduzir o texto** das questões.

## EXEMPLO COMPLETO (1 questão totalmente convertida)

Entrada (texto extraído do PDF):

> 1. Sobre la transcripción en eucariotas podemos afirmar que:
> a) La ARN polimerasa sintetiza una cadena de ARN en sentido 3'-5' a partir del ADN molde.
> Incorrecto: sintetiza en sentido 5'-3' a partir de la cadena molde que es leída en sentido 3'-5'.
> b) El transcripto primario posee una secuencia de bases complementaria a la cadena molde pero posee U en vez de T.
> Correcto: Dado que durante la transcripción la doble cadena de ADN se abre y los nucleótidos de la nueva cadena se van posicionando por la complementariedad de bases, la nueva cadena de ARN es complementaria a la cadena que fue leída por la ARN polimerasa.
> c) Existen factores de transcripción específicos que son requeridos por el operador.
> Incorrecto: los factores de transcripción específicos son requeridos por los reguladores de los genes eucariotas.
> d) El transcripto primario debe salir del núcleo para ser traducido.
> Incorrecto: el transcripto primario debe ser procesado antes de salir del núcleo.

Saída esperada:

```json
{
  "numero": 1,
  "pergunta": "Sobre la transcripción en eucariotas podemos afirmar que:",
  "opcoes": [
    {"letra": "A", "texto": "La ARN polimerasa sintetiza una cadena de ARN en sentido 3'-5' a partir del ADN molde."},
    {"letra": "B", "texto": "El transcripto primario posee una secuencia de bases complementaria a la cadena molde pero posee U en vez de T."},
    {"letra": "C", "texto": "Existen factores de transcripción específicos que son requeridos por el operador."},
    {"letra": "D", "texto": "El transcripto primario debe salir del núcleo para ser traducido."}
  ],
  "tipo": "multipla_escolha",
  "nota": null,
  "resposta_correta": "B",
  "feedback": "Resposta correta: B. Dado que durante la transcripción la doble cadena de ADN se abre y los nucleótidos de la nueva cadena se van posicionando por la complementariedad de bases, la nueva cadena de ARN es complementaria a la cadena que fue leída por la ARN polimerasa.\n\nAs demais alternativas estão incorretas:\nA) Sintetiza en sentido 5'-3' a partir de la cadena molde que es leída en sentido 3'-5'.\nC) Los factores de transcripción específicos son requeridos por los reguladores de los genes eucariotas.\nD) El transcripto primario debe ser procesado antes de salir del núcleo."
}
```

Note que: o "Incorrecto:" / "Correcto:" do PDF é jogado fora (vira a justificativa propriamente dita), as letras viram maiúsculas, e o feedback é consolidado no formato canônico do sistema.

---

**Comece** lendo os PDFs anexados, depois faça a rodada única de 4 perguntas de confirmação antes de processar qualquer coisa.
