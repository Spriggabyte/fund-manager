<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\PuppeteerPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PuppeteerPdfServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_pdf_produces_a_valid_non_empty_pdf(): void
    {
        $fund = Fund::factory()->create();

        // Override the render boundary so orchestration is validated without a
        // real Chrome — the stub writes valid PDF magic bytes to the target.
        $service = new class extends PuppeteerPdfService
        {
            protected function executePuppeteerScript(string $script, string $tempPdfPath, Fund $fund): void
            {
                file_put_contents($tempPdfPath, "%PDF-1.4\n%mock fact sheet\n%%EOF");
            }
        };

        $path = $service->generatePdf($fund);

        $this->assertFileExists($path);
        $this->assertGreaterThan(0, filesize($path));
        $this->assertSame('%PDF-', substr((string) file_get_contents($path), 0, 5));

        @unlink($path);
    }

    public function test_generate_pdf_throws_when_no_file_is_produced(): void
    {
        $fund = Fund::factory()->create();

        $service = new class extends PuppeteerPdfService
        {
            protected function executePuppeteerScript(string $script, string $tempPdfPath, Fund $fund): void
            {
                // Simulate a render that produced no output file.
            }
        };

        $this->expectException(\Exception::class);

        $service->generatePdf($fund);
    }
}
