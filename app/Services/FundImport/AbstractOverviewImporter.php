<?php

namespace App\Services\FundImport;

use App\Models\Fund;
use Carbon\Carbon;
use Throwable;

/**
 * Shared machinery for the two-page multi-fund overview exports
 * (LOCAL_OVERVIEW.xlsx → FUND OVERVIEW: SOUTH AFRICA, GLOBAL_OVERVIEW.xlsx →
 * FUND OVERVIEW: GLOBAL).
 *
 * Both sheets carry a MONTH_END_DATE, per-fund {code}_FOORD_{n}Y_TO_D
 * performance keys, {code}_AA_* allocation cells, an ESAOT_RANK_n sector
 * list and quarterly WORLD_ and SA_ / ASIA_ synopsis and strategy bullets.
 * Subclasses own the table skeletons (which funds, which rows, which
 * labels); this class owns the cell parsing, the period map, the sector
 * list merge and the bullet blocks so the two importers cannot drift.
 */
abstract class AbstractOverviewImporter extends AbstractExcelImporter
{
    /** @var array<string, int> column key => years */
    protected const PERIODS = ['20yrs' => 20, '15yrs' => 15, '10yrs' => 10, '5yrs' => 5, '3yrs' => 3, '1yr' => 1];

    /** @var list<string> */
    protected const PERIOD_HEADERS = ['20 YEARS', '15 YEARS', '10 YEARS', '5 YEARS', '3 YEARS', '1 YEAR'];

    /** Upper bound on ESAOT_RANK_n / *_RANK_n scans. */
    protected const MAX_RANKS = 50;

    protected const MAX_BULLETS = 10;

    protected const ROUNDING_NOTE = 'Note: Totals may not cast perfectly due to rounding';

