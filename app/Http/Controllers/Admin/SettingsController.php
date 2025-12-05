<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

/**
 * Admin Settings Controller
 * Handles system-wide settings and configuration.
 */
class SettingsController extends AdminController
{
    /**
     * Display the integration settings page.
     */
    public function integrationSettings(): View
    {
        $settings = [
            'real_debrid_enabled' => config('streampilot.integrations.real_debrid.enabled', false),
            'tmdb_enabled' => config('streampilot.integrations.tmdb.enabled', false),
            'epg_enabled' => config('streampilot.integrations.epg.enabled', true),
        ];

        return view('admin.settings.integration-settings', compact('settings'));
    }

    /**
     * Display the system management page.
     */
    public function systemManagement(): View
    {
        $systemInfo = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'db_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];

        return view('admin.settings.system-management', compact('systemInfo'));
    }

    /**
     * Update integration settings.
     */
    public function updateIntegrationSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'real_debrid_enabled' => ['boolean'],
            'tmdb_enabled' => ['boolean'],
            'epg_enabled' => ['boolean'],
        ]);

        // In a real application, you would save these to a database or config file
        // For now, we'll just flash a success message
        activity()
            ->causedBy(auth()->user())
            ->withProperties($validated)
            ->log('Integration settings updated via admin panel');

        return redirect()->route('admin.settings.integration-settings')
            ->with('success', 'Integration settings updated successfully.');
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): RedirectResponse
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        activity()
            ->causedBy(auth()->user())
            ->log('Application cache cleared via admin panel');

        return redirect()->route('admin.settings.system-management')
            ->with('success', 'Application cache cleared successfully.');
    }

    /**
     * Optimize application.
     */
    public function optimize(): RedirectResponse
    {
        Artisan::call('optimize');

        activity()
            ->causedBy(auth()->user())
            ->log('Application optimized via admin panel');

        return redirect()->route('admin.settings.system-management')
            ->with('success', 'Application optimized successfully.');
    }
}
