<?php

namespace Deployer;

require 'recipe/laravel.php';

// -----------------------------------------------------------------------------
// Zero-downtime deploy for the Foord fund-manager app.
//
// Deployer builds each release in releases/<n>, then atomically switches the
// `current` symlink. Rollback is `dep rollback` (re-points current to the
// previous release). Host/credentials come from env so nothing is hard-coded —
// see the DEPLOY_* variables below and DEPLOYMENT.md.
// -----------------------------------------------------------------------------

set('application', 'fund-manager');
set('repository', getenv('DEPLOY_REPOSITORY') ?: 'git@github.com:your-org/fund-manager.git');
set('keep_releases', 5);
set('git_tty', false);

// The Laravel recipe already shares storage/ and .env, marks writable dirs,
// runs migrations, and caches config/routes/views/events. composer install runs
// with --no-dev --optimize-autoloader via the recipe's composer options.

host('production')
    ->set('hostname', getenv('DEPLOY_HOST') ?: 'example.com')
    ->set('remote_user', getenv('DEPLOY_USER') ?: 'deploy')
    ->set('deploy_path', getenv('DEPLOY_PATH') ?: '/var/www/fund-manager')
    ->set('branch', getenv('DEPLOY_BRANCH') ?: 'main')
    ->set('healthcheck_url', getenv('DEPLOY_HEALTHCHECK_URL') ?: 'http://localhost/up');

// Staging review environment. Same recipe, separate host/path/branch so a
// branch can be put in front of the client before it reaches main. Setup and
// server provisioning: docs/staging.md.
host('staging')
    ->set('hostname', getenv('STAGING_HOST') ?: 'staging.example.com')
    ->set('remote_user', getenv('STAGING_USER') ?: 'deploy')
    ->set('deploy_path', getenv('STAGING_PATH') ?: '/var/www/fund-manager-staging')
    ->set('branch', getenv('STAGING_BRANCH') ?: 'main')
    // /up is exempt from the nginx basic auth (see deploy/nginx/
    // fund-manager-staging.conf) so this check does not need credentials.
    ->set('healthcheck_url', getenv('STAGING_HEALTHCHECK_URL') ?: 'http://localhost/up')
    // Each release carries its own node_modules (puppeteer + canvas ~500MB),
    // so staging keeps fewer than production's 5.
    ->set('keep_releases', 2);

// Build front-end assets on the server (public/build is gitignored). Requires
// Node on the host.
task('deploy:assets', function () {
    run('cd {{release_path}} && npm ci && npm run build');
})->desc('Install node modules and build Vite assets');

// Provision Chromium for Puppeteer when no system Chrome path is configured
// (PUPPETEER_EXECUTABLE_PATH unset). Idempotent.
task('deploy:puppeteer', function () {
    run('cd {{release_path}} && npx --yes puppeteer browsers install chrome');
})->desc('Provision Chromium for Puppeteer');

// Verify the freshly-linked release is healthy via the /up endpoint. If this
// fails the deploy fails and we roll back to the previous release.
task('deploy:health-check', function () {
    $url = get('healthcheck_url');
    run("curl -fsS --max-time 15 {$url} > /dev/null");
})->desc('Hit the /up health endpoint');

// Gracefully restart Horizon so workers pick up the new release's code.
task('artisan:horizon:terminate', function () {
    run('cd {{deploy_path}}/current && {{bin/php}} artisan horizon:terminate');
})->desc('Terminate Horizon so Supervisor restarts it on new code');

// Ordering: build assets + Chromium after composer, health-check + Horizon
// restart after the atomic symlink switch.
after('deploy:vendors', 'deploy:assets');
after('deploy:assets', 'deploy:puppeteer');
after('deploy:symlink', 'deploy:health-check');
after('deploy:health-check', 'artisan:horizon:terminate');

// Roll back automatically if the post-switch health check fails.
fail('deploy:health-check', 'rollback');

// Always unlock on failure.
after('deploy:failed', 'deploy:unlock');
