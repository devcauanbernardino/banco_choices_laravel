-- Script gerado para popular questoes da materia Neurocirugia (slug = neurocirugia)
-- Rodar inteiro de uma vez no phpMyAdmin (aba SQL) no banco de producao.
SET @materia_id = (SELECT id FROM materias WHERE slug = "neurocirugia" LIMIT 1);

INSERT INTO questoes (materia_id, catedra_id, overlay_key, parcial, tema, is_demo, created_at, updated_at)
SELECT @materia_id, NULL, v.overlay_key, NULL, v.tema, (v.overlay_key < 12), NOW(), NOW()
FROM (
  SELECT 0 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 1 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 2 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 3 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 4 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 5 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 6 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 7 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 8 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 9 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 10 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 11 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 12 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 13 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 14 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 15 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 16 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 17 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 18 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 19 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 20 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 21 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 22 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 23 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 24 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 25 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 26 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 27 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 28 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 29 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 30 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 31 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 32 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 33 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 34 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 35 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 36 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 37 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 38 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 39 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 40 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 41 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 42 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 43 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 44 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 45 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 46 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 47 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 48 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 49 AS overlay_key, "Examen Final (Hospital Bernardino Rivadavia)" AS tema
  UNION ALL
  SELECT 50 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 51 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 52 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 53 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 54 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 55 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 56 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 57 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 58 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 59 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 60 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 61 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 62 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 63 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 64 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 65 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 66 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 67 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 68 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 69 AS overlay_key, "Tumores" AS tema
  UNION ALL
  SELECT 70 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 71 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 72 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 73 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 74 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 75 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 76 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 77 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 78 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 79 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 80 AS overlay_key, "Hematomas" AS tema
  UNION ALL
  SELECT 81 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 82 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 83 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 84 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 85 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 86 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 87 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 88 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 89 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 90 AS overlay_key, "TEC" AS tema
  UNION ALL
  SELECT 91 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 92 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 93 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 94 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 95 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 96 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 97 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 98 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 99 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 100 AS overlay_key, "Columna Degenerativa" AS tema
  UNION ALL
  SELECT 101 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 102 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 103 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 104 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 105 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 106 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 107 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 108 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 109 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 110 AS overlay_key, "Hipertensión Endocraneana (HTE)" AS tema
  UNION ALL
  SELECT 111 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 112 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 113 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 114 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 115 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 116 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 117 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 118 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 119 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 120 AS overlay_key, "Hidrocefalia" AS tema
  UNION ALL
  SELECT 121 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 122 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 123 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 124 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 125 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 126 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 127 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 128 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 129 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 130 AS overlay_key, "Aneurismas" AS tema
  UNION ALL
  SELECT 131 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 132 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 133 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 134 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 135 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 136 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 137 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 138 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 139 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 140 AS overlay_key, "Infecciosas" AS tema
  UNION ALL
  SELECT 141 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 142 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 143 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 144 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 145 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 146 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 147 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 148 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 149 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 150 AS overlay_key, "Traumatismo de Columna" AS tema
  UNION ALL
  SELECT 151 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 152 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 153 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 154 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 155 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 156 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 157 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 158 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 159 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 160 AS overlay_key, "Síndromes Medulares" AS tema
  UNION ALL
  SELECT 161 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 162 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 163 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 164 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 165 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 166 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 167 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 168 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 169 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 170 AS overlay_key, "Casos Clínicos y HSA" AS tema
  UNION ALL
  SELECT 171 AS overlay_key, "Parkinson" AS tema
  UNION ALL
  SELECT 172 AS overlay_key, "Neuralgia del Trigémino" AS tema
  UNION ALL
  SELECT 173 AS overlay_key, "Chiari I y II" AS tema
  UNION ALL
  SELECT 174 AS overlay_key, "Chiari I y II" AS tema
  UNION ALL
  SELECT 175 AS overlay_key, "Chiari I y II" AS tema
  UNION ALL
  SELECT 176 AS overlay_key, "Chiari I y II" AS tema
  UNION ALL
  SELECT 177 AS overlay_key, "Chiari I y II" AS tema
  UNION ALL
  SELECT 178 AS overlay_key, "Craneosinostosis" AS tema
  UNION ALL
  SELECT 179 AS overlay_key, "Craneosinostosis" AS tema
  UNION ALL
  SELECT 180 AS overlay_key, "Craneosinostosis" AS tema
  UNION ALL
  SELECT 181 AS overlay_key, "Craneosinostosis" AS tema
  UNION ALL
  SELECT 182 AS overlay_key, "Craneosinostosis" AS tema
  UNION ALL
  SELECT 183 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 184 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 185 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 186 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 187 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 188 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 189 AS overlay_key, "Cefaleas y Neuralgias" AS tema
  UNION ALL
  SELECT 190 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 191 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 192 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 193 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 194 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 195 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 196 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 197 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 198 AS overlay_key, "ACV" AS tema
  UNION ALL
  SELECT 199 AS overlay_key, "ACV" AS tema
) v
ON DUPLICATE KEY UPDATE tema = v.tema, is_demo = (v.overlay_key < 12);

SELECT @materia_id AS materia_id_neurocirugia, COUNT(*) AS total_questoes, SUM(is_demo) AS total_demo
FROM questoes WHERE materia_id = @materia_id;