<?php

namespace App\Support;

/**
 * Server-side text formatting shared by the international fact sheets
 * (Foord QC card 430). Each method has a client-side twin in
 * resources/views/funds/partials/global-intl-fixes.blade.php
 * (`window.intlFormatters`) so an edited value re-renders identically.
 */
class FactsheetText
{
    private const SUPERSCRIPTS = [
        '¹' => '1', '²' => '2', '³' => '3', '⁴' => '4', '⁵' => '5',
        '⁶' => '6', '⁷' => '7', '⁸' => '8', '⁹' => '9',
    ];

    /**
     * Unicode superscript digits ("VALUE²") become <sup> markup, so every
     * note number shares the one 5pt superscript style instead of the
     * font's larger superior-figure glyphs. Expects HTML (not escaped).
     */
    public static function supDigits(string $html): string
    {
        return preg_replace_callback(
            '/[¹²³⁴⁵⁶⁷⁸⁹]+/u',
            fn (array $m) => '<sup>'.strtr($m[0], self::SUPERSCRIPTS).'</sup>',
            $html
        );
    }

    /**
     * A note number printed after a closing bracket belongs to the last
     * word inside it: "…ANNUALISED)¹" → "…ANNUALISED¹)".
     */
    public static function supInsideBracket(string $text): string
    {
        return preg_replace('/\)([¹²³⁴⁵⁶⁷⁸⁹]+)/u', '$1)', $text);
    }

    /**
     * Notes are stored as "<sup>1</sup> Returns in USD…". The number goes
     * in a fixed-width slot (and the space after it is dropped) so every
     * note's text starts on the same vertical as its wrapped lines.
     */
    public static function noteHang(string $html): string
    {
        $html = self::supDigits($html);

        return preg_replace(
            '/^\s*(<sup>[^<]*<\/sup>)\s*/u',
            '<span class="note-num">$1</span>',
            $html
        );
    }

    /**
     * Footer blurb: "…investment track record," ends the first line so
     * "the Foord team, …" starts the second (card 430). Plain text in.
     */
    public static function footerInfo(string $text): string
    {
        return preg_replace('/(track record,)\s+/u', '$1<br>', e($text));
    }
}
