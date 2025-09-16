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
        
        // Generate the full URL to the fund show page
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
        
        // Set a proper viewport for desktop rendering - ensure it's above Tailwind's md: breakpoint (768px)
        await page.setViewport({
            width: 1200,
            height: 1600,
            deviceScaleFactor: 2
        });
        
        console.log('Viewport set to 1200x1600 - should trigger md: breakpoint (768px+)');
        
        console.log('Navigating to: " . addslashes($url) . "');
        
        // Navigate to the page and wait for all resources
        await page.goto('" . addslashes($url) . "', {
            waitUntil: ['networkidle0', 'load'],
            timeout: 60000
        });
        
        console.log('Page loaded, waiting for Tailwind CSS to fully initialize...');
        
        // Wait for Tailwind CSS to be properly loaded and applied
        try {
            await page.waitForFunction(() => {
                // Check if Tailwind classes are being applied correctly
                const testEl = document.querySelector('.bg-gray-800');
                if (!testEl) return false;
                
                const styles = window.getComputedStyle(testEl);
                const bgColor = styles.backgroundColor;
                // Check if the background color matches Tailwind's gray-800 (#1f2937)
                return bgColor === 'rgb(31, 41, 55)' || bgColor.includes('31') || bgColor !== 'rgba(0, 0, 0, 0)';
            }, { timeout: 10000 });
            console.log('Tailwind CSS fully loaded and applied');
        } catch (e) {
            console.log('Timeout waiting for Tailwind, continuing anyway');
        }
        
        console.log('Now waiting for charts...');
        
        // Wait for Chart.js and charts to render
        try {
            // First check if there are chart containers
            const hasCharts = await page.evaluate(() => {
                return document.querySelector('#inflationChart') !== null || 
                       document.querySelector('#portfolioChart') !== null ||
                       document.querySelectorAll('canvas').length > 0;
            });
            
            if (hasCharts) {
                console.log('Chart containers found, waiting for Chart.js...');
                
                // Wait for Chart.js library to load
                await page.waitForFunction(() => {
                    return typeof window.Chart !== 'undefined';
                }, { timeout: 15000 });
                
                console.log('Chart.js loaded, waiting for charts to initialize...');
                
                // Wait for scripts to execute and create charts
                await page.waitForTimeout(3000);
                
                // Check if any charts were actually created
                const chartCount = await page.evaluate(() => {
                    const canvases = document.querySelectorAll('canvas');
                    let renderedCharts = 0;
                    
                    for (let canvas of canvases) {
                        if (canvas.width > 0 && canvas.height > 0) {
                            try {
                                const ctx = canvas.getContext('2d');
                                const imageData = ctx.getImageData(0, 0, Math.min(10, canvas.width), Math.min(10, canvas.height));
                                const hasContent = imageData.data.some((pixel, index) => index % 4 !== 3 && pixel !== 0);
                                if (hasContent) renderedCharts++;
                            } catch (e) {
                                // If we can't read the canvas, assume it has content
                                renderedCharts++;
                            }
                        }
                    }
                    
                    return { total: canvases.length, rendered: renderedCharts };
                });
                
                console.log(`Found \${chartCount.total} canvas elements, \${chartCount.rendered} appear to have content`);
                
                if (chartCount.total > 0 && chartCount.rendered === 0) {
                    console.log('Charts found but appear empty, waiting a bit more...');
                    await page.waitForTimeout(2000);
                }
                
            } else {
                console.log('No chart containers found in page');
            }
            
        } catch (e) {
            console.log('Chart handling failed:', e.message);
        }
        
        console.log('Cleaning up page for PDF...');
        
        // Remove navigation elements and optimize for PDF
        await page.evaluate(() => {
            // Remove navigation and edit controls only
            const elementsToRemove = document.querySelectorAll('.no-print, .notification');
            elementsToRemove.forEach(el => el.remove());
            
            // Remove Alpine.js attributes that might interfere
            const alpineElements = document.querySelectorAll('*');
            alpineElements.forEach(el => {
                const attributes = [...el.attributes];
                attributes.forEach(attr => {
                    if (attr.name.startsWith('x-') || attr.name.startsWith('@') || attr.name.startsWith(':')) {
                        el.removeAttribute(attr.name);
                    }
                });
            });
            
            // Ensure body and main container styling
            document.body.style.margin = '0';
            document.body.style.padding = '0';
            document.body.style.background = '#f3f4f6';
            
            // Fix container width for PDF
            const containers = document.querySelectorAll('.max-w-4xl, .container');
            containers.forEach(container => {
                container.style.maxWidth = '100%';
                container.style.width = '100%';
                container.style.margin = '0 auto';
                container.style.padding = '0';
            });
            
            // Simple layout forcing - ignore responsive classes completely
            console.log('Forcing all layouts to be horizontal...');
            
            // Force ALL flex containers to be horizontal, except for the main outer container
            const allFlexContainers = document.querySelectorAll('.flex');
            console.log('Found ' + allFlexContainers.length + ' flex containers to fix');
            
            allFlexContainers.forEach((container, index) => {
                // Don't change the outer flex-col container that holds the main sections
                if (!container.classList.contains('max-w-4xl') && !container.parentElement.classList.contains('max-w-4xl')) {
                    container.style.display = 'flex';
                    container.style.flexDirection = 'row';
                    console.log('Set container ' + (index + 1) + ' to horizontal');
                } else {
                    console.log('Skipped main container ' + (index + 1));
                }
            });
            
            // Set exact sidebar width from reference
            const allSidebars = document.querySelectorAll('.bg-gray-800.p-6');
            console.log('Found ' + allSidebars.length + ' sidebars to fix');
            
            allSidebars.forEach((sidebar, index) => {
                sidebar.style.width = '240px';
                sidebar.style.minWidth = '240px';
                sidebar.style.maxWidth = '240px';
                sidebar.style.flex = '0 0 240px';
                sidebar.style.flexShrink = '0';
                console.log('Set sidebar ' + (index + 1) + ' to 240px width to match reference exactly');
            });
            
            // Force ALL main content areas to flex
            const allMainContent = document.querySelectorAll('.flex-1');
            console.log('Found ' + allMainContent.length + ' main content areas to fix');
            
            allMainContent.forEach((content, index) => {
                content.style.flex = '1';
                console.log('Set main content ' + (index + 1) + ' to flex: 1');
            });
            
            // Force ALL grid containers to display charts side by side
            const allGrids = document.querySelectorAll('.grid');
            console.log('Found ' + allGrids.length + ' grid containers to fix');
            
            allGrids.forEach((grid, index) => {
                grid.style.display = 'grid';
                grid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                grid.style.gap = '1rem';
                console.log('Set grid ' + (index + 1) + ' to 2-column layout');
            });
            
            // Ensure chart containers are properly sized
            const chartContainers = document.querySelectorAll('.grid .p-1');
            console.log('Found ' + chartContainers.length + ' chart containers to fix');
            
            chartContainers.forEach((container, index) => {
                container.style.width = '100%';
                container.style.display = 'block';
                console.log('Set chart container ' + (index + 1) + ' to full width');
            });
            
            // Smart page break prevention matching reference structure
            const firstSection = document.querySelector('.bg-white.shadow-lg:first-of-type');
            if (firstSection) {
                firstSection.style.pageBreakInside = 'avoid';
                firstSection.style.pageBreakAfter = 'avoid';
                console.log('Protected first section from breaking');
            }
            
            // Only allow page break at the designated element
            const pageBreakSection = document.querySelector('.page-break');
            if (pageBreakSection) {
                pageBreakSection.style.pageBreakBefore = 'always';
                pageBreakSection.style.breakBefore = 'always';
                console.log('Set page break only at designated section');
            }
            
            // Apply exact styling from reference
            document.body.style.fontSize = '0.875rem';
            document.body.style.lineHeight = '1.25';
            document.body.style.fontFamily = 'system-ui, -apple-system, sans-serif';
            
            console.log('Applied exact styling to match reference');
            console.log('Page cleanup and layout forcing completed');
        });
        
        // Add simple, non-responsive CSS that forces consistent layout
        await page.addStyleTag({
            content: `
                @media print, screen {
                    * {
                        print-color-adjust: exact !important;
                    }
                    
                    /* REMOVE ALL RESPONSIVE BEHAVIOR - FORCE CONSISTENT LAYOUT */
                    
                    /* Force ALL flex containers to be horizontal with no wrap */
                    .flex {
                        display: flex !important;
                        flex-direction: row !important;
                        flex-wrap: nowrap !important;
                    }
                    
                    /* Exact sidebar width from reference - allow page breaks */
                    .bg-gray-800.p-6 {
                        width: 240px !important;
                        min-width: 240px !important;
                        max-width: 240px !important;
                        flex: 0 0 240px !important;
                        flex-shrink: 0 !important;
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                    }
                    
                    /* Force ALL main content areas to fill remaining space */
                    .flex-1 {
                        flex: 1 1 0% !important;
                        min-width: 0 !important;
                        max-width: calc(100% - 240px) !important;
                    }
                    
                    /* Identical to reference layout */
                    @page {
                        size: A4;
                        margin: 0.5in;
                    }
                    
                    /* Keep first page content together */
                    .bg-white.shadow-lg:first-of-type {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                        page-break-after: avoid !important;
                        break-after: avoid !important;
                    }
                    
                    /* Page break only at designated section */
                    .page-break {
                        page-break-before: always !important;
                        break-before: always !important;
                    }
                    
                    /* Prevent unwanted breaks */
                    table, .grid {
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }
                    
                    /* Grid layout for charts - optimized for 2-page density */
                    .grid {
                        display: grid !important;
                        grid-template-columns: repeat(2, 1fr) !important;
                        gap: 1rem !important;
                        margin-bottom: 1rem !important;
                        width: 100% !important;
                        page-break-inside: avoid !important;
                    }
                    
                    .grid-cols-1 {
                        grid-template-columns: repeat(2, 1fr) !important;
                    }
                    
                    .lg\\:grid-cols-2 {
                        grid-template-columns: repeat(2, 1fr) !important;
                    }
                    
                    /* Canvas styling for charts - optimized height for 2 pages */
                    canvas {
                        max-height: 280px !important;
                        width: 100% !important;
                        display: block !important;
                    }
                    
                    /* Chart containers with compact spacing for 2-page fit */
                    .grid .p-1 {
                        display: block !important;
                        width: 100% !important;
                        padding: 0.25rem !important;
                    }
                    
                    /* Exact spacing from reference */
                    .mb-6, .mb-4, .mb-8 {
                        margin-bottom: 0.3rem !important;
                    }
                    
                    .mt-4, .mt-8 {
                        margin-top: 0.25rem !important;
                    }
                    
                    /* Reference padding */
                    .p-6 {
                        padding: 0.5rem !important;
                    }
                    
                    /* Exact font sizing from reference */
                    .text-sm {
                        font-size: 0.875rem !important;
                        line-height: 1.3 !important;
                    }
                    
                    .text-xs {
                        font-size: 0.75rem !important;
                        line-height: 1.2 !important;
                    }
                    
                    /* Reference text defaults */
                    p, div, span {
                        line-height: 1.25 !important;
                        margin: 0.1rem 0 !important;
                        font-size: 0.875rem !important;
                    }
                    
                    /* Reference table styling */
                    table {
                        margin: 0.3rem 0 !important;
                        font-size: 0.75rem !important;
                    }
                    
                    td, th {
                        padding: 0.125rem 0.25rem !important;
                        line-height: 1.2 !important;
                        font-size: 0.75rem !important;
                    }
                    
                    /* Reference chart sizing */
                    canvas {
                        max-height: 240px !important;
                        width: 100% !important;
                    }
                    
                    /* Exact heading hierarchy from reference */
                    h1 {
                        font-size: 1.25rem !important;
                        margin: 0.25rem 0 !important;
                        line-height: 1.2 !important;
                        font-weight: bold !important;
                    }
                    
                    h2, h3 {
                        margin: 0.15rem 0 !important;
                        line-height: 1.2 !important;
                        font-size: 0.875rem !important;
                        font-weight: bold !important;
                    }
                    
                    /* Override specific responsive classes that interfere with layout */
                    .flex.flex-col.md\\:flex-row { 
                        flex-direction: row !important; 
                        flex-wrap: nowrap !important;
                    }
                    
                    /* Keep main container vertical */
                    .max-w-4xl > .flex-col {
                        flex-direction: column !important;
                    }
                    
                    /* Ensure flex containers don't break across pages improperly */
                    .flex:not(.flex-col) {
                        page-break-inside: auto !important;
                        break-inside: auto !important;
                    }
                    
                    /* Fix container max-width to fit A4 with margins */
                    .max-w-4xl {
                        max-width: 100% !important;
                        width: 100% !important;
                    }
                }
            `
        });
        
        console.log('Generating PDF...');
        
        // Generate PDF identical to reference
        await page.pdf({
            path: '" . addslashes($pdfPath) . "',
            format: 'A4',
            printBackground: true,
            margin: {
                top: '0.5in',
                bottom: '0.5in', 
                left: '0.5in',
                right: '0.5in'
            },
            preferCSSPageSize: false,
            displayHeaderFooter: false,
            scale: 0.65  // Exact scale to match reference density
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