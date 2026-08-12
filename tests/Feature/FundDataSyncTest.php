<?php

namespace Tests\Feature;

use App\Services\FundImport\FundDataSyncService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FundDataSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('sftp');
        Storage::fake('local');
    }

    private function sync(?string $month = null, bool $dryRun = false): array
    {
        return app(FundDataSyncService::class)->sync($month, $dryRun);
    }

    public function test_mirrors_remote_month_folders_to_local_storage(): void
    {
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'factsheet-bytes');
        Storage::disk('sftp')->put('2026-06/810/810A_PRICE_GRAPH.xlsx', 'graph-bytes');

        $report = $this->sync();

        Storage::disk('local')->assertExists('fund-data/2026-06/817/817A_FACTSHEET.xlsx');
        Storage::disk('local')->assertExists('fund-data/2026-06/810/810A_PRICE_GRAPH.xlsx');
        $this->assertSame('factsheet-bytes', Storage::disk('local')->get('fund-data/2026-06/817/817A_FACTSHEET.xlsx'));
        $this->assertSame(['2026-06'], $report['months']);
        $this->assertCount(2, $report['downloaded']);
        $this->assertSame([], $report['skipped']);
        $this->assertSame([], $report['errors']);
    }

    public function test_ignores_remote_directories_not_matching_year_month(): void
    {
        Storage::disk('sftp')->put('archive/817/OLD.xlsx', 'x');
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'x');

        $report = $this->sync();

        $this->assertSame(['2026-06'], $report['months']);
        Storage::disk('local')->assertMissing('fund-data/archive/817/OLD.xlsx');
    }

    public function test_skips_files_already_downloaded_with_same_size(): void
    {
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'same-bytes');
        Storage::disk('local')->put('fund-data/2026-06/817/817A_FACTSHEET.xlsx', 'same-bytes');

        $report = $this->sync();

        $this->assertSame([], $report['downloaded']);
        $this->assertSame(['fund-data/2026-06/817/817A_FACTSHEET.xlsx'], $report['skipped']);
    }

    public function test_redownloads_when_remote_file_size_differs(): void
    {
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'replacement with more bytes');
        Storage::disk('local')->put('fund-data/2026-06/817/817A_FACTSHEET.xlsx', 'stale');

        $report = $this->sync();

        $this->assertSame(['fund-data/2026-06/817/817A_FACTSHEET.xlsx'], $report['downloaded']);
        $this->assertSame('replacement with more bytes', Storage::disk('local')->get('fund-data/2026-06/817/817A_FACTSHEET.xlsx'));
    }

    public function test_month_filter_limits_sync_to_that_month(): void
    {
        Storage::disk('sftp')->put('2026-05/817/817A_FACTSHEET.xlsx', 'may');
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'june');

        $report = $this->sync('2026-06');

        $this->assertSame(['2026-06'], $report['months']);
        Storage::disk('local')->assertExists('fund-data/2026-06/817/817A_FACTSHEET.xlsx');
        Storage::disk('local')->assertMissing('fund-data/2026-05/817/817A_FACTSHEET.xlsx');
    }

    public function test_dry_run_reports_without_writing(): void
    {
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'x');

        $report = $this->sync(null, true);

        $this->assertSame(['fund-data/2026-06/817/817A_FACTSHEET.xlsx'], $report['downloaded']);
        Storage::disk('local')->assertMissing('fund-data/2026-06/817/817A_FACTSHEET.xlsx');
    }

    public function test_sync_command_warns_when_sftp_not_configured(): void
    {
        // Set explicitly rather than relying on the developer's .env — once a
        // real SFTP_HOST is configured locally this test would otherwise fail.
        config(['filesystems.disks.sftp.host' => null]);

        $this->artisan('funds:sync-data')
            ->expectsOutputToContain('SFTP_HOST is not configured')
            ->assertExitCode(0);
    }

    public function test_sync_command_downloads_and_reports(): void
    {
        config(['filesystems.disks.sftp.host' => 'feed.example.com']);
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'bytes');

        $this->artisan('funds:sync-data')
            ->expectsOutputToContain('Downloaded: 1')
            ->assertExitCode(0);

        Storage::disk('local')->assertExists('fund-data/2026-06/817/817A_FACTSHEET.xlsx');
    }

    public function test_sync_command_dry_run_writes_nothing(): void
    {
        config(['filesystems.disks.sftp.host' => 'feed.example.com']);
        Storage::disk('sftp')->put('2026-06/817/817A_FACTSHEET.xlsx', 'bytes');

        $this->artisan('funds:sync-data', ['--dry-run' => true])
            ->expectsOutputToContain('fund-data/2026-06/817/817A_FACTSHEET.xlsx')
            ->assertExitCode(0);

        Storage::disk('local')->assertMissing('fund-data/2026-06/817/817A_FACTSHEET.xlsx');
    }
}
