<?php

namespace Tests\Unit;

use App\Support\Question;
use PHPUnit\Framework\TestCase;

class QuestionExplicacoesAlternativasTest extends TestCase
{
    public function test_formato_por_mascote_retorna_a_lista_do_mascote_pedido(): void
    {
        $q = new Question([
            'opcoes' => ['A', 'B'],
            'explicacoes' => [
                'robo' => ['Explicação lógica A', 'Explicação lógica B'],
                'fantasma' => ['Explicação leve A', 'Explicação leve B'],
                'gato' => ['Explicação curiosa A', 'Explicação curiosa B'],
            ],
        ]);

        $this->assertSame(['Explicação lógica A', 'Explicação lógica B'], $q->getExplicacoesAlternativas('robo'));
        $this->assertSame(['Explicação leve A', 'Explicação leve B'], $q->getExplicacoesAlternativas('fantasma'));
        $this->assertSame(['Explicação curiosa A', 'Explicação curiosa B'], $q->getExplicacoesAlternativas('gato'));
    }

    public function test_formato_por_mascote_sem_mascote_pedido_cai_na_primeira_variante(): void
    {
        $q = new Question([
            'opcoes' => ['A', 'B'],
            'explicacoes' => [
                'robo' => ['Explicação lógica A', 'Explicação lógica B'],
                'fantasma' => ['Explicação leve A', 'Explicação leve B'],
            ],
        ]);

        $this->assertSame(['Explicação lógica A', 'Explicação lógica B'], $q->getExplicacoesAlternativas(null));
        $this->assertSame(['Explicação lógica A', 'Explicação lógica B'], $q->getExplicacoesAlternativas('inexistente'));
    }

    public function test_formato_legado_flat_ignora_mascote_e_retorna_a_mesma_lista(): void
    {
        $q = new Question([
            'opcoes' => ['A', 'B'],
            'explicacoes' => ['Explicação única A', 'Explicação única B'],
        ]);

        $this->assertSame(['Explicação única A', 'Explicação única B'], $q->getExplicacoesAlternativas('robo'));
        $this->assertSame(['Explicação única A', 'Explicação única B'], $q->getExplicacoesAlternativas('gato'));
        $this->assertSame(['Explicação única A', 'Explicação única B'], $q->getExplicacoesAlternativas(null));
    }

    public function test_sem_explicacoes_retorna_lista_vazia(): void
    {
        $q = new Question(['opcoes' => ['A', 'B']]);

        $this->assertSame([], $q->getExplicacoesAlternativas('robo'));
    }
}
