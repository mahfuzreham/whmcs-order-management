<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->installed()) abort(404);
        return view('install');
    }

    public function configure(Request $request)
    {
        if ($this->installed()) abort(404);

        $data = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_url' => 'required|url|max:255',
            'db_host' => 'required|string|max:100',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_database' => 'required|string|max:100',
            'db_username' => 'required|string|max:100',
            'db_password' => 'nullable|string|max:255',
        ]);

        $envPath = base_path('.env');
        if (File::exists($envPath)) return back()->withErrors(['install' => 'A .env file already exists. Remove it only if this is a fresh installation.']);

        $key = 'base64:'.base64_encode(random_bytes(32));
        $env = 'APP_NAME="'.str_replace('"','',$data['app_name']).'"'.PHP_EOL.
            'APP_ENV=production'.PHP_EOL.
            'APP_KEY='.$key.PHP_EOL.
            'APP_DEBUG=false'.PHP_EOL.
            'APP_URL='.$data['app_url'].PHP_EOL.PHP_EOL.
            'LOG_CHANNEL=stack'.PHP_EOL.
            'LOG_LEVEL=error'.PHP_EOL.PHP_EOL.
            'DB_CONNECTION=mysql'.PHP_EOL.
            'DB_HOST='.$data['db_host'].PHP_EOL.
            'DB_PORT='.$data['db_port'].PHP_EOL.
            'DB_DATABASE='.$data['db_database'].PHP_EOL.
            'DB_USERNAME='.$data['db_username'].PHP_EOL.
            'DB_PASSWORD="'.addslashes($data['db_password']).'"'.PHP_EOL.PHP_EOL.
            'SESSION_DRIVER=file'.PHP_EOL.
            'CACHE_STORE=file'.PHP_EOL.
            'QUEUE_CONNECTION=database'.PHP_EOL;

        if (!File::put($envPath, $env)) return back()->withErrors(['install' => 'Unable to write .env. Check cPanel file permissions.']);

        try {
            Artisan::call('migrate', ['--force' => true]);
            File::ensureDirectoryExists(storage_path('app'));
            File::put(storage_path('app/installed'), now()->toIso8601String());
            return redirect()->route('install.done');
        } catch (\Throwable $e) {
            File::delete($envPath);
            return view('install', ['error' => 'Database setup failed. Check the database credentials and server logs.', 'envCreated' => false]);
        }
    }

    public function run()
    {
        // Installation is intentionally POST-only through configure().
        abort(404);
    }

    public function done()
    {
        if (!$this->installed()) abort(404);
        return view('install-done');
    }

    private function installed(): bool
    {
        return File::exists(storage_path('app/installed'));
    }
}
