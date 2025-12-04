<?php

namespace App\Http\Controllers\Admin;

use App\Models\EpgSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin EPG Source Management Controller
 * Handles CRUD operations for EPG sources in the admin panel.
 */
class EpgSourceController extends AdminController
{
    /**
     * Display a listing of EPG sources.
     */
    public function index(Request $request): View
    {
        $query = EpgSource::query();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $epgSources = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.epg-sources.index', compact('epgSources'));
    }

    /**
     * Show the form for creating a new EPG source.
     */
    public function create(): View
    {
        return view('admin.epg-sources.create');
    }

    /**
     * Store a newly created EPG source in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $epgSource = EpgSource::create($validated);

        activity()
            ->performedOn($epgSource)
            ->causedBy(auth()->user())
            ->log('EPG Source created via admin panel');

        return redirect()->route('admin.epg-sources.index')
            ->with('success', 'EPG Source created successfully.');
    }

    /**
     * Show the form for editing the specified EPG source.
     */
    public function edit(EpgSource $epgSource): View
    {
        return view('admin.epg-sources.edit', compact('epgSource'));
    }

    /**
     * Update the specified EPG source in storage.
     */
    public function update(Request $request, EpgSource $epgSource): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
        ]);

        $epgSource->update($validated);

        activity()
            ->performedOn($epgSource)
            ->causedBy(auth()->user())
            ->log('EPG Source updated via admin panel');

        return redirect()->route('admin.epg-sources.index')
            ->with('success', 'EPG Source updated successfully.');
    }

    /**
     * Remove the specified EPG source from storage.
     */
    public function destroy(EpgSource $epgSource): RedirectResponse
    {
        $sourceName = $epgSource->name;
        $epgSource->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_epg_source' => $sourceName])
            ->log('EPG Source deleted via admin panel');

        return redirect()->route('admin.epg-sources.index')
            ->with('success', 'EPG Source deleted successfully.');
    }
}
