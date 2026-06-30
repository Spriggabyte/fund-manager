<?php

namespace App\Jobs;

use App\Models\FundPdfExport;
use App\Services\PuppeteerPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateFundPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Attempts before the job is marked failed.
     */
    public int $tries = 3;

    /**
     * Per-attempt timeout (seconds). Sits inside the timeout layering:
     * proc 180 < job 200 < worker --timeout 240 < queue retry_after 300.
     */
    public int $timeout = 200;

    /**
     * Backoff between retries (seconds).
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 120];

    public function __construct(public FundPdfExport $export) {}

    /**
     * Stop retrying after this point regardless of remaining attempts.
     */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(10);
    }

    public function handle(PuppeteerPdfService $service): void
    {
        $this->export->update([
            'status' => FundPdfExport::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        $tempPath = $service->generatePdf($this->export->fund);

        $disk = config('puppeteer.output_disk');
        $dir = trim((string) config('puppeteer.output_dir'), '/');
        $name = "fund-{$this->export->fund_id}-{$this->export->id}.pdf";
        $path = $dir === '' ? $name : "{$dir}/{$name}";

        Storage::disk($disk)->put($path, file_get_contents($tempPath));
        @unlink($tempPath);

        $this->export->update([
            'status' => FundPdfExport::STATUS_DONE,
            'disk' => $disk,
            'path' => $path,
            'completed_at' => now(),
        ]);
    }

    /**
     * Record and log the failure with enough context to debug a bad render.
     * Sentry additionally auto-captures the queue.failed event.
     */
    public function failed(Throwable $e): void
    {
        $this->export->update([
            'status' => FundPdfExport::STATUS_FAILED,
            'error' => $e->getMessage(),
            'completed_at' => now(),
        ]);

        Log::error('Fund PDF generation failed', [
            'export_id' => $this->export->id,
            'fund_id' => $this->export->fund_id,
            'user_id' => $this->export->user_id,
            'template' => $this->export->template,
            'exception' => $e->getMessage(),
        ]);
    }
}
