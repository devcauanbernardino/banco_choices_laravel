/**
 * Extrai preguntasPorSeccion do preguntas.of.js do site farmaco2cat3choice.
 * Uso: node scripts/farmaco2cat3-extract.cjs <caminho-para-preguntas.of.js>
 */
'use strict';

const path = require('path');
const fs = require('fs');

const jsPath = process.argv[2];
if (!jsPath || !fs.existsSync(jsPath)) {
  process.stderr.write('Uso: node farmaco2cat3-extract.cjs <preguntas.of.js>\n');
  process.exit(1);
}

global.document = {
  addEventListener: () => {},
  getElementById: () => null,
  querySelector: () => null,
  querySelectorAll: () => [],
};
global.window = global;

const mod = require(path.resolve(jsPath));
if (!mod || !mod.preguntasPorSeccion) {
  process.stderr.write('preguntasPorSeccion não encontrado no módulo.\n');
  process.exit(1);
}

process.stdout.write(JSON.stringify(mod.preguntasPorSeccion));