    /**
     * Last completed quarter for a month-end date: Jul/Aug/Sep → Q2, and
     * Jan/Feb/Mar roll back to Q4 of the previous year. Null when the date
     * cannot be parsed, so the caller keeps whatever quarter is stored.
     */
    public static function quarterLabelFor(string $monthEndDate): ?string
    {
        if (trim($monthEndDate) === '') {
            return null;
        }

        try {
            $date = Carbon::parse($monthEndDate);
        } catch (Throwable) {
            return null;
        }

        $q = intdiv($date->month - 1, 3);

        return $q === 0 ? 'Q4 '.($date->year - 1) : "Q{$q} {$date->year}";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapDate(Fund $fund, array $data): void
    {
        if (isset($data['MONTH_END_DATE']) && (string) $data['MONTH_END_DATE'] !== '') {
            $fund->fund_date = (string) $data['MONTH_END_DATE'];
        }
    }

    // ── Cells ────────────────────────────────────────────────────────────

    /**
     * One period => value map. Cells the feed leaves as "—", "-", blank or
     * anything non-numeric are absent from the map rather than placeholders.
     *
     * @param  array<string, mixed>  $data
     * @param  callable(int): string  $key
     * @return array<string, float>
     */
    protected function performanceValues(array $data, callable $key): array
    {
        $values = [];
        foreach (static::PERIODS as $column => $years) {
            $value = $this->numeric($data[$key($years)] ?? null);
            if ($value !== null) {
                $values[$column] = round($value, 1);
            }
        }

        return $values;
    }

    /**
     * A feed cell as a float, or null when it holds no number ("—", "-",
     * blank, ERR…). Thousands separators are not expected in this export.
     */
    protected function numeric(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * "-", blank and absent read as 0; integral values are ints so the JSON
     * carries 42 rather than 42.0.
     */
    protected function chartNumber(mixed $value): int|float
    {
        $number = $this->numeric($value) ?? 0.0;

        return floor($number) === $number ? (int) $number : $number;
    }

    /**
     * An allocation cell rounded to one decimal; the feed's "-" marker is
     * an explicit 0.0, anything non-numeric is null (absent).
     */
    protected function allocationValue(mixed $value): ?float
    {
        if ($value !== null && trim((string) $value) === '-') {
            return 0.0;
        }

        $number = $this->numeric($value);

        return $number === null ? null : round($number, 1);
    }

    /**
     * The shared guard plus this export's own failure mode: a broken link
     * prints "ERR>>>0>>>LINK%", which carries a digit and would otherwise
     * pass as a statistic.
     */
    protected function usableStat(mixed $value): bool
    {
        return $this->isUsableStat($value) && ! str_starts_with(trim((string) $value), 'ERR');
    }

    protected function formatStat(string $value, bool $isYield): string
    {
        $value = trim($value);

        if ($value === '-' || $isYield) {
            return $value;
        }

        return number_format((float) $value, 2);
    }

    // ── Sector list ──────────────────────────────────────────────────────

    /**
     * {prefix}{n}_{ITEM,CURRENT,BENCHMARK} → [{name, fund, benchmark}] for
     * as many ranks as the export carries; blank ITEM ranks are skipped. A
     * BENCHMARK cell the feed omits entirely keeps the stored figure for the
     * same (normalised) sector name, else 0.
     *
     * @param  array<string, mixed>  $data
     * @param  list<array<string, mixed>>  $storedSectors
     * @return list<array{name: string, fund: int|float, benchmark: int|float}>
     */
    protected function mapSectorList(array $data, string $prefix, array $storedSectors): array
    {
        $storedByName = collect($storedSectors)->keyBy(fn (array $sector): string => (string) ($sector['name'] ?? ''));

        $sectors = [];
        for ($n = 1; $n <= static::MAX_RANKS; $n++) {
            if (! array_key_exists("{$prefix}{$n}_ITEM", $data)) {
                break;
            }

            $name = $this->sectorName((string) $data["{$prefix}{$n}_ITEM"]);
            if ($name === '') {
                continue;
            }

            $benchmarkKey = "{$prefix}{$n}_BENCHMARK";
            $benchmark = array_key_exists($benchmarkKey, $data)
                ? $this->chartNumber($data[$benchmarkKey])
                : ($storedByName->get($name)['benchmark'] ?? 0);

            $sectors[] = [
                'name' => $name,
                'fund' => $this->chartNumber($data["{$prefix}{$n}_CURRENT"] ?? null),
                'benchmark' => $benchmark,
            ];
        }

        return $sectors;
    }

    /**
     * The printed name for a feed ITEM cell. Subclasses layer sheet-specific
     * renames over the shared normalisation.
     */
    protected function sectorName(string $raw): string
    {
        return $this->normaliseSectorName($raw);
    }

    /**
     * "Consumer/services" → "Consumer / services"; an all-caps item
     * ("PROPERTY") → "Property".
     */
    protected function normaliseSectorName(string $name): string
    {
        $name = trim((string) preg_replace('~\s*/\s*~', ' / ', trim($name)));

        if ($name !== '' && mb_strtoupper($name) === $name) {
            $name = ucfirst(mb_strtolower($name));
        }

        return $name;
    }

    // ── Synopsis / strategy ──────────────────────────────────────────────

    /**
     * WORLD_SYN_n / {REGION}_SYN_n → page2_content['synopsis'], WORLD_STRAT_n
     * / {REGION}_STRAT_n → ['strategy']. Both carry the last completed
     * quarter of MONTH_END_DATE. The rounding note and QR block get their
     * defaults here too; anything already stored wins.
     *
     * @param  array<string, mixed>  $data
     * @param  array{0: string, 1: string}  $secondColumn  [feed prefix, stored key] — ['SA', 'southAfrica'] or ['ASIA', 'asia']
     * @param  array{heading: string, text: string, url: string, image: string}  $qr
     */
    protected function mapSynopsisAndStrategy(
        Fund $fund,
        array $data,
        array $secondColumn,
        array $qr,
        string $roundingNote = self::ROUNDING_NOTE,
    ): void {
        [$feedPrefix, $storedKey] = $secondColumn;
        $page2 = $fund->page2_content ?? [];
        $quarter = isset($data['MONTH_END_DATE']) ? static::quarterLabelFor((string) $data['MONTH_END_DATE']) : null;

        foreach (['synopsis' => ['MARKET SYNOPSIS', 'SYN'], 'strategy' => ['STRATEGY', 'STRAT']] as $key => [$title, $suffix]) {
            $stored = $page2[$key] ?? [];

            $block = [
                'title' => $stored['title'] ?? $title,
                'quarter' => $quarter ?? $stored['quarter'] ?? null,
                'world' => $this->bullets($data, "WORLD_{$suffix}_") ?? $stored['world'] ?? [],
                $storedKey => $this->bullets($data, "{$feedPrefix}_{$suffix}_") ?? $stored[$storedKey] ?? [],
            ];
            if ($block['quarter'] === null) {
                unset($block['quarter']);
            }

            $page2[$key] = $block;
        }

        $page2['roundingNote'] = $page2['roundingNote'] ?? $roundingNote;
        $page2['qr'] = ($page2['qr'] ?? []) + $qr;

        $fund->page2_content = $page2;
    }

    /**
     * Bullets 1..n, stopping at the first blank. Null when the export has no
     * such list at all, so the stored bullets are kept.
     *
     * @param  array<string, mixed>  $data
     * @return ?list<string>
     */
    protected function bullets(array $data, string $prefix): ?array
    {
        if (! array_key_exists("{$prefix}1", $data)) {
            return null;
        }

        $bullets = [];
        for ($n = 1; $n <= static::MAX_BULLETS; $n++) {
            $text = trim((string) ($data["{$prefix}{$n}"] ?? ''));
            if ($text === '') {
                break;
            }
            $bullets[] = $text;
        }

        return $bullets;
    }
}
