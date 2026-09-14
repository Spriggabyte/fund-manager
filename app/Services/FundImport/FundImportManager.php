<?php

namespace App\Services\FundImport;

use App\Models\Fund;
use DateTimeImmutable;
use DateTimeZone;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

/**
 * Routes Foord Excel exports to the importer that understands them.
 *
 * Onboarding a new export type (e.g. the flexible fund's COST_REG28_GRAPH)
 * means adding one importer class and one line to the registry below —
 * unrecognised files are reported, never silently skipped.
 */
class FundImportManager
{
    /**
     * Registry in import order: the factsheet must run first because graph
     * importers only touch chart_data, while the factsheet rewrites the
     * fund's tables and scalars.
     *
     * @var list<AbstractExcelImporter>
     */
    private array $importers;

    public function __construct()
    {
        $this->importers = [
            new FactsheetImporter,
            new PriceGraphImporter,
            new InflationGraphImporter,
            new AlsiGraphImporter,
            new CostReg28GraphImporter,
            new RollingReturnGraphImporter,
            new LocalOverviewImporter,
            new GlobalOverviewImporter,
        ];
    }

    public function importerFor(string $filename): ?AbstractExcelImporter
    {
        foreach ($this->importers as $importer) {
            if ($importer->supports($filename)) {
                return $importer;
            }
        }

        return null;
    }

    /**
     * Import a single file; returns the importer label, or null when no
     * importer recognises the filename.
     */
    public function importFile(Fund $fund, string $filePath, ?string $originalName = null): ?string
    {
        $importer = $this->importerFor($originalName ?? $filePath);

        if (! $importer) {
            return null;
        }

        $importer->import($fund, $filePath);

        return $importer->label();
    }

    /**
     * Select the files in a data-feed folder that belong to one share class.
     *
     * A fund-code folder holds every class's exports side by side:
     *   810A_FACTSHEET.xlsx  810B2_FACTSHEET.xlsx  810_SA_INFLATION_GRAPH.xlsx
     *
     * A file belongs to a fund when the token between the fund code and the
     * first underscore is either empty (shared by every class, e.g. the
     * inflation graph) or equal to the fund's class code. The underscore
     * delimiter is what keeps 840B distinct from 840B3, and 877R from 877R1.
     *
     * Without a fund code or class code there is nothing to match on, so every
     * file is returned — preserving behaviour for ad-hoc directories such as
     * Funds/<fund>/Data.
     *
     * @param  list<string>  $files
     * @return list<string>
     */
    public function filesForClass(array $files, ?string $fundCode, ?string $classCode): array
    {
        if (! $fundCode || ! $classCode) {
            return $files;
        }

        $pattern = '/^'.preg_quote($fundCode, '/').'([A-Za-z][0-9]*)?_/';

        return array_values(array_filter($files, function (string $file) use ($pattern, $classCode): bool {
            if (! preg_match($pattern, basename($file), $matches)) {
                // Not named for this fund code at all — leave it to the
                // importers to recognise or report.
                return true;
            }

            $token = $matches[1] ?? '';

            return $token === '' || strcasecmp($token, $classCode) === 0;
        }));
    }

    /**
     * Collapse re-exports of the same file down to the newest one.
     *
     * The export tool writes a re-export of an existing file under a random
     * numeric suffix (LOCAL_OVERVIEW.xlsx, LOCAL_OVERVIEW_2049089080.xlsx,
     * LOCAL_OVERVIEW_1617171697.xlsx) and the feed keeps every copy. Glob
     * order puts the suffixed — often older — copy last, so left alone it
     * would be imported last and win. Files are grouped by stem (suffix
     * stripped) and the newest export time stamp is kept; files without a
     * suffixed twin pass through untouched, in their original order.
     *
     * @param  list<string>  $files
     * @return array{kept: list<string>, superseded: array<string, string>} superseded maps loser basename => winner basename
     */
    public function dedupeReExports(array $files): array
    {
        $groups = [];
        foreach ($files as $file) {
            $stem = preg_replace('/_\d+(?=\.xlsx$)/i', '', basename($file));
            $groups[$stem][] = $file;
        }

        $kept = [];
        $superseded = [];

        foreach ($files as $file) {
            $stem = preg_replace('/_\d+(?=\.xlsx$)/i', '', basename($file));
            $group = $groups[$stem];

            if (count($group) === 1) {
                $kept[] = $file;

                continue;
            }

            $winner = $this->newestExport($group);
            if ($file === $winner) {
                $kept[] = $file;
            } else {
                $superseded[basename($file)] = basename($winner);
            }
        }

        return ['kept' => $kept, 'superseded' => $superseded];
    }

