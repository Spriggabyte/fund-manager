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

class FundImportMonthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function equityFund(User $user, ?string $code = '817', ?string $classCode = 'A'): Fund
    {
        return Fund::factory()->create([
            'user_id' => $user->id,
            'template' => 'show-equity',
            'fund_code' => $code,
            'class_code' => $classCode,
        ]);
    }

    /**
     * Drop a minimal factsheet xlsx into the downloaded month folder for a fund.
     */
    private function seedFactsheet(string $month, string $code): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Set');
        $sheet->fromArray([
            ['MONTH_END_DATE', '30 June 2026'],
            ['AA_SHARE_CURRENT', '92'], ['AA_SHARE_PRIOR', '96'],
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

    public function test_imports_downloaded_month_into_fund_with_revision(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);
        $this->seedFactsheet('2026-06', '817');

        $response = $this->actingAs($user)
            ->post(route('funds.import-month', [$fund, '2026-06']));

        $response->assertRedirect(route('funds.edit', $fund))
            ->assertSessionHas('success');

        $fund->refresh();
        $this->assertSame('92', $fund->asset_allocation['rows'][0]['current']);
        $this->assertCount(1, $fund->revisions);
        $this->assertStringContainsString('2026-06', $fund->revisions->first()->change_summary);
    }

    public function test_allows_importing_into_a_colleagues_fund(): void
    {
        $creator = User::factory()->create();
        $fund = $this->equityFund($creator);
        $this->seedFactsheet('2026-06', '817');

        $this->actingAs(User::factory()->create())
            ->post(route('funds.import-month', [$fund, '2026-06']))
            ->assertRedirect(route('funds.edit', $fund));
    }

    public function test_import_requires_authentication(): void
    {
        $fund = $this->equityFund(User::factory()->create());
        $this->seedFactsheet('2026-06', '817');

        $this->post(route('funds.import-month', [$fund, '2026-06']))
            ->assertRedirect(route('login'));
    }

    public function test_errors_when_month_not_downloaded(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);

        $this->actingAs($user)
            ->post(route('funds.import-month', [$fund, '2026-06']))
            ->assertRedirect(route('funds.edit', $fund))
            ->assertSessionHas('error');

        $this->assertCount(0, $fund->revisions);
    }

    public function test_errors_when_fund_has_no_fund_code(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user, null);

        $this->actingAs($user)
            ->post(route('funds.import-month', [$fund, '2026-06']))
            ->assertRedirect(route('funds.edit', $fund))
            ->assertSessionHas('error');
    }

    public function test_rejects_invalid_month_format(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);

        $this->actingAs($user)
            ->post("/funds/{$fund->id}/import-data/not-a-month")
            ->assertNotFound();
    }

    public function test_edit_page_lists_available_months(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);
        $this->seedFactsheet('2026-05', '817');
        $this->seedFactsheet('2026-06', '817');
        $this->seedFactsheet('2026-06', '810'); // another fund's folder — ignored

        $this->actingAs($user)
            ->get(route('funds.edit', $fund))
            ->assertOk()
            ->assertSeeInOrder(['2026-06', '2026-05']);
    }

    /**
     * A feed folder holds every share class, so importing without a class code
     * would pull in another class's figures — the card asks for one instead.
     */
    public function test_edit_page_asks_for_a_class_code_before_listing_months(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user, '817', null);
        $this->seedFactsheet('2026-06', '817');

        $this->actingAs($user)
            ->get(route('funds.edit', $fund))
            ->assertOk()
            ->assertSee('Class Code')
            ->assertDontSee('Import 2026-06');
    }
}
