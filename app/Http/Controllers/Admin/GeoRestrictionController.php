<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeoRestriction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Geo Restriction Management Controller
 * Handles CRUD operations for geo restrictions in the admin panel.
 */
class GeoRestrictionController extends AdminController
{
    /**
     * Display a listing of geo restrictions.
     */
    public function index(Request $request): View
    {
        $query = GeoRestriction::query();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('country_code', 'like', "%{$search}%");
            });
        }

        $geoRestrictions = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.geo-restrictions.index', compact('geoRestrictions'));
    }

    /**
     * Show the form for creating a new geo restriction.
     */
    public function create(): View
    {
        return view('admin.geo-restrictions.create');
    }

    /**
     * Store a newly created geo restriction in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'rule_type' => ['required', 'in:allow,block'],
        ]);

        $geoRestriction = GeoRestriction::create($validated);

        activity()
            ->performedOn($geoRestriction)
            ->causedBy(auth()->user())
            ->log('Geo Restriction created via admin panel');

        return redirect()->route('admin.geo-restrictions.index')
            ->with('success', 'Geo Restriction created successfully.');
    }

    /**
     * Show the form for editing the specified geo restriction.
     */
    public function edit(GeoRestriction $geoRestriction): View
    {
        return view('admin.geo-restrictions.edit', compact('geoRestriction'));
    }

    /**
     * Update the specified geo restriction in storage.
     */
    public function update(Request $request, GeoRestriction $geoRestriction): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'rule_type' => ['required', 'in:allow,block'],
        ]);

        $geoRestriction->update($validated);

        activity()
            ->performedOn($geoRestriction)
            ->causedBy(auth()->user())
            ->log('Geo Restriction updated via admin panel');

        return redirect()->route('admin.geo-restrictions.index')
            ->with('success', 'Geo Restriction updated successfully.');
    }

    /**
     * Remove the specified geo restriction from storage.
     */
    public function destroy(GeoRestriction $geoRestriction): RedirectResponse
    {
        $geoRestrictionName = $geoRestriction->name;
        $geoRestriction->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_geo_restriction' => $geoRestrictionName])
            ->log('Geo Restriction deleted via admin panel');

        return redirect()->route('admin.geo-restrictions.index')
            ->with('success', 'Geo Restriction deleted successfully.');
    }
}
