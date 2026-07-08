<?php

namespace App\Support;

/**
 * Nome de referencia das materias e em espanhol (es). Traducao por locale,
 * mapeada por slug (estavel entre ambientes, ao contrario do id).
 */
final class MateriaLocale
{
    private const NAMES = [
        'pt_BR' => [
            'microbiologia-y-parasitologia' => 'Microbiologia e Parasitologia',
            'biologia-celular' => 'Biologia Celular',
            'biologia-la-plata' => 'Biologia (UNLP)',
            'biologia-cbc' => 'Biologia (CBC)',
            'farmacologia-ii-catedra-3' => 'Farmacologia II — Cátedra III',
            'histologia' => 'Histologia',
            'embriologia' => 'Embriologia',
            'biologia-molecular-y-genetica' => 'Biologia Molecular e Genética',
            'fisiologia-y-biofisica' => 'Fisiologia e Biofísica',
            'bioquimica' => 'Bioquímica',
            'inmunologia-humana' => 'Imunologia Humana',
            'patologia' => 'Patologia',
            'farmacologia-i' => 'Farmacologia I',
            'farmacologia-ii' => 'Farmacologia II',
            'medicina-i' => 'Medicina I',
            'bioetica' => 'Bioética',
            'neurocirugia' => 'Neurocirurgia',
            'ciencias-sociales-y-medicina' => 'Ciências Sociais e Medicina',
            'farmacologia-general-barcelo' => 'Farmacologia Geral',
        ],
        'en_US' => [
            'microbiologia-y-parasitologia' => 'Microbiology and Parasitology',
            'biologia-celular' => 'Cell Biology',
            'biologia-la-plata' => 'Biology (UNLP)',
            'biologia-cbc' => 'Biology (CBC)',
            'farmacologia-ii-catedra-3' => 'Pharmacology II — Chair III',
            'histologia' => 'Histology',
            'embriologia' => 'Embryology',
            'biologia-molecular-y-genetica' => 'Molecular Biology and Genetics',
            'fisiologia-y-biofisica' => 'Physiology and Biophysics',
            'bioquimica' => 'Biochemistry',
            'inmunologia-humana' => 'Human Immunology',
            'patologia' => 'Pathology',
            'farmacologia-i' => 'Pharmacology I',
            'farmacologia-ii' => 'Pharmacology II',
            'medicina-i' => 'Medicine I',
            'bioetica' => 'Bioethics',
            'neurocirugia' => 'Neurosurgery',
            'ciencias-sociales-y-medicina' => 'Social Sciences and Medicine',
            'farmacologia-general-barcelo' => 'General Pharmacology',
        ],
    ];

    /**
     * @param  object{nome: string, slug: ?string}  $materia
     */
    public static function nome(object $materia, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (str_starts_with(strtolower(str_replace('-', '_', $locale)), 'es')) {
            return $materia->nome;
        }

        $slug = $materia->slug ?? null;
        if (! is_string($slug) || $slug === '') {
            return $materia->nome;
        }

        $key = QuestionLocale::normalizeLocaleKey($locale);

        return self::NAMES[$key][$slug] ?? $materia->nome;
    }
}
