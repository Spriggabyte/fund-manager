<?php

require_once 'vendor/autoload.php';

use App\Models\Fund;
use App\Services\SvgChartService;
use Illuminate\Database\Capsule\Manager as Capsule;

// Bootstrap Laravel without HTTP
require_once 'bootstrap/app.php';

// Read environment variables from .env file
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv(sprintf('%s=%s', $name, $value));
        }
    }
}

// Setup database connection
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_DATABASE'),
    'username'  => getenv('DB_USERNAME'),
    'password'  => getenv('DB_PASSWORD'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Get fund 10
    $fund = Fund::find(10);
    
    if (!$fund) {
        echo "Fund 10 not found\n";
        exit(1);
    }
    
    echo "Testing chart generation for Fund: " . $fund->name . "\n";
    
    // Test chart service
    $chartService = new SvgChartService();
    $charts = $chartService->generateChartsForFund($fund);
    
    echo "Charts generated: " . count($charts) . "\n";
    
    foreach ($charts as $name => $svg) {
        echo "Chart: $name - " . strlen($svg) . " characters\n";
        
        // Save to file for inspection
        file_put_contents("debug_chart_{$name}.svg", $svg);
        echo "Saved to debug_chart_{$name}.svg\n";
        
        // Check if it contains valid SVG
        if (strpos($svg, '<svg') !== false && strpos($svg, '</svg>') !== false) {
            echo "✓ Valid SVG structure\n";
        } else {
            echo "✗ Invalid SVG structure\n";
        }
    }
    
    // Test simple SVG rendering
    $testSvg = '<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="red"/><text x="50" y="50" text-anchor="middle" fill="white">TEST</text></svg>';
    file_put_contents('test_simple.svg', $testSvg);
    echo "Test SVG saved to test_simple.svg\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}