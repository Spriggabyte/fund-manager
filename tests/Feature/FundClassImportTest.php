<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use App\Services\FundImport\FundDataSyncService;
use App\Services\FundImport\FundImportManager;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * A data-feed folder is per fund code but its files are per share class:
 * 810A_FACTSHEET.xlsx, 810B2_FACTSHEET.xlsx, 810_SA_INFLATION_GRAPH.xlsx.
 * Without class awareness every class imports into every fund, last one wins.
 */
class FundClassImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function manager(): FundImportManager
    {
        return app(FundImportManager::class);
    }

    /** @return list<string> */
    private function names(array $files): array
    {
        return array_map('basename', $files);
    }

    public function test_selects_own_class_files_plus_shared_files(): void
    {
        $files = [
            '810A_FACTSHEET.xlsx', '810A_PRICE_GRAPH.xlsx',
            '810B2_FACTSHEET.xlsx', '810B2_PRICE_GRAPH.xlsx',
            '810B3_FACTSHEET.xlsx', '810B3_PRICE_GRAPH.xlsx',
            '810_SA_INFLATION_GRAPH.xlsx',
        ];

        $this->assertSame(
            ['810A_FACTSHEET.xlsx', '810A_PRICE_GRAPH.xlsx', '810_SA_INFLATION_GRAPH.xlsx'],
            $this->names($this->manager()->filesForClass($files, '810', 'A'))
        );

        $this->assertSame(
            ['810B2_FACTSHEET.xlsx', '810B2_PRICE_GRAPH.xlsx', '810_SA_INFLATION_GRAPH.xlsx'],
            $this->names($this->manager()->filesForClass($files, '810', 'B2'))
        );
    }

    /**
     * The underscore delimiter is what keeps these apart — a naive "starts
     * with 840B" would hand class B the B3 exports as well.
     */
    public function test_does_not_confuse_class_b_with_class_b3(): void
    {
        $files = ['840B_FACTSHEET.xlsx', '840B3_FACTSHEET.xlsx'];

        $this->assertSame(['840B_FACTSHEET.xlsx'], $this->names($this->manager()->filesForClass($files, '840', 'B')));
        $this->assertSame(['840B3_FACTSHEET.xlsx'], $this->names($this->manager()->filesForClass($files, '840', 'B3')));
    }

    public function test_does_not_confuse_class_r_with_class_r1(): void
    {
        $files = ['877R_FACTSHEET.xlsx', '877R1_FACTSHEET.xlsx', '877B_FACTSHEET.xlsx'];

        $this->assertSame(['877R_FACTSHEET.xlsx'], $this->names($this->manager()->filesForClass($files, '877', 'R')));
        $this->assertSame(['877R1_FACTSHEET.xlsx'], $this->names($this->manager()->filesForClass($files, '877', 'R1')));
    }

    public function test_class_matching_is_case_insensitive(): void
    {
        $files = ['810b2_FACTSHEET.xlsx'];

        $this->assertCount(1, $this->manager()->filesForClass($files, '810', 'B2'));
    }

    public function test_without_a_class_code_every_file_is_kept(): void
    {
        $files = ['810A_FACTSHEET.xlsx', '810B2_FACTSHEET.xlsx'];

        $this->assertCount(2, $this->manager()->filesForClass($files, '810', null));
        $this->assertCount(2, $this->manager()->filesForClass($files, null, 'A'));
    }

    public function test_files_not_named_for_the_fund_code_are_kept(): void
    {
        // Ad-hoc directories (Funds/<fund>/Data) must keep working.
        $files = ['FACTSHEET.xlsx', 'SOME_OTHER_EXPORT.xlsx'];

        $this->assertCount(2, $this->manager()->filesForClass($files, '810', 'A'));
    }

    /**
     * The bug this feature exists to fix: importing the shared 810 folder into
     * the Class A fund used to leave it holding Class B3's figures.
     */
    public function test_importing_a_multi_class_folder_uses_only_this_funds_class(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create([
            'user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A',
        ]);

        $this->seedFactsheet('2026-07', '810', 'A', 'ZAE000042172');
        $this->seedFactsheet('2026-07', '810', 'B2', 'ZAE000164901');
        $this->seedFactsheet('2026-07', '810', 'B3', 'ZAE000164893');

        $result = $this->manager()->importDirectory(
            $fund,
            Storage::disk('local')->path(FundDataSyncService::LOCAL_ROOT.'/2026-07/810')
        );

        $this->assertSame('ZAE000042172', $fund->isin_number);
        $this->assertSame(['810A_FACTSHEET.xlsx'], array_keys($result['imported']));
        $this->assertEqualsCanonicalizing(
            ['810B2_FACTSHEET.xlsx', '810B3_FACTSHEET.xlsx'],
            $result['otherClasses']
        );
        // Other classes are not "skipped" — that list means no importer exists
        // and is the signal to write one.
        $this->assertSame([], $result['skipped']);
    }

    public function test_available_months_counts_only_this_classes_files(): void
    {
        $this->seedFactsheet('2026-07', '810', 'A');
        $this->seedFactsheet('2026-07', '810', 'B2');
        $this->seedFactsheet('2026-07', '810', 'B3');

        $service = app(FundDataSyncService::class);

        $this->assertSame(['2026-07' => 1], $service->availableMonths('810', 'A'));
        $this->assertSame(['2026-07' => 3], $service->availableMonths('810', null));
    }

    public function test_edit_page_reports_the_class_specific_file_count(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create([
            'user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A',
        ]);

        $this->seedFactsheet('2026-07', '810', 'A');
        $this->seedFactsheet('2026-07', '810', 'B2');

        $this->actingAs($user)
            ->get(route('funds.edit', $fund))
            ->assertOk()
            ->assertSee('1 file');
    }

    public function test_same_fund_code_is_allowed_across_different_classes(): void
    {
        $user = User::factory()->create();

        Fund::factory()->create(['user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A']);
        Fund::factory()->create(['user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'B2']);

        $this->assertSame(2, Fund::where('fund_code', '810')->count());
    }

    public function test_the_same_code_and_class_pair_is_rejected(): void
    {
        $user = User::factory()->create();
        Fund::factory()->create(['user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A']);

        $this->expectException(QueryException::class);
        Fund::factory()->create(['user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A']);
    }

    public function test_add_class_command_clones_the_source_fund(): void
    {
        $user = User::factory()->create();
        $source = Fund::factory()->create([
            'user_id' => $user->id,
            'name' => 'FOORD BALANCED FUND — CLASS A',
            'fund_code' => '810',
            'class_code' => 'A',
            'isin_number' => 'ZAE000042172',
            'domicile' => 'South Africa',
        ]);

        $this->artisan('fund:add-class', ['source' => $source->id, 'class' => 'B3'])
            ->assertExitCode(0);

        $new = Fund::where('class_code', 'B3')->firstOrFail();
        $this->assertSame('FOORD BALANCED FUND — CLASS B3', $new->name);
        $this->assertSame('810', $new->fund_code);
        // Static content is inherited...
        $this->assertSame('South Africa', $new->domicile);
        // ...but per-class values must come from that class's own export.
        $this->assertNull($new->isin_number);
    }

    public function test_add_class_command_refuses_an_existing_class(): void
    {
        $user = User::factory()->create();
        $source = Fund::factory()->create([
            'user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A',
        ]);

        $this->artisan('fund:add-class', ['source' => $source->id, 'class' => 'A'])
            ->expectsOutputToContain('already exists')
            ->assertExitCode(1);

        $this->assertSame(1, Fund::where('fund_code', '810')->count());
    }

    public function test_add_class_command_rejects_a_malformed_class_code(): void
    {
        $user = User::factory()->create();
        $source = Fund::factory()->create([
            'user_id' => $user->id, 'fund_code' => '810', 'class_code' => 'A',
        ]);

        $this->artisan('fund:add-class', ['source' => $source->id, 'class' => 'B-2'])
            ->expectsOutputToContain('Invalid class code')
            ->assertExitCode(1);
    }

    public function test_add_class_command_can_import_a_month_immediately(): void
    {
        $user = User::factory()->create();
        $source = Fund::factory()->create([
            'user_id' => $user->id,
            'name' => 'FOORD BALANCED FUND — CLASS A',
            'fund_code' => '810',
            'class_code' => 'A',
        ]);

        $this->seedFactsheet('2026-07', '810', 'A', 'ZAE000042172');
        $this->seedFactsheet('2026-07', '810', 'B3', 'ZAE000164893');

        $this->artisan('fund:add-class', [
            'source' => $source->id, 'class' => 'B3', '--import' => '2026-07',
        ])->assertExitCode(0);

        $this->assertSame('ZAE000164893', Fund::where('class_code', 'B3')->firstOrFail()->isin_number);
    }

    /**
     * Minimal factsheet for one class, written into the downloaded month folder.
     */
    private function seedFactsheet(string $month, string $code, string $class, string $isin = 'ZAE000000000'): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Set');
        $sheet->fromArray([
            ['MONTH_END_DATE', '31 July 2026'],
            ['CLASS', $class],
            ['ISIN', $isin],
            ['AA_SHARE_CURRENT', '92'], ['AA_SHARE_PRIOR', '96'],
            ['AA_CASH_CURRENT', '6'], ['AA_CASH_PRIOR', '1'],
        ], null, 'A1', true);

        $target = FundDataSyncService::LOCAL_ROOT."/{$month}/{$code}/{$code}{$class}_FACTSHEET.xlsx";
        Storage::disk('local')->makeDirectory(dirname($target));
        (new Xlsx($spreadsheet))->save(Storage::disk('local')->path($target));
    }
}
