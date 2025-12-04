<?php

namespace App\Http\Controllers\Admin;

use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Server Management Controller
 * Handles CRUD operations for servers in the admin panel.
 */
class ServerController extends AdminController
{
    /**
     * Display a listing of servers.
     */
    public function index(Request $request): View
    {
        $query = Server::query()->withCount('streams');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('host', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $servers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new server.
     */
    public function create(): View
    {
        return view('admin.servers.create');
    }

    /**
     * Store a newly created server in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_active' => ['boolean'],
            'max_clients' => ['nullable', 'integer', 'min:0'],
        ]);

        $server = Server::create([
            'name' => $validated['name'],
            'host' => $validated['host'],
            'port' => $validated['port'] ?? 80,
            'is_active' => $request->boolean('is_active', true),
            'max_clients' => $validated['max_clients'] ?? 1000,
        ]);

        activity()
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->log('Server created via admin panel');

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server created successfully.');
    }

    /**
     * Show the form for editing the specified server.
     */
    public function edit(Server $server): View
    {
        return view('admin.servers.edit', compact('server'));
    }

    /**
     * Update the specified server in storage.
     */
    public function update(Request $request, Server $server): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'is_active' => ['boolean'],
            'max_clients' => ['nullable', 'integer', 'min:0'],
        ]);

        $server->update([
            'name' => $validated['name'],
            'host' => $validated['host'],
            'port' => $validated['port'] ?? 80,
            'is_active' => $request->boolean('is_active', true),
            'max_clients' => $validated['max_clients'] ?? 1000,
        ]);

        activity()
            ->performedOn($server)
            ->causedBy(auth()->user())
            ->log('Server updated via admin panel');

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server updated successfully.');
    }

    /**
     * Remove the specified server from storage.
     */
    public function destroy(Server $server): RedirectResponse
    {
        // Check if server has streams
        if ($server->streams()->count() > 0) {
            return redirect()->route('admin.servers.index')
                ->with('error', 'Cannot delete server with existing streams.');
        }

        $serverName = $server->name;
        $server->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_server' => $serverName])
            ->log('Server deleted via admin panel');

        return redirect()->route('admin.servers.index')
            ->with('success', 'Server deleted successfully.');
    }
}
