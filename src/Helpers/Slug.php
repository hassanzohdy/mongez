<?php
declare(strict_types=1);

namespace HZ\Illuminate\Mongez\Helpers;

use Cocur\Slugify\Slugify;
use Illuminate\Support\Str;

class Slug
{
    /**
     * make slug for any model
     *
     * @param string $text
     * @param string $localeCode
     * @param string $separator
     * @return string
     */
    public static function make(string $text, string $localeCode = 'en', string $separator = '-'): string
    {
        return $localeCode === 'ar' ? static::arabicSlug($text) : Str::slug($text, $separator, $localeCode);
    }

    /**
     * slug by arabic latter
     *
     * @param $string
     * @return string
     */
    public static function arabicSlug($string): string
    {
        $slug = new Slugify(['regexp' => '/([^\p{Arabic}a-zA-Z0-9]+|-+)/u']);

        $slug->addRules([
            'أ' => 'أ',
            'ب' => 'ب',
            'ت' => 'ت',
            'ث' => 'ث',
            'ج' => 'ج',
            'ح' => 'ح',
            'خ' => 'خ',
            'د' => 'د',
            'ذ' => 'ذ',
            'ر' => 'ر',
            'ز' => 'ز',
            'س' => 'س',
            'ش' => 'ش',
            'ص' => 'ص',
            'ض' => 'ض',
            'ط' => 'ط',
            'ظ' => 'ظ',
            'ع' => 'ع',
            'غ' => 'غ',
            'ف' => 'ف',
            'ق' => 'ق',
            'ك' => 'ك',
            'ل' => 'ل',
            'م' => 'م',
            'ن' => 'ن',
            'ه' => 'ه',
            'و' => 'و',
            'ي' => 'ي',
        ]);

        return $slug->slugify($string);
    }
}
