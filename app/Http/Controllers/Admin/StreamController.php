<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Server;
use App\Models\Stream;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Stream Management Controller
 * Handles CRUD operations for streams in the admin panel.
 */
class StreamController extends AdminController
{
    /**
     * Display a listing of streams.
     */
    public function index(Request $request): View
    {
        $query = Stream::query()->with('category', 'server');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('stream_url', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('last_check_status', $status);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $streams = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.streams.index', compact('streams', 'categories'));
    }

    /**
     * Show the form for creating a new stream.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $servers = Server::where('is_active', true)->orderBy('name')->get();

        return view('admin.streams.create', compact('categories', 'servers'));
    }

    /**
     * Store a newly created stream in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stream_url' => ['required', 'url', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'stream_icon' => ['nullable', 'url', 'max:2048'],
            'epg_channel_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'transcode_profile' => ['nullable', 'string', 'max:255'],
            'custom_sid' => ['nullable', 'string', 'max:255'],
        ]);

        $stream = Stream::create([
            'name' => $validated['name'],
            'stream_url' => $validated['stream_url'],
            'category_id' => $validated['category_id'],
            'server_id' => $validated['server_id'] ?? null,
            'stream_icon' => $validated['stream_icon'] ?? null,
            'epg_channel_id' => $validated['epg_channel_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'transcode_profile' => $validated['transcode_profile'] ?? null,
            'custom_sid' => $validated['custom_sid'] ?? null,
        ]);

        activity()
            ->performedOn($stream)
            ->causedBy(auth()->user())
            ->log('Stream created via admin panel');

        return redirect()->route('admin.streams.index')
            ->with('success', 'Stream created successfully.');
    }

    /**
     * Show the form for editing the specified stream.
     */
    public function edit(Stream $stream): View
    {
        $categories = Category::orderBy('name')->get();
        $servers = Server::where('is_active', true)->orderBy('name')->get();

        return view('admin.streams.edit', compact('stream', 'categories', 'servers'));
    }

    /**
     * Update the specified stream in storage.
     */
    public function update(Request $request, Stream $stream): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'stream_url' => ['required', 'url', 'max:2048'],
            'category_id' => ['required', 'exists:categories,id'],
            'server_id' => ['nullable', 'exists:servers,id'],
            'stream_icon' => ['nullable', 'url', 'max:2048'],
            'epg_channel_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'transcode_profile' => ['nullable', 'string', 'max:255'],
            'custom_sid' => ['nullable', 'string', 'max:255'],
        ]);

        $stream->update([
            'name' => $validated['name'],
            'stream_url' => $validated['stream_url'],
            'category_id' => $validated['category_id'],
            'server_id' => $validated['server_id'] ?? null,
            'stream_icon' => $validated['stream_icon'] ?? null,
            'epg_channel_id' => $validated['epg_channel_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'transcode_profile' => $validated['transcode_profile'] ?? null,
            'custom_sid' => $validated['custom_sid'] ?? null,
        ]);

        activity()
            ->performedOn($stream)
            ->causedBy(auth()->user())
            ->log('Stream updated via admin panel');

        return redirect()->route('admin.streams.index')
            ->with('success', 'Stream updated successfully.');
    }

    /**
     * Remove the specified stream from storage.
     */
    public function destroy(Stream $stream): RedirectResponse
    {
        $streamName = $stream->name;
        $stream->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_stream' => $streamName])
            ->log('Stream deleted via admin panel');

        return redirect()->route('admin.streams.index')
            ->with('success', 'Stream deleted successfully.');
    }
}
