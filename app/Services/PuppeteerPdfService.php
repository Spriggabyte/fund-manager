<?php

namespace App\Services;

use App\Models\Fund;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PuppeteerPdfService
{
    /**
     * Generate a PDF from the fund show page using Puppeteer.
     */
    public function generatePdf(Fund $fund): string
    {
        $filename = 'fund-' . $fund->id . '-' . now()->format('Y-m-d-His') . '.pdf';
        $tempDir = storage_path('app/temp');
        $tempPdfPath = $tempDir . '/' . $filename;
        
        // Ensure temp directory exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Generate the full URL to the fund show page (internal route bypasses auth)
        $baseUrl = env('APP_URL', 'http://foord-funds.test');
        $url = $baseUrl . '/internal/funds/' . $fund->id . '/pdf-view';
        
        // Create the Puppeteer script (no auth cookie needed for internal route)
        $script = $this->generatePuppeteerScript($url, $tempPdfPath);
        
        // Execute the script
        $this->executePuppeteerScript($script, $tempPdfPath);
        
        // Verify PDF was created
        if (!file_exists($tempPdfPath)) {
            throw new \Exception('PDF file was not generated');
        }
        
        return $tempPdfPath;
    }
    
    /**
     * Generate the Puppeteer JavaScript code.
     */
    private function generatePuppeteerScript(string $url, string $pdfPath): string
    {
        return "
const puppeteer = require('puppeteer-core');

(async () => {
    const browser = await puppeteer.launch({
        headless: true,
        executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        timeout: 60000,
        args: [
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

        console.log('Navigating to: " . addslashes($url) . "');

        // Navigate to the page and wait for all resources
        await page.goto('" . addslashes($url) . "', {
            waitUntil: ['networkidle0', 'load', 'domcontentloaded'],
            timeout: 60000
        });

        console.log('Page loaded, waiting for fonts to load...');

        // Wait for web fonts to load
        await page.evaluate(async () => {
            await document.fonts.ready;
        });

        console.log('Fonts loaded, waiting for charts...');

        // Wait for Chart.js and charts to render
        try {
            const hasCharts = await page.evaluate(() => {
                return document.querySelector('#inflationChart') !== null ||
                       document.querySelector('#portfolioChart') !== null;
            });

            if (hasCharts) {
                console.log('Chart containers found, waiting for Chart.js...');

                await page.waitForFunction(() => {
                    return typeof window.Chart !== 'undefined';
                }, { timeout: 15000 });

                console.log('Chart.js loaded, waiting for charts to render...');

                // Wait for DOMContentLoaded event to fire (charts init)
                await page.waitForTimeout(2000);

                // Additional wait for charts to fully render
                await page.evaluate(() => {
                    return new Promise(resolve => {
                        setTimeout(resolve, 1500);
                    });
                });

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
            path: '" . addslashes($pdfPath) . "',
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

        console.log('PDF generated successfully at: " . addslashes($pdfPath) . "');

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
     * Execute the Puppeteer script with timeout handling.
     */
    private function executePuppeteerScript(string $script, string $tempPdfPath): void
    {
        $command = ['node', '-e', $script];
        
        $process = proc_open(
            implode(' ', array_map('escapeshellarg', $command)),
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes,
            null,
            ['NODE_PATH' => base_path('node_modules')]
        );
        
        if (!is_resource($process)) {
            throw new \Exception('Failed to start Puppeteer process');
        }
        
        fclose($pipes[0]);
        
        // Set streams to non-blocking for timeout handling
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        
        $output = '';
        $error = '';
        $timeout = 120; // 120 seconds timeout for better chart rendering
        $start = time();
        
        while (true) {
            $status = proc_get_status($process);
            
            if (!$status['running']) {
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
                throw new \Exception('Puppeteer process timed out after ' . $timeout . ' seconds');
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
        
        if ($returnValue !== 0 && !$pdfWasCreated) {
            $errorMessage = $error ?: $output ?: 'Unknown error occurred';
            Log::error('Puppeteer process failed', [
                'return_value' => $returnValue,
                'output' => $output,
                'error' => $error,
                'pdf_created' => $pdfWasCreated
            ]);
            throw new \Exception('Puppeteer process failed: ' . $errorMessage);
        } elseif ($returnValue !== 0 && $pdfWasCreated) {
            // PDF was created but process returned non-zero, log warning but continue
            Log::warning('Puppeteer process returned non-zero but PDF was created', [
                'return_value' => $returnValue,
                'output' => $output,
                'error' => $error,
                'pdf_size' => filesize($tempPdfPath)
            ]);
        }
        
        Log::info('Puppeteer PDF generation completed', ['output' => $output]);
    }
}