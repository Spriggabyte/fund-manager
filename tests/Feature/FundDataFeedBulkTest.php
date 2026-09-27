<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use App\Services\FundImport\FundDataSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class FundDataFeedBulkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('sftp');
        config(['filesystems.disks.sftp.host' => 'sftp.example.test']);
    }

    private function seedFactsheet(string $month, string $code, string $share): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Set');
        $sheet->fromArray([
            ['MONTH_END_DATE', '30 June 2026'],
            ['AA_SHARE_CURRENT', $share], ['AA_SHARE_PRIOR', '96'],
            ['AA_RES_CURRENT', '23'], ['AA_RES_PRIOR', '21'],
            ['AA_FIN_CURRENT', '16'], ['AA_FIN_PRIOR', '19'],
            ['AA_IND_CURRENT', '52'], ['AA_IND_PRIOR', '56'],
            ['AA_PROPERTY_CURRENT', '3'], ['AA_PROPERTY_PRIOR', '3'],
            ['AA_COMMOD_CURRENT', '-'], ['AA_COMMOD_PRIOR', '-'],
            ['AA_CASH_CURRENT', '6'], ['AA_CASH_PRIOR', '1'],
        ], null, 'A1', true);

        $target = FundDataSyncService::LOCAL_ROOT."/{$month}/{$code}/{$code}A_FACTSHEET.xlsx";
        Storage::disk('local')->makeDirectory(dirname($target));
        (new Xlsx($spreadsheet))->save(Storage::disk('local')->path($target));
    }

    public function test_download_mirrors_only_the_newest_remote_month(): void
    {
        Storage::disk('sftp')->put('2026-07/817/817A_FACTSHEET.xlsx', 'old');
        Storage::disk('sftp')->put('2026-08/817/817A_FACTSHEET.xlsx', 'new');

        $this->actingAs(User::factory()->create())
            ->post(route('funds.data-feed.download'))
            ->assertRedirect(route('funds.index'))
            ->assertSessionHas('success');

        Storage::disk('local')->assertExists('fund-data/2026-08/817/817A_FACTSHEET.xlsx');
        Storage::disk('local')->assertMissing('fund-data/2026-07/817/817A_FACTSHEET.xlsx');
    }

    public function test_download_reports_missing_sftp_config(): void
    {
        config(['filesystems.disks.sftp.host' => null]);

        $this->actingAs(User::factory()->create())
            ->post(route('funds.data-feed.download'))
            ->assertRedirect(route('funds.index'))
            ->assertSessionHas('error');
    }

    public function test_import_uses_each_funds_newest_month_and_skips_repeat_imports(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create([
            'user_id' => $user->id, 'template' => 'show-equity', 'fund_code' => '817', 'class_code' => 'A',
        ]);
        $uncoded = Fund::factory()->create(['user_id' => $user->id, 'fund_code' => null]);
        $this->seedFactsheet('2026-07', '817', '80');
        $this->seedFactsheet('2026-08', '817', '92');

        $this->actingAs($user)
            ->post(route('funds.data-feed.import'))
            ->assertRedirect(route('funds.index'))
            ->assertSessionHas('success');

        $fund->refresh();
        $this->assertSame('92', $fund->asset_allocation['rows'][0]['current']);
        $this->assertSame('Before data feed import (2026-08)', $fund->revisions()->first()->change_summary);
        $this->assertCount(0, $uncoded->revisions);

        // Second click: already imported for 2026-08, so no new revision.
        $this->actingAs($user)->post(route('funds.data-feed.import'));
        $this->assertCount(1, $fund->fresh()->revisions);
    }

    public function test_bulk_actions_require_authentication(): void
    {
        $this->post(route('funds.data-feed.download'))->assertRedirect(route('login'));
        $this->post(route('funds.data-feed.import'))->assertRedirect(route('login'));
    }
}
