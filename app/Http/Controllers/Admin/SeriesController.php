<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Series;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Series Management Controller
 * Handles CRUD operations for series in the admin panel.
 */
class SeriesController extends AdminController
{
    /**
     * Display a listing of series.
     */
    public function index(Request $request): View
    {
        $query = Series::query()->with('category')->withCount('episodes');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('imdb_id', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $series = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('category_type', 'series')->orderBy('name')->get();

        return view('admin.series.index', compact('series', 'categories'));
    }

    /**
     * Show the form for creating a new series.
     */
    public function create(): View
    {
        $categories = Category::where('category_type', 'series')->orderBy('name')->get();

        return view('admin.series.create', compact('categories'));
    }

    /**
     * Store a newly created series in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'plot' => ['nullable', 'string'],
            'imdb_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
        ]);

        $series = Series::create($validated);

        activity()
            ->performedOn($series)
            ->causedBy(auth()->user())
            ->log('Series created via admin panel');

        return redirect()->route('admin.series.index')
            ->with('success', 'Series created successfully.');
    }

    /**
     * Show the form for editing the specified series.
     */
    public function edit(Series $series): View
    {
        $categories = Category::where('category_type', 'series')->orderBy('name')->get();

        return view('admin.series.edit', compact('series', 'categories'));
    }

    /**
     * Update the specified series in storage.
     */
    public function update(Request $request, Series $series): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'plot' => ['nullable', 'string'],
            'imdb_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
        ]);

        $series->update($validated);

        activity()
            ->performedOn($series)
            ->causedBy(auth()->user())
            ->log('Series updated via admin panel');

        return redirect()->route('admin.series.index')
            ->with('success', 'Series updated successfully.');
    }

    /**
     * Remove the specified series from storage.
     */
    public function destroy(Series $series): RedirectResponse
    {
        $seriesName = $series->name;
        $series->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_series' => $seriesName])
            ->log('Series deleted via admin panel');

        return redirect()->route('admin.series.index')
            ->with('success', 'Series deleted successfully.');
    }
}
