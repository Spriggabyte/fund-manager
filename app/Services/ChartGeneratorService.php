<?php

namespace App\Services;

use App\Models\Fund;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ChartGeneratorService
{
    protected string $chartOutputPath;

    protected string $tempPath;

    public function __construct()
    {
        $this->chartOutputPath = storage_path('app/charts');
        $this->tempPath = storage_path('app/temp');

        // Ensure directories exist
        if (! is_dir($this->chartOutputPath)) {
            mkdir($this->chartOutputPath, 0755, true);
        }
        if (! is_dir($this->tempPath)) {
            mkdir($this->tempPath, 0755, true);
        }
    }

    /**
     * Generate chart images for a fund
     */
    public function generateChartsForFund(Fund $fund): array
    {
        $fundId = $fund->id;
        $outputDir = $this->chartOutputPath.'/'.$fundId;

        // Create fund-specific directory
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Create temporary JSON file with fund data
        $tempFile = $this->tempPath.'/fund_'.$fundId.'_'.time().'.json';
        file_put_contents($tempFile, json_encode($fund->data));

        try {
            // Run Node.js chart generator
            $process = new Process([
                'node',
                base_path('chart-generator.js'),
                $tempFile,
                $outputDir,
            ]);

            $process->setTimeout(60); // 60 second timeout
            $process->run();

            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Clean up temp file
            unlink($tempFile);

            // Return paths to generated images
            return [
                'inflation' => $outputDir.'/inflation-chart.png',
                'portfolio' => $outputDir.'/portfolio-chart.png',
            ];

        } catch (ProcessFailedException $exception) {
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            throw new \Exception('Chart generation failed: '.$exception->getMessage());
        }
    }

    /**
     * Get chart images as base64 data URLs for embedding in HTML
     */
    public function getChartsAsDataUrls(Fund $fund): array
    {
        try {
            $chartPaths = $this->generateChartsForFund($fund);

            $result = [];
            foreach ($chartPaths as $name => $path) {
                if (file_exists($path)) {
                    $imageData = base64_encode(file_get_contents($path));
                    $result[$name] = 'data:image/png;base64,'.$imageData;
                }
            }

            return $result;
        } catch (\Exception $e) {
            // Return empty array if chart generation fails
            return [];
        }
    }

    /**
     * Check if Node.js and required packages are available
     */
    public function isAvailable(): bool
    {
        try {
            $process = new Process(['node', '--version']);
            $process->run();

            return $process->isSuccessful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clean up old chart files
     */
    public function cleanupOldCharts(int $daysOld = 7): void
    {
        $cutoffTime = time() - ($daysOld * 24 * 60 * 60);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->chartOutputPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getMTime() < $cutoffTime) {
                unlink($file->getPathname());
            }
        }
    }
}
