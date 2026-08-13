<?php

namespace App\Services;

use App\Models\Fund;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class PuppeteerPdfService
{
    protected ?string $chromePath;

    protected ?string $nodePath;

    protected ?string $cacheDir;

    protected int $timeout;

    protected int $navTimeout;

    public function __construct()
    {
        $this->chromePath = config('puppeteer.chrome_path');
        $this->nodePath = config('puppeteer.node_path');
        $this->cacheDir = config('puppeteer.cache_dir');
        $this->timeout = (int) config('puppeteer.timeout', 180);
        $this->navTimeout = (int) config('puppeteer.nav_timeout', 60000);
    }

    /**
     * Generate a PDF from the fund show page using Puppeteer.
     */
    public function generatePdf(Fund $fund): string
    {
        $filename = 'fund-'.$fund->id.'-'.now()->format('Y-m-d-His').'.pdf';
        $tempDir = storage_path('app/temp');
        $tempPdfPath = $tempDir.'/'.$filename;

        // Ensure temp directory exists
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $url = $this->pdfViewUrl($fund);

        // Create the Puppeteer script (no auth cookie needed for internal route)
        $profileDir = $this->chromeProfileDir();
        $script = $this->generatePuppeteerScript($url, $tempPdfPath, $profileDir);

        // Execute the script
        try {
            $this->executePuppeteerScript($script, $tempPdfPath, $fund);
        } finally {
            $this->deleteDirectory($profileDir);
        }

        // Verify PDF was created
        if (! file_exists($tempPdfPath)) {
            throw new \Exception('PDF file was not generated');
        }

        return $tempPdfPath;
    }

    /**
     * Build the URL Puppeteer navigates to in order to render the fact sheet.
     *
     * A short-lived signed URL keeps the unauthenticated render route private:
     * only this service can produce a valid link, and it expires in minutes.
     * The host comes from config('app.url'), which the worker must be able to
     * reach for the signature to validate.
     */
    protected function pdfViewUrl(Fund $fund): string
    {
        return URL::temporarySignedRoute(
            'funds.internal.pdf-view',
            now()->addMinutes(5),
            ['fund' => $fund->id]
        );
    }

    /**
     * Generate the Puppeteer JavaScript code.
     *
     * When a Chrome path is configured we drive that binary via puppeteer-core;
     * otherwise we fall back to the full "puppeteer" package, which locates the
     * Chromium it provisioned itself.
     */
    protected function generatePuppeteerScript(string $url, string $pdfPath, string $profileDir): string
    {
        if ($this->chromePath) {
            $require = "const puppeteer = require('puppeteer-core');";
            $executablePath = "executablePath: '".addslashes($this->chromePath)."',";
        } else {
            $require = "const puppeteer = require('puppeteer');";
            $executablePath = '';
        }

        return "
{$require}

(async () => {
    const browser = await puppeteer.launch({
        headless: true,
        {$executablePath}
        timeout: {$this->navTimeout},
        args: [
            '--user-data-dir=".addslashes($profileDir)."',
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-web-security',
            '--allow-running-insecure-content',
            '--disable-features=VizDisplayCompositor',
            '--disable-extensions',
            '--disable-default-apps'
        ]
    });

    try {
        const page = await browser.newPage();

        // Set viewport to match A4 dimensions at 96 DPI
        // A4: 210mm x 297mm = 794px x 1123px at 96 DPI
        await page.setViewport({
            width: 794,
            height: 1123,
            deviceScaleFactor: 2
        });

        console.log('Navigating to: ".addslashes($url)."');

        // Navigate to the page and wait for all resources
        await page.goto('".addslashes($url)."', {
            waitUntil: ['networkidle0', 'load', 'domcontentloaded'],
            timeout: {$this->navTimeout}
        });

        console.log('Page loaded, waiting for fonts to load...');

        // Wait for web fonts to load
        await page.evaluate(async () => {
            await document.fonts.ready;
        });

        console.log('Fonts loaded, waiting for charts...');

        // Wait for Highcharts and charts to render
        try {
            const hasCharts = await page.evaluate(() => {
                return document.querySelector('#inflationChart') !== null ||
                       document.querySelector('#strategyChart') !== null ||
                       document.querySelector('#portfolioChart') !== null ||
                       document.querySelector('#performanceChart') !== null;
            });

            if (hasCharts) {
                console.log('Chart containers found, waiting for chart library...');

                // Highcharts templates (balanced/flexible/equity) or Chart.js
                // (the international template's canvas chart) — wait for
                // whichever library the page loads.
                await page.waitForFunction(() => {
                    return typeof window.Highcharts !== 'undefined' ||
                           typeof window.Chart !== 'undefined';
                }, { timeout: 15000 });

                console.log('Highcharts loaded, waiting for charts to render...');

                // Settle wait for charts to initialise (page.waitForTimeout was
                // removed in Puppeteer v22+, so use a plain timer instead).
                await new Promise(resolve => setTimeout(resolve, 2000));

                // Additional wait for charts to fully render
                await new Promise(resolve => setTimeout(resolve, 1500));

                const chartCount = await page.evaluate(() => {
                    return document.querySelectorAll('canvas').length;
                });

                console.log(`Found \${chartCount} chart canvas elements`);
            } else {
                console.log('No chart containers found');
            }
        } catch (e) {
            console.log('Chart handling warning:', e.message);
        }

        console.log('Generating PDF with Foord design specifications...');

        // Generate PDF with exact A4 dimensions
        // The template handles all margins internally, so we set margins to 0
        await page.pdf({
            path: '".addslashes($pdfPath)."',
            format: 'A4',
            printBackground: true,
            margin: {
                top: '0',
                bottom: '0',
                left: '0',
                right: '0'
            },
            preferCSSPageSize: true,
            displayHeaderFooter: false
        });

        console.log('PDF generated successfully at: ".addslashes($pdfPath)."');

    } catch (error) {
        console.error('Error generating PDF:', error.message);
        console.error('Stack trace:', error.stack);
        throw error;
    } finally {
        await browser.close();
    }
})();
";
    }

    /**
     * Environment for the render subprocess.
     *
     * proc_open REPLACES the environment rather than adding to it, so anything
     * Chrome needs must be listed here. HOME is the load-bearing one: without
     * it Chrome resolves its profile directory to "/" and dies with
     * "mkdir: cannot create directory '/.local': Permission denied". The queue
     * worker runs as www-data, whose home (/var/www) is root-owned, so it has
     * to be pointed at storage instead.
     *
     * @return array<string, string>
     */
    protected function renderEnvironment(): array
    {
        $home = $this->chromeHome();

        return [
            'NODE_PATH' => $this->nodePath ?: base_path('node_modules'),
            // The shell proc_open spawns needs this to find the node binary.
            'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin',
            'HOME' => $home,
            'XDG_CONFIG_HOME' => $home.'/.config',
            'XDG_CACHE_HOME' => $home.'/.cache',
            // Pinned separately from HOME: with no chrome_path configured the
            // "puppeteer" package resolves the browser it downloaded relative
            // to HOME, and the override above would send it somewhere empty.
            'PUPPETEER_CACHE_DIR' => $this->cacheDir ?: $this->defaultBrowserCacheDir(),
        ];
    }

    /**
     * Where `npx puppeteer browsers install chrome` puts the browser: the cache
     * directory of the user running the worker, not the substituted HOME.
     */
    protected function defaultBrowserCacheDir(): string
    {
        $realHome = getenv('HOME') ?: null;

        if (! $realHome && function_exists('posix_geteuid')) {
            $realHome = posix_getpwuid(posix_geteuid())['dir'] ?? null;
        }

        return rtrim((string) ($realHome ?: sys_get_temp_dir()), '/').'/.cache/puppeteer';
    }

    /**
     * Writable base directory stood in for the render user's home directory.
     */
    protected function chromeHome(): string
    {
        $home = storage_path('app/temp/chrome');

        if (! is_dir($home)) {
            mkdir($home, 0775, true);
        }

        return $home;
    }

    /**
     * A fresh Chrome profile per render.
     *
     * Concurrent renders sharing one profile collide on Chrome's singleton
     * lock, so each gets its own and it is removed once the render finishes.
     */
    protected function chromeProfileDir(): string
    {
        $dir = $this->chromeHome().'/profile-'.uniqid('', true);

        mkdir($dir, 0775, true);

        return $dir;
    }

    /**
     * Remove a render's throwaway profile directory.
     */
    protected function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }

    /**
     * Execute the Puppeteer script with timeout handling.
     *
     * This is the render boundary: it is intentionally a protected method so it
     * can be overridden in tests to validate orchestration without a real Chrome.
     */
    protected function executePuppeteerScript(string $script, string $tempPdfPath, Fund $fund): void
    {
        $command = ['node', '-e', $script];

        $process = proc_open(
            implode(' ', array_map('escapeshellarg', $command)),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null,
            $this->renderEnvironment()
        );

        if (! is_resource($process)) {
            throw new \Exception('Failed to start Puppeteer process');
        }

        fclose($pipes[0]);

        // Set streams to non-blocking for timeout handling
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $error = '';
        $timeout = $this->timeout;
        $start = time();

        while (true) {
            $status = proc_get_status($process);

            if (! $status['running']) {
                // Process finished
                $output .= stream_get_contents($pipes[1]);
                $error .= stream_get_contents($pipes[2]);
                break;
            }

            if ((time() - $start) > $timeout) {
                // Timeout reached, kill the process
                proc_terminate($process);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                throw new \Exception('Puppeteer process timed out after '.$timeout.' seconds');
            }

            // Read available output
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);

            usleep(100000); // Sleep for 0.1 seconds
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $returnValue = proc_close($process);

        // Check if PDF was actually created successfully despite non-zero return code
        $pdfWasCreated = file_exists($tempPdfPath) && filesize($tempPdfPath) > 0;

        if ($returnValue !== 0 && ! $pdfWasCreated) {
            $errorMessage = $error ?: $output ?: 'Unknown error occurred';
            Log::error('Puppeteer process failed', [
                'fund_id' => $fund->id,
                'return_value' => $returnValue,
                'output' => $output,
                'error' => $error,
                'pdf_created' => $pdfWasCreated,
            ]);
            throw new \Exception('Puppeteer process failed: '.$errorMessage);
        } elseif ($returnValue !== 0 && $pdfWasCreated) {
            // PDF was created but process returned non-zero, log warning but continue
            Log::warning('Puppeteer process returned non-zero but PDF was created', [
                'fund_id' => $fund->id,
                'return_value' => $returnValue,
                'output' => $output,
                'error' => $error,
                'pdf_size' => filesize($tempPdfPath),
            ]);
        }

        Log::info('Puppeteer PDF generation completed', [
            'fund_id' => $fund->id,
            'output' => $output,
        ]);
    }
}
