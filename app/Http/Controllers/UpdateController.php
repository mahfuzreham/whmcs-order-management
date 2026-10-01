<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\Update\UpdateService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function index(UpdateService $updates)
    {
        try {
            $info = $updates->check();
        } catch (\Throwable $e) {
            $info = ['error' => $e->getMessage(), 'current' => $updates->currentVersion()];
        }

        return view('admin.updates', compact('info'));
    }

    public function check(UpdateService $updates)
    {
        try {
            $info = $updates->check();
            return back()->with('update_info', $info)->with('success', $info['available'] ? 'A new update is available.' : 'You are already up to date.');
        } catch (\Throwable $e) {
            return back()->withErrors(['update' => $e->getMessage()]);
        }
    }

    public function install(UpdateService $updates)
    {
        $lock = storage_path('app/update.lock');

        if (file_exists($lock)) {
            return back()->withErrors(['update' => 'Another update is already running.']);
        }

        file_put_contents($lock, (string) now());

        try {
            $result = $updates->installLatest();
            return redirect()->route('admin.updates')->with('success', $result['message'].' Version: '.$result['version']);
        } catch (\Throwable $e) {
            return back()->withErrors(['update' => $e->getMessage()]);
        } finally {
            @unlink($lock);
        }
    }

    public function saveSettings(Request $request)
    {
        $d = $request->validate([
            'github_update_token' => 'nullable|string|max:1000',
            'update_timeout' => 'required|integer|min:10|max:120',
        ]);

        if (!empty($d['github_update_token'])) {
            AdminSetting::put('github_update_token', $d['github_update_token']);
        }
        AdminSetting::put('update_timeout', $d['update_timeout']);

        return back()->with('success', 'Update settings saved.');
    }
}
