<?php

namespace Tests\Unit;

use App\Services\Questions\Farmaco2Cat3Importer;
use App\Services\Questions\Farmaco2Cat3SectionCatalog;
use PHPUnit\Framework\TestCase;

class Farmaco2Cat3ImporterTest extends TestCase
{
    public function test_converts_single_choice_question(): void
    {
        $importer = new Farmaco2Cat3Importer;
        $row = $importer->convertOne([
            'pregunta' => 'Señale la opción CORRECTA:',
            'opciones' => ['a', 'b', 'c', 'd'],
            'correcta' => [2],
            'multiple' => false,
        ], 1, 'inotropicos');

        $this->assertNotNull($row);
        $this->assertSame(1, $row['numero']);
        $this->assertSame('C', $row['resposta_correta']);
        $this->assertSame('inotropicos', $row['origem_seccion']);
        $this->assertCount(4, $row['opcoes']);
    }

    public function test_skips_checkbox_questions(): void
    {
        $importer = new Farmaco2Cat3Importer;
        $row = $importer->convertOne([
            'pregunta' => 'Marque todas las correctas',
            'opciones' => ['a', 'b', 'c', 'd'],
            'correcta' => [0, 1],
            'multiple' => true,
        ], 1, 'test');

        $this->assertNull($row);
    }

    public function test_detects_incorrecta_nota(): void
    {
        $importer = new Farmaco2Cat3Importer;
        $this->assertSame(
            'Selecionar opção INCORRETA',
            $importer->detectNota('Indique la afirmación INCORRECTA sobre el fármaco')
        );
    }

    public function test_section_catalog_maps_parcial_and_tema(): void
    {
        $m = Farmaco2Cat3SectionCatalog::resolve('penicilinas');
        $this->assertSame('2', $m['parcial']);
        $this->assertSame('Penicilinas', $m['tema']);

        $f = Farmaco2Cat3SectionCatalog::resolve('final_17_07_2024');
        $this->assertSame('final', $f['parcial']);

        $l = Farmaco2Cat3SectionCatalog::resolve('examenlibre');
        $this->assertSame('libre', $l['parcial']);
    }
}
