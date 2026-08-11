<?php

namespace App\Services\FundImport;

use App\Models\Fund;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Base class for the per-file-type fund data importers.
 *
 * Foord's Excel exports all share the same envelope: a "Data Set" sheet with
 * the payload and a "Details" sheet with export metadata. Subclasses declare
 * which filenames they handle (supports) and how the Data Set maps onto the
 * fund (import). Fund-to-fund variation lives inside the importers via key
 * detection (e.g. AA_DOM_* vs AAOT_* vs AA_SHARE_*), never as per-fund classes.
 */
abstract class AbstractExcelImporter
{
    /**
     * Whether this importer handles the given file, based on its name
     * (e.g. "811A_FACTSHEET.xlsx" matches the factsheet importer).
     */
    abstract public function supports(string $filename): bool;

    abstract public function import(Fund $fund, string $filePath): void;

    /**
     * Human-readable name used in import summaries.
     */
    abstract public function label(): string;

    protected function filenameMatches(string $filename, string $needle): bool
    {
        return str_contains(strtoupper(basename($filename)), strtoupper($needle));
    }

    protected function loadDataSetSheet(string $filePath): Worksheet
    {
        $spreadsheet = IOFactory::load($filePath);

        return $spreadsheet->getSheetByName('Data Set') ?? $spreadsheet->getActiveSheet();
    }

    /**
     * Read a two-column sheet into a key => value map (factsheet layout).
     *
     * @return array<string, mixed>
     */
    protected function readKeyValuePairs(Worksheet $sheet): array
    {
        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $cells = [];
            foreach ($row->getCellIterator() as $cell) {
                $cells[] = $cell->getValue();
            }
            if (! empty($cells[0]) && isset($cells[1])) {
                $data[$cells[0]] = $cells[1];
            }
        }

        return $data;
    }

    protected function excelSerialToMonth(int|float|string $serial): string
    {
        // Excel serial 1 = 1900-01-01 (with the legacy 1900 leap-year bug).
        $unix = ((int) $serial - 25569) * 86400;

        return gmdate('Y-m', $unix);
    }

    protected function toNumber(mixed $value): float|int
    {
        if (is_numeric($value)) {
            $num = $value + 0;

            return is_float($num) ? $num : (int) $num;
        }

        return 0;
    }

    protected function dashToZero(string $value): float|int
    {
        return $value === '-' ? 0 : $this->toNumber($value);
    }

    protected function parseMonthYear(string $description): ?string
    {
        $months = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12',
        ];

        if (preg_match('/^(\w{3})\s+(\d{4})$/', $description, $m)) {
            $month = $months[$m[1]] ?? null;
            if ($month) {
                return $m[2].'-'.$month;
            }
        }

        return null;
    }
}
