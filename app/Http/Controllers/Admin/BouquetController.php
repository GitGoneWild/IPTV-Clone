<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bouquet;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin Bouquet Management Controller
 * Handles CRUD operations for bouquets in the admin panel.
 */
class BouquetController extends AdminController
{
    /**
     * Display a listing of bouquets.
     */
    public function index(Request $request): View
    {
        $query = Bouquet::query()->withCount(['categories', 'users']);

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $bouquets = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.bouquets.index', compact('bouquets'));
    }

    /**
     * Show the form for creating a new bouquet.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.bouquets.create', compact('categories'));
    }

    /**
     * Store a newly created bouquet in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:bouquets'],
            'description' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        $bouquet = Bouquet::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Attach categories
        if (!empty($validated['categories'])) {
            $bouquet->categories()->attach($validated['categories']);
        }

        activity()
            ->performedOn($bouquet)
            ->causedBy(auth()->user())
            ->log('Bouquet created via admin panel');

        return redirect()->route('admin.bouquets.index')
            ->with('success', 'Bouquet created successfully.');
    }

    /**
     * Show the form for editing the specified bouquet.
     */
    public function edit(Bouquet $bouquet): View
    {
        $categories = Category::orderBy('name')->get();
        $bouquet->load('categories');

        return view('admin.bouquets.edit', compact('bouquet', 'categories'));
    }

    /**
     * Update the specified bouquet in storage.
     */
    public function update(Request $request, Bouquet $bouquet): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('bouquets')->ignore($bouquet->id)],
            'description' => ['nullable', 'string'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);

        $bouquet->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Sync categories
        $bouquet->categories()->sync($validated['categories'] ?? []);

        activity()
            ->performedOn($bouquet)
            ->causedBy(auth()->user())
            ->log('Bouquet updated via admin panel');

        return redirect()->route('admin.bouquets.index')
            ->with('success', 'Bouquet updated successfully.');
    }

    /**
     * Remove the specified bouquet from storage.
     */
    public function destroy(Bouquet $bouquet): RedirectResponse
    {
        $bouquetName = $bouquet->name;
        $bouquet->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_bouquet' => $bouquetName])
            ->log('Bouquet deleted via admin panel');

        return redirect()->route('admin.bouquets.index')
            ->with('success', 'Bouquet deleted successfully.');
    }
}
