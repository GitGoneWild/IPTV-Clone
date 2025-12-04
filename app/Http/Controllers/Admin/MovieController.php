<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Movie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Movie Management Controller
 * Handles CRUD operations for movies in the admin panel.
 */
class MovieController extends AdminController
{
    /**
     * Display a listing of movies.
     */
    public function index(Request $request): View
    {
        $query = Movie::query()->with('category');

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

        $movies = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('category_type', 'movie')->orderBy('name')->get();

        return view('admin.movies.index', compact('movies', 'categories'));
    }

    /**
     * Show the form for creating a new movie.
     */
    public function create(): View
    {
        $categories = Category::where('category_type', 'movie')->orderBy('name')->get();

        return view('admin.movies.create', compact('categories'));
    }

    /**
     * Store a newly created movie in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stream_url' => ['required', 'url', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'plot' => ['nullable', 'string'],
            'imdb_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $movie = Movie::create($validated);

        activity()
            ->performedOn($movie)
            ->causedBy(auth()->user())
            ->log('Movie created via admin panel');

        return redirect()->route('admin.movies.index')
            ->with('success', 'Movie created successfully.');
    }

    /**
     * Show the form for editing the specified movie.
     */
    public function edit(Movie $movie): View
    {
        $categories = Category::where('category_type', 'movie')->orderBy('name')->get();

        return view('admin.movies.edit', compact('movie', 'categories'));
    }

    /**
     * Update the specified movie in storage.
     */
    public function update(Request $request, Movie $movie): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stream_url' => ['required', 'url', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'plot' => ['nullable', 'string'],
            'imdb_id' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'release_year' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 5)],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
        ]);

        $movie->update($validated);

        activity()
            ->performedOn($movie)
            ->causedBy(auth()->user())
            ->log('Movie updated via admin panel');

        return redirect()->route('admin.movies.index')
            ->with('success', 'Movie updated successfully.');
    }

    /**
     * Remove the specified movie from storage.
     */
    public function destroy(Movie $movie): RedirectResponse
    {
        $movieName = $movie->name;
        $movie->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_movie' => $movieName])
            ->log('Movie deleted via admin panel');

        return redirect()->route('admin.movies.index')
            ->with('success', 'Movie deleted successfully.');
    }
}
