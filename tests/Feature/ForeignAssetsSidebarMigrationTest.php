<?php

namespace Tests\Feature;

use App\Models\Fund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForeignAssetsSidebarMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_drops_singapore_from_the_stale_foreign_assets_line(): void
    {
        $stale = Fund::factory()->create([
            'template' => 'show',
            'foreign_assets' => 'Foreign asset exposure is obtained predominantly via the US dollar priced Foord global funds in Luxembourg and Singapore.',
        ]);
        $other = Fund::factory()->create([
            'template' => 'show-asia-ex-japan',
            'foreign_assets' => 'Singapore and Hong Kong listed equities.',
        ]);

        $migration = require database_path('migrations/2026_09_30_180000_drop_singapore_from_foreign_assets_sidebar.php');
        $migration->up();

        $this->assertSame(
            'Foreign asset exposure is obtained predominantly via the US dollar priced Foord global funds in Luxembourg.',
            $stale->fresh()->foreign_assets
        );
        $this->assertSame('Singapore and Hong Kong listed equities.', $other->fresh()->foreign_assets);
    }
}
