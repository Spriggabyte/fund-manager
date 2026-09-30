<?php

namespace Tests\Unit;

use App\Support\FactsheetText;
use PHPUnit\Framework\TestCase;

class FactsheetTextTest extends TestCase
{
    public function test_unicode_superscripts_become_sup_markup(): void
    {
        $this->assertSame('CASH<br>VALUE<sup>2</sup>', FactsheetText::supDigits('CASH<br>VALUE²'));
        $this->assertSame('Fund highest<sup>38</sup>', FactsheetText::supDigits('Fund highest³⁸'));
        $this->assertSame('SINCE<br>INCEPTION', FactsheetText::supDigits('SINCE<br>INCEPTION'));
    }

    public function test_note_number_after_a_bracket_moves_inside_it(): void
    {
        $this->assertSame(
            'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)',
            FactsheetText::supInsideBracket('PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED)¹')
        );
    }

    public function test_note_number_goes_in_a_fixed_slot_without_the_following_space(): void
    {
        $this->assertSame(
            '<span class="note-num"><sup>1</sup></span>Returns in USD unless otherwise stated.',
            FactsheetText::noteHang('<sup>1</sup> Returns in USD unless otherwise stated.')
        );
        $this->assertSame(
            '<span class="note-num"><sup>1</sup></span>The notional GAVs illustrated.',
            FactsheetText::noteHang('¹ The notional GAVs illustrated.')
        );
        // Unnumbered notes are left as they are.
        $this->assertSame(
            'Totals may not cast perfectly due to rounding.',
            FactsheetText::noteHang('Totals may not cast perfectly due to rounding.')
        );
    }

    public function test_footer_breaks_after_track_record_and_escapes_the_text(): void
    {
        $this->assertSame(
            'Please visit our website for more information regarding our investment track record,<br>the Foord team &amp; more.',
            FactsheetText::footerInfo('Please visit our website for more information regarding our investment track record, the Foord team & more.')
        );
    }
}
