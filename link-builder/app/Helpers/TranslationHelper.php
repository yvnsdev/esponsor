<?php

namespace App\Helpers;

class TranslationHelper
{
    private static $translations = [
        'en' => [
            'powered_by' => 'Powered by eSponsor',
            'visit' => 'Visit',
            'watch' => 'Watch',
            'contact' => 'Contact',
        ],
        'es' => [
            'powered_by' => 'Desarrollado por eSponsor',
            'visit' => 'Visitar',
            'watch' => 'Ver',
            'contact' => 'Contacto',
        ],
        'fr' => [
            'powered_by' => 'Propulsé par eSponsor',
            'visit' => 'Visiter',
            'watch' => 'Regarder',
            'contact' => 'Contact',
        ],
        'it' => [
            'powered_by' => 'Offerto da eSponsor',
            'visit' => 'Visita',
            'watch' => 'Guarda',
            'contact' => 'Contatto',
        ],
        'de' => [
            'powered_by' => 'Betrieben von eSponsor',
            'visit' => 'Besuchen',
            'watch' => 'Ansehen',
            'contact' => 'Kontakt',
        ],
    ];

    public static function get(string $key, string $locale = 'en'): string
    {
        return self::$translations[$locale][$key] ?? self::$translations['en'][$key] ?? $key;
    }
}
