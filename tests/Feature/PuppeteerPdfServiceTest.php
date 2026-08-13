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

    public function test_render_environment_gives_chrome_a_writable_home(): void
    {
        // proc_open replaces the whole environment rather than adding to it, so
        // anything Chrome needs has to be listed explicitly. Without HOME it
        // resolves the profile directory to "/" and dies with
        // "mkdir: cannot create directory '/.local': Permission denied" for any
        // user whose home is not writable (www-data's is /var/www, root-owned).
        $service = new class extends PuppeteerPdfService
        {
            public function exposedRenderEnvironment(): array
            {
                return $this->renderEnvironment();
            }
        };

        $env = $service->exposedRenderEnvironment();

        $this->assertArrayHasKey('HOME', $env);
        $this->assertDirectoryExists($env['HOME']);
        $this->assertDirectoryIsWritable($env['HOME']);

        // Chrome writes its per-user config and cache under these; leaving them
        // unset sends both back to HOME's default, which is what broke.
        $this->assertArrayHasKey('XDG_CONFIG_HOME', $env);
        $this->assertArrayHasKey('XDG_CACHE_HOME', $env);

        // The existing contract must survive: node module resolution, and a
        // PATH for the shell that proc_open spawns to find the node binary.
        $this->assertSame(base_path('node_modules'), $env['NODE_PATH']);
        $this->assertNotEmpty($env['PATH']);
    }

    public function test_puppeteer_browser_cache_is_not_moved_by_the_home_override(): void
    {
        // When no PUPPETEER_EXECUTABLE_PATH is configured the service falls back
        // to the "puppeteer" package, which resolves the Chrome it downloaded
        // relative to HOME. Repointing HOME at storage would send it looking in
        // an empty directory ("Could not find Chrome"), so the cache location
        // has to be pinned independently.
        $service = new class extends PuppeteerPdfService
        {
            public function exposedRenderEnvironment(): array
            {
                return $this->renderEnvironment();
            }
        };

        $env = $service->exposedRenderEnvironment();

        $this->assertArrayHasKey('PUPPETEER_CACHE_DIR', $env);
        $this->assertStringStartsNotWith($env['HOME'], $env['PUPPETEER_CACHE_DIR']);
    }

    public function test_each_render_gets_an_isolated_chrome_profile(): void
    {
        // A single shared profile would let two concurrent renders collide on
        // Chrome's profile lock once Horizon runs more than one process.
        $service = new class extends PuppeteerPdfService
        {
            public function exposedProfileDir(): string
            {
                return $this->chromeProfileDir();
            }

            public function exposedScript(string $profileDir): string
            {
                return $this->generatePuppeteerScript('https://example.test/x', '/tmp/out.pdf', $profileDir);
            }
        };

        $first = $service->exposedProfileDir();
        $second = $service->exposedProfileDir();

        $this->assertNotSame($first, $second);
        $this->assertDirectoryExists($first);
        $this->assertStringContainsString("--user-data-dir={$first}", $service->exposedScript($first));

        @rmdir($first);
        @rmdir($second);
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
