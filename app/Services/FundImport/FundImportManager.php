<?php

namespace App\Services\FundImport;

use App\Models\Fund;

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
     * Import every recognised .xlsx in a directory (registry order, so the
     * factsheet runs before the graphs).
     *
     * Only the fund's own share class is imported; other classes' exports are
     * reported separately from `skipped`, which means "no importer registered"
     * and is the signal to write a new importer.
     *
     * @return array{imported: array<string, string>, skipped: list<string>, otherClasses: list<string>}
     */
    public function importDirectory(Fund $fund, string $directory): array
    {
        $allFiles = glob(rtrim($directory, '/').'/*.[xX][lL][sS][xX]') ?: [];
        $files = $this->filesForClass($allFiles, $fund->fund_code, $fund->class_code);

        $otherClasses = array_values(array_map(
            'basename',
            array_diff($allFiles, $files)
        ));

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

        return ['imported' => $imported, 'skipped' => $skipped, 'otherClasses' => $otherClasses];
    }

    /**
     * Snapshot the fund, import a directory, and persist when anything was
     * recognised. Shared by fund:import and the web data-feed import.
     *
     * @return array{imported: array<string, string>, skipped: list<string>, otherClasses: list<string>, changed: list<string>}
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
