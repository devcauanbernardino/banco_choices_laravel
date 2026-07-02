<?php

namespace App\Support;

/**
 * Encurta o feedback editorial de uma questão pra uso como verso de flashcard:
 * mantém só a explicação da resposta correta, cortando a parte que detalha
 * cada alternativa errada uma a uma (útil no modo prova, verboso demais num cartão).
 */
final class FlashcardAnswerFormatter
{
    private const DELIMITADORES = [
        'As demais alternativas estão incorretas',
        'As demais alternativas estao incorretas',
        'As demais opções estão incorretas',
        'As outras alternativas estão incorretas',
        'Las demás alternativas son incorrectas',
        'Las demás alternativas están incorrectas',
        'Las demás alternativas son válidas',
        'Las otras alternativas son incorrectas',
        'Las otras opciones no satisfacen',
        'Incorretas: ',
        'Incorrectas: ',
    ];

    public static function format(string $feedback): string
    {
        $texto = trim($feedback);

        $corte = null;
        foreach (self::DELIMITADORES as $marcador) {
            $pos = mb_strpos($texto, $marcador);
            if ($pos !== false && ($corte === null || $pos < $corte)) {
                $corte = $pos;
            }
        }

        if ($corte !== null) {
            $texto = trim(mb_substr($texto, 0, $corte));
        }

        $texto = rtrim($texto, " \n\r\t");

        if ($texto !== '' && ! in_array(mb_substr($texto, -1), ['.', '!', '?'], true)) {
            $texto .= '.';
        }

        return $texto;
    }
}
