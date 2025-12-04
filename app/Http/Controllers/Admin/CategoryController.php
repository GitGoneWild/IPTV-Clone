<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Admin Category Management Controller
 * Handles CRUD operations for categories in the admin panel.
 */
class CategoryController extends AdminController
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $query = Category::query()->withCount('streams');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories'],
            'category_type' => ['required', 'in:live,movie,series'],
        ]);

        $category = Category::create($validated);

        activity()
            ->performedOn($category)
            ->causedBy(auth()->user())
            ->log('Category created via admin panel');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'category_type' => ['required', 'in:live,movie,series'],
        ]);

        $category->update($validated);

        activity()
            ->performedOn($category)
            ->causedBy(auth()->user())
            ->log('Category updated via admin panel');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Check if category has streams
        if ($category->streams()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with existing streams.');
        }

        $categoryName = $category->name;
        $category->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_category' => $categoryName])
            ->log('Category deleted via admin panel');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
