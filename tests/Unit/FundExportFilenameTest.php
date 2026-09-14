<?php

namespace Tests\Unit;

use App\Models\Fund;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Fact-sheet exports are named like Foord's published documents:
 * "Foord Global Equity Feeder Fund Class A at 2026-08-31.pdf".
 */
class FundExportFilenameTest extends TestCase
{
    private function fund(string $name, ?string $classCode, ?string $date): Fund
    {
        $fund = new Fund;
        $fund->name = $name;
        $fund->class_code = $classCode;
        $fund->fund_date = $date;

        return $fund;
    }

    public function test_class_fund_follows_the_published_structure(): void
    {
        $fund = $this->fund('FOORD GLOBAL EQUITY FEEDER FUND — CLASS A', 'A', '31 August 2026');

        $this->assertSame('Foord Global Equity Feeder Fund Class A at 2026-08-31.pdf', $fund->exportFilename());
    }

    public function test_small_words_hyphens_and_brackets_keep_the_published_casing(): void
    {
        $this->assertSame(
            'Foord Flexible Fund of Funds Class B2 at 2026-07-31.pdf',
            $this->fund('FOORD FLEXIBLE FUND OF FUNDS — CLASS B2', 'B2', '31 July 2026')->exportFilename()
        );
        $this->assertSame(
            'Foord Asia ex-Japan Fund Class R1 at 2026-07-31.pdf',
            $this->fund('FOORD ASIA EX-JAPAN FUND — CLASS R1', 'R1', '31 July 2026')->exportFilename()
        );
        $this->assertSame(
            'Foord-Hassen Shariah Global Equity Fund Class R at 2026-07-31.pdf',
            $this->fund('FOORD-HASSEN SHARIAH GLOBAL EQUITY FUND — CLASS R', 'R', '31 July 2026')->exportFilename()
        );
        $this->assertSame(
            'Foord Global Equity Fund (Luxembourg) Class R at 2026-07-31.pdf',
            $this->fund('FOORD GLOBAL EQUITY FUND (LUXEMBOURG) — CLASS R', 'R', '31 July 2026')->exportFilename()
        );
        $this->assertSame(
            'Prescient Foord International Feeder Fund Class B2 at 2026-07-31.pdf',
            $this->fund('PRESCIENT FOORD INTERNATIONAL FEEDER FUND — CLASS B2', 'B2', '31 July 2026')->exportFilename()
        );
    }

    public function test_class_less_overview_sheets_drop_the_class_and_the_colon(): void
    {
        $this->assertSame(
            'Fund Overview - South Africa at 2026-08-31.pdf',
            $this->fund('FUND OVERVIEW: SOUTH AFRICA', null, '31 August 2026')->exportFilename()
        );
    }

    public function test_missing_or_unparseable_date_falls_back_to_the_given_date(): void
    {
        $fallback = Carbon::parse('2026-09-14');

        $this->assertSame(
            'Foord Bond Fund Class A at 2026-09-14.pdf',
            $this->fund('FOORD BOND FUND — CLASS A', 'A', null)->exportFilename($fallback)
        );
        $this->assertSame(
            'Foord Bond Fund Class A at 2026-09-14.pdf',
            $this->fund('FOORD BOND FUND — CLASS A', 'A', 'not a date')->exportFilename($fallback)
        );
    }

    public function test_class_code_wins_over_the_name_suffix_and_illegal_characters_are_dropped(): void
    {
        $this->assertSame(
            'Foord Equity Fund Class B3 at 2026-01-31.pdf',
            $this->fund('FOORD EQUITY FUND — CLASS B2', 'B3', '31 January 2026')->exportFilename()
        );
        $this->assertSame(
            'Foord Test Fund Class A at 2026-01-31.pdf',
            $this->fund('FOORD TEST/FUND? — CLASS A', 'A', '31 January 2026')->exportFilename()
        );
    }
}
