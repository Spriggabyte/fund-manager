<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fund;
use App\Services\PuppeteerPdfService;

class GeneratePdfsCommand extends Command
{
    protected $signature = 'qa:generate-pdfs';
    protected $description = 'Generate all PDFs for QA';

    public function handle(PuppeteerPdfService $service)
    {
        $funds = Fund::all();
        $outDir = '/Users/gsadmin/.gemini/antigravity-cli/brain/2b3cd93c-8c55-4c62-9b8a-0d54c07b030f/scratch/pdfs';
        
        foreach ($funds as $fund) {
            try {
                $this->info("Generating PDF for fund: {$fund->id}");
                $tempPath = $service->generatePdf($fund);
                $name = "fund_{$fund->id}.pdf";
                copy($tempPath, "{$outDir}/{$name}");
                @unlink($tempPath);
            } catch (\Exception $e) {
                $this->error("Failed to generate PDF for fund: {$fund->id}. Error: " . $e->getMessage());
            }
        }
        $this->info("Done!");
    }
}
