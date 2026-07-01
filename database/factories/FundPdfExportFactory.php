<?php

namespace Database\Factories;

use App\Models\Fund;
use App\Models\FundPdfExport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FundPdfExport>
 */
class FundPdfExportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fund_id' => Fund::factory(),
            'user_id' => User::factory(),
            'template' => 'pdf',
            'status' => FundPdfExport::STATUS_PENDING,
        ];
    }

    public function done(string $path = 'pdfs/example.pdf'): static
    {
        return $this->state([
            'status' => FundPdfExport::STATUS_DONE,
            'disk' => 'local',
            'path' => $path,
            'started_at' => now()->subSeconds(30),
            'completed_at' => now(),
        ]);
    }

    public function failed(string $error = 'render failed'): static
    {
        return $this->state([
            'status' => FundPdfExport::STATUS_FAILED,
            'error' => $error,
            'started_at' => now()->subSeconds(30),
            'completed_at' => now(),
        ]);
    }
}
