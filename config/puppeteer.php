<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chrome / Chromium executable path
    |--------------------------------------------------------------------------
    |
    | Absolute path to the Chrome/Chromium binary Puppeteer should drive. When
    | this is null the service falls back to the "puppeteer" npm package, which
    | locates the browser it downloaded itself (`npx puppeteer browsers install
    | chrome`). On a Linux server set this to e.g. /usr/bin/google-chrome-stable.
    | The previous hard-coded macOS path is gone — this MUST be configured per
    | environment.
    |
    */

    'chrome_path' => env('PUPPETEER_EXECUTABLE_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Node module resolution path
    |--------------------------------------------------------------------------
    |
    | Directory used as NODE_PATH when running the inline render script. Null
    | defaults to the application's own node_modules.
    |
    */

    'node_path' => env('PUPPETEER_NODE_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Browser cache directory
    |--------------------------------------------------------------------------
    |
    | Only consulted when "chrome_path" is null and the fallback "puppeteer"
    | package has to locate the browser it downloaded. The render subprocess
    | runs with a substituted HOME (Chrome needs a writable profile directory
    | and the worker user's real home usually is not), which would otherwise
    | move this lookup with it. Null resolves to the worker user's own
    | ~/.cache/puppeteer, which is where `npx puppeteer browsers install`
    | writes.
    |
    */

    'cache_dir' => env('PUPPETEER_CACHE_DIR'),

    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    |
    | "timeout" is the wall-clock ceiling (seconds) for the whole render
    | subprocess. It MUST sit inside the queue timeout layering:
    |   proc timeout (180) < job $timeout (200) < worker --timeout (240)
    |   < queue retry_after (300)
    | so a slow render can never be re-dispatched while still running.
    | "nav_timeout" is the per-navigation timeout (milliseconds).
    |
    */

    'timeout' => (int) env('PUPPETEER_TIMEOUT', 180),

    'nav_timeout' => (int) env('PUPPETEER_NAV_TIMEOUT', 60000),

    /*
    |--------------------------------------------------------------------------
    | Generated PDF storage
    |--------------------------------------------------------------------------
    |
    | Filesystem disk and directory where completed fact-sheet PDFs are stored
    | by the queued job so they can be downloaded once rendering finishes.
    |
    */

    'output_disk' => env('PUPPETEER_OUTPUT_DISK', 'local'),

    'output_dir' => env('PUPPETEER_OUTPUT_DIR', 'pdfs'),

];
