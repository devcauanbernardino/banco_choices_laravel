# Farmacología II — Cátedra III (importação farmaco2cat3choice)

Banco importado do site público [farmaco2cat3choice](https://farmaco2cat3choice.github.io/farmaco2cat3choice/#menu) (repositório [farmaco2cat3choice/farmaco2cat3choice](https://github.com/farmaco2cat3choice/farmaco2cat3choice)).

## Identificadores no sistema

| Campo | Valor |
|--------|--------|
| `materia_id` | **5** |
| Slug matéria | `farmacologia-ii-catedra-3` |
| Agrupamento | UBA · Ciclo Clínico |
| Cátedra | `catedra-iii` — Cátedra III |
| JSON | `storage/app/data/questoes_farmaco2_cat3.json` |

## Comandos

```bash
# Importar / atualizar JSON a partir do GitHub (requer Node.js)
php artisan questions:import-farmaco2cat3

# Configurar catálogo + tabela questoes (parcial, tema, cátedra)
php artisan questions:setup-farmaco2cat3

# Tudo de uma vez (importa só se o JSON não existir)
php artisan questions:setup-farmaco2cat3 --import

# Reimportar do site e re-sincronizar metadados
php artisan questions:setup-farmaco2cat3 --reimport
```

Após mudanças no JSON:

```bash
php artisan questions:repair --force questoes_farmaco2_cat3.json
php artisan questions:setup-farmaco2cat3
```

## Traduções (pt_BR / en_US)

O texto base está em espanhol. Para demo/simulador em português ou inglês:

```bash
php artisan questions:build-i18n questoes_farmaco2_cat3.json pt
php artisan questions:build-i18n questoes_farmaco2_cat3.json en
```

Grava em `storage/app/data/i18n/{pt_BR,en_US}/questoes_farmaco2_cat3.json`. Se interromper, volte a correr o mesmo comando (retoma automaticamente). Recomeçar do zero: `--fresh`.

Requer `composer install` (pacote `stichoza/google-translate-php`) e rede. ~2427 questões → várias horas por idioma.


Cada questão no JSON traz `origem_seccion` (chave do site). O mapeamento em `Farmaco2Cat3SectionCatalog` define:

- **`parcial`**: `1` \| `2` \| `3` \| `final` \| `libre` (filtros do banco / simulador)
- **`tema`**: rótulo legível (ex.: «Penicilinas», «1er parcial 11-09-2024»)

Parciais espelham o menu do site: 1er / 2do / 3er parcial, simulacros por data, finais e examen libre.

## Questões não importadas

29 itens do site ficam de fora (checkbox, 5 alternativas ou várias respostas corretas). Ver saída verbosa de `questions:import-farmaco2cat3 -v`.

## Atribuir matéria a um usuário

No admin ou via `usuarios_materias`, incluir `materia_id = 5` (ou compra pelo fluxo de addon).
