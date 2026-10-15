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
     * Straight quotes become typographic ones ("Master Fund" → “Master
     * Fund”, fund's → fund’s) to match the curly “Equity Trustees” the
     * published prose already uses (card 445). A quote opens after the
     * start, whitespace, an opening bracket or a dash; otherwise it closes.
     * Plain text in, plain text out.
     */
    public static function smartQuotes(string $text): string
    {
        $text = preg_replace('/(^|[\s(\[{—–])"/u', '$1“', $text);
        $text = str_replace('"', '”', $text);
        $text = preg_replace("/(^|[\\s(\\[{—–])'/u", '$1‘', $text);

        return str_replace("'", '’', $text);
    }

    /**
     * Clickable links in copy, all styled `.ref-link` (the copy's own type
     * setting, card 460): "[link text](https://…)" links the bracketed
     * words, and bare www./http(s) URLs and email addresses link themselves
     * (a sentence's closing full stop stays outside the link). Chrome keeps
     * the anchors as link annotations in the exported PDF. Expects escaped
     * HTML, e.g. e($text).
     */
    public static function links(string $html): string
    {
        $anchor = fn (string $href, string $text) => '<a class="ref-link" href="'.$href.'" target="_blank" rel="noopener">'.$text.'</a>';
        $href = fn (string $url) => str_starts_with($url, 'www.') ? 'https://'.$url : $url;

        return preg_replace_callback(
            '/\[([^\]]+)\]\(((?:https?:\/\/|www\.|mailto:)[^\s)]+)\)|((?:www\.|https?:\/\/)[^\s,)<]+)|([\w.+-]+@[\w.-]+\.\w+)/',
            function (array $m) use ($anchor, $href) {
                if ($m[1] !== null) {
                    return $anchor($href($m[2]), $m[1]);
                }
                if ($m[3] !== null) {
                    preg_match('/^(.*?)([.;:!?]*)$/', $m[3], $parts);

                    return $anchor($href($parts[1]), $parts[1]).$parts[2];
                }

                return $anchor('mailto:'.$m[4], $m[4]);
            },
            $html,
            flags: PREG_UNMATCHED_AS_NULL
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