    /**
     * @param  non-empty-list<string>  $group
     */
    private function newestExport(array $group): string
    {
        $winner = $group[0];
        $winnerAt = $this->exportedAt($winner);

        foreach (array_slice($group, 1) as $file) {
            $at = $this->exportedAt($file);
            // Strictly newer wins, so on a tie the earlier (plain-named)
            // file is kept.
            if ($at > $winnerAt) {
                $winner = $file;
                $winnerAt = $at;
            }
        }

        return $winner;
    }

    /**
     * When the export was produced: the Details sheet's "Time Stamp [ZA]"
     * cell (B7, "07 September 2026 14:06:14"), falling back to the file's
     * modification time when the envelope is missing or unreadable.
     */
    private function exportedAt(string $file): int
    {
        try {
            $reader = IOFactory::createReader('Xlsx');
            $reader->setLoadSheetsOnly('Details');
            $details = $reader->load($file)->getSheetByName('Details');
            $stamp = $details?->getCell('B7')->getValue();

            if (is_string($stamp) && $stamp !== '') {
                $parsed = DateTimeImmutable::createFromFormat('d F Y H:i:s', trim($stamp), new DateTimeZone('Africa/Johannesburg'));
                if ($parsed !== false) {
                    return $parsed->getTimestamp();
                }
            }
        } catch (Throwable) {
            // Fall through to mtime.
        }

        return (int) (@filemtime($file) ?: 0);
    }

    /**
     * Import every recognised .xlsx in a directory (registry order, so the
     * factsheet runs before the graphs).
     *
     * Only the fund's own share class is imported; other classes' exports are
     * reported separately from `skipped`, which means "no importer registered"
     * and is the signal to write a new importer. Suffixed re-exports of the
     * same file are collapsed to the newest copy; the losers are reported as
     * `superseded` (loser => winner) and never imported.
     *
     * @return array{imported: array<string, string>, skipped: list<string>, otherClasses: list<string>, superseded: array<string, string>}
     */
    public function importDirectory(Fund $fund, string $directory): array
    {
        $allFiles = glob(rtrim($directory, '/').'/*.[xX][lL][sS][xX]') ?: [];
        $classFiles = $this->filesForClass($allFiles, $fund->fund_code, $fund->class_code);

        $otherClasses = array_values(array_map(
            'basename',
            array_diff($allFiles, $classFiles)
        ));

        ['kept' => $files, 'superseded' => $superseded] = $this->dedupeReExports($classFiles);

        $imported = [];
        $skipped = [];

        foreach ($this->importers as $importer) {
            foreach ($files as $file) {
                if ($importer->supports($file)) {
                    $importer->import($fund, $file);
                    $imported[basename($file)] = $importer->label();
                }
            }
        }

        foreach ($files as $file) {
            if (! isset($imported[basename($file)])) {
                $skipped[] = basename($file);
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'otherClasses' => $otherClasses,
            'superseded' => $superseded,
        ];
    }

    /**
     * Snapshot the fund, import a directory, and persist when anything was
     * recognised. Shared by fund:import and the web data-feed import.
     *
     * @return array{imported: array<string, string>, skipped: list<string>, otherClasses: list<string>, superseded: array<string, string>, changed: list<string>}
     */
    public function importDirectoryWithSnapshot(Fund $fund, string $directory, string $summary): array
    {
        $fund->createRevision(null, null, null, $summary);

        $result = $this->importDirectory($fund, $directory);
        $result['changed'] = array_keys($fund->getDirty());

        if ($result['imported']) {
            $fund->save();
        }

        return $result;
    }
}
