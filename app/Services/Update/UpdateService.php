<?php

namespace App\Services\Update;

use App\Models\AdminSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class UpdateService
{
    public function currentVersion(): string
    {
        return (string) config('version.version', '1.0.0');
    }

    public function repository(): string
    {
        return (string) config('version.repository', 'mahfuzreham/whmcs-order-management');
    }

    protected function client()
    {
        $http = Http::timeout((int) AdminSetting::get('update_timeout', 30))
            ->acceptJson()
            ->withHeaders(['User-Agent' => 'WHMCS-Billing-Portal-Updater']);

        $token = AdminSetting::get('github_update_token', '');
        if ($token) {
            $http = $http->withToken($token);
        }

        return $http;
    }

    public function check(): array
    {
        $repo = $this->repository();
        $url = "https://api.github.com/repos/{$repo}/releases/latest";

        $response = $this->client()->get($url);
        if (!$response->successful()) {
            throw new RuntimeException('Unable to check GitHub updates. HTTP '.$response->status());
        }

        $release = $response->json();
        $tag = ltrim((string) ($release['tag_name'] ?? ''), 'v');

        if ($tag === '') {
            throw new RuntimeException('GitHub latest release has no version tag.');
        }

        return [
            'current' => $this->currentVersion(),
            'latest' => $tag,
            'available' => version_compare($tag, $this->currentVersion(), '>'),
            'name' => $release['name'] ?? $tag,
            'notes' => $release['body'] ?? '',
            'published_at' => $release['published_at'] ?? null,
            'html_url' => $release['html_url'] ?? null,
            'zipball_url' => $release['zipball_url'] ?? null,
            'assets' => collect($release['assets'] ?? [])->map(fn ($a) => [
                'name' => $a['name'] ?? '',
                'url' => $a['url'] ?? null,
                'size' => $a['size'] ?? 0,
                'content_type' => $a['content_type'] ?? '',
            ])->values()->all(),
        ];
    }

    public function installLatest(): array
    {
        $update = $this->check();

        if (!$update['available']) {
            return ['success' => true, 'message' => 'The portal is already up to date.', 'version' => $update['current']];
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP ZipArchive extension is required for automatic updates.');
        }

        $base = base_path();
        $backupDir = storage_path('app/updates/backups/'.date('Ymd_His'));
        File::ensureDirectoryExists($backupDir);

        $this->backup($backupDir);

        $zipPath = storage_path('app/updates/update-'.Str::random(16).'.zip');
        File::ensureDirectoryExists(dirname($zipPath));

        try {
            $url = $update['zipball_url'];
            if (!$url) {
                throw new RuntimeException('GitHub release does not provide a source archive.');
            }

            $token = AdminSetting::get('github_update_token', '');
            $request = Http::timeout((int) AdminSetting::get('update_timeout', 30))
                ->withHeaders(['User-Agent' => 'WHMCS-Billing-Portal-Updater', 'Accept' => 'application/vnd.github+json']);

            if ($token) {
                $request = $request->withToken($token);
            }

            $response = $request->get($url);
            if (!$response->successful()) {
                throw new RuntimeException('Unable to download update package. HTTP '.$response->status());
            }

            File::put($zipPath, $response->body());

            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new RuntimeException('Downloaded update package is not a valid ZIP archive.');
            }

            $root = $zip->getNameIndex(0);
            if (!$root || !str_ends_with($root, '/')) {
                throw new RuntimeException('Invalid GitHub archive structure.');
            }

            $extractDir = storage_path('app/updates/extract-'.Str::random(12));
            File::ensureDirectoryExists($extractDir);
            $zip->extractTo($extractDir);
            $zip->close();

            $source = rtrim($extractDir.'/'.$root, '/');
            if (!File::exists($source.'/artisan')) {
                throw new RuntimeException('Update archive does not contain a valid Laravel application.');
            }

            $this->overlay($source, $base);

            $this->runMigrations();

            @unlink($zipPath);
            File::deleteDirectory($extractDir);

            return [
                'success' => true,
                'message' => 'Update installed successfully.',
                'version' => $update['latest'],
                'backup' => $backupDir,
            ];
        } catch (\Throwable $e) {
            $this->restore($backupDir, $base);
            @unlink($zipPath);
            throw new RuntimeException('Update failed and the previous files were restored: '.$e->getMessage(), 0, $e);
        }
    }

    protected function backup(string $dir): void
    {
        foreach (['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'composer.json', 'composer.lock', 'artisan', 'VERSION'] as $item) {
            $source = base_path($item);
            if (!File::exists($source)) continue;

            $destination = $dir.'/'.$item;
            if (File::isDirectory($source)) {
                File::copyDirectory($source, $destination);
            } else {
                File::ensureDirectoryExists(dirname($destination));
                File::copy($source, $destination);
            }
        }
    }

    protected function restore(string $dir, string $base): void
    {
        if (!File::exists($dir)) return;

        foreach (['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes'] as $item) {
            $backup = $dir.'/'.$item;
            if (!File::exists($backup)) continue;

            File::deleteDirectory($base.'/'.$item);
            File::copyDirectory($backup, $base.'/'.$item);
        }

        foreach (['composer.json', 'composer.lock', 'artisan', 'VERSION'] as $item) {
            if (File::exists($dir.'/'.$item)) {
                File::copy($dir.'/'.$item, $base.'/'.$item);
            }
        }
    }

    protected function overlay(string $source, string $base): void
    {
        foreach (['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes'] as $item) {
            if (File::exists($source.'/'.$item)) {
                File::copyDirectory($source.'/'.$item, $base.'/'.$item);
            }
        }

        foreach (['composer.json', 'composer.lock', 'artisan', 'VERSION'] as $item) {
            if (File::exists($source.'/'.$item)) {
                File::copy($source.'/'.$item, $base.'/'.$item);
            }
        }

        // Never replace runtime/user data or the production .env.
        File::ensureDirectoryExists($base.'/storage');
    }

    protected function runMigrations(): void
    {
        $php = PHP_BINARY;
        $artisan = base_path('artisan');
        $command = escapeshellarg($php).' '.escapeshellarg($artisan).' migrate --force';

        $output = [];
        $code = 0;
        exec($command.' 2>&1', $output, $code);

        if ($code !== 0) {
            throw new RuntimeException('Database migration failed: '.implode("\n", $output));
        }
    }
}
