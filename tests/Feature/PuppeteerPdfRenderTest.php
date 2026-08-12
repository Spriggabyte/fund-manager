<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\PuppeteerPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Real headless-Chrome render. Skipped unless RUN_PUPPETEER_TESTS=1 and Chrome
 * is resolvable, so the default suite stays fast and deterministic. Requires
 * APP_URL to point at a running server that can serve the signed internal
 * render route (e.g. `php artisan serve` on the same host).
 */
#[Group('puppeteer')]
class PuppeteerPdfRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! getenv('RUN_PUPPETEER_TESTS')) {
            $this->markTestSkipped('Set RUN_PUPPETEER_TESTS=1 to run the real Puppeteer render.');
        }

        $configured = config('puppeteer.chrome_path') ?: getenv('PUPPETEER_EXECUTABLE_PATH');
        $chromeAvailable = $configured
            ? is_executable($configured)
            : str_contains((string) @shell_exec('node -e "try{console.log(require(\'puppeteer\').executablePath())}catch(e){}" 2>/dev/null'), '/');

        if (! $chromeAvailable) {
            $this->markTestSkipped('Chrome/Chromium is not available for Puppeteer.');
        }
    }

    public function test_it_renders_a_real_non_empty_pdf(): void
    {
        $fund = Fund::factory()->representative()->create();

        $path = app(PuppeteerPdfService::class)->generatePdf($fund);

        $this->assertFileExists($path);
        $this->assertGreaterThan(1000, filesize($path));
        $this->assertSame('%PDF-', substr((string) file_get_contents($path), 0, 5));

        @unlink($path);
    }
}
