<?php

namespace Tests\Feature;

use App\Jobs\GenerateFundPdfJob;
use App\Models\Fund;
use App\Models\FundPdfExport;
use App\Models\User;
use App\Services\PuppeteerPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FundPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_dispatches_job_and_records_pending_export(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show']);

        $response = $this->actingAs($user)->get(route('funds.pdf', $fund));

        $response->assertOk()->assertViewIs('funds.pdf-preparing');
        Queue::assertPushed(GenerateFundPdfJob::class);
        $this->assertDatabaseHas('fund_pdf_exports', [
            'fund_id' => $fund->id,
            'user_id' => $user->id,
            'status' => FundPdfExport::STATUS_PENDING,
        ]);
    }

    public function test_export_is_restricted_to_fund_owner(): void
    {
        Queue::fake();
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->for($owner)->create();

        $this->actingAs($other)->get(route('funds.pdf', $fund))->assertForbidden();

        Queue::assertNotPushed(GenerateFundPdfJob::class);
    }

    public function test_status_endpoint_returns_download_url_when_done(): void
    {
        $user = User::factory()->create();
        $export = FundPdfExport::factory()->for($user)->done()->create();

        $this->actingAs($user)->get(route('funds.pdf.status', $export))
            ->assertOk()
            ->assertJson(['status' => FundPdfExport::STATUS_DONE])
            ->assertJsonPath('download_url', route('funds.pdf.download', $export));
    }

    public function test_status_endpoint_is_forbidden_for_non_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $export = FundPdfExport::factory()->for($user)->create();

        $this->actingAs($other)->get(route('funds.pdf.status', $export))->assertForbidden();
    }

    public function test_download_streams_pdf_to_owner_when_done(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        Storage::disk('local')->put('pdfs/example.pdf', "%PDF-1.4\nx\n%%EOF");
        $export = FundPdfExport::factory()->for($user)->done('pdfs/example.pdf')->create();

        $this->actingAs($user)->get(route('funds.pdf.download', $export))
            ->assertOk()
            ->assertDownload();
    }

    public function test_download_is_not_found_when_export_not_done(): void
    {
        $user = User::factory()->create();
        $export = FundPdfExport::factory()->for($user)->create(); // pending

        $this->actingAs($user)->get(route('funds.pdf.download', $export))->assertNotFound();
    }

    public function test_download_is_forbidden_for_non_owner(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $other = User::factory()->create();
        Storage::disk('local')->put('pdfs/example.pdf', "%PDF-1.4\nx\n%%EOF");
        $export = FundPdfExport::factory()->for($user)->done('pdfs/example.pdf')->create();

        $this->actingAs($other)->get(route('funds.pdf.download', $export))->assertForbidden();
    }

    public function test_job_generates_and_stores_pdf_and_marks_export_done(): void
    {
        Storage::fake('local');
        $export = FundPdfExport::factory()->create(['status' => FundPdfExport::STATUS_PENDING]);

        $fakeService = new class extends PuppeteerPdfService
        {
            public function generatePdf(Fund $fund): string
            {
                $path = tempnam(sys_get_temp_dir(), 'pdf');
                file_put_contents($path, "%PDF-1.4\nmock\n%%EOF");

                return $path;
            }
        };

        (new GenerateFundPdfJob($export))->handle($fakeService);

        $export->refresh();
        $this->assertTrue($export->isDone());
        $this->assertNotNull($export->path);
        Storage::disk('local')->assertExists($export->path);
    }

    public function test_failed_job_marks_export_failed_with_context(): void
    {
        $export = FundPdfExport::factory()->create(['status' => FundPdfExport::STATUS_PROCESSING]);

        (new GenerateFundPdfJob($export))->failed(new \RuntimeException('chrome exploded'));

        $export->refresh();
        $this->assertTrue($export->isFailed());
        $this->assertSame('chrome exploded', $export->error);
    }
}
