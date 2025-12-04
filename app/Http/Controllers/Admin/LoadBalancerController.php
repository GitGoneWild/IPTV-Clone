<?php

namespace App\Http\Controllers\Admin;

use App\Models\LoadBalancer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Load Balancer Management Controller
 * Handles CRUD operations for load balancers in the admin panel.
 */
class LoadBalancerController extends AdminController
{
    /**
     * Display a listing of load balancers.
     */
    public function index(Request $request): View
    {
        $query = LoadBalancer::query();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $loadBalancers = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.load-balancers.index', compact('loadBalancers'));
    }

    /**
     * Show the form for creating a new load balancer.
     */
    public function create(): View
    {
        return view('admin.load-balancers.create');
    }

    /**
     * Store a newly created load balancer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hostname' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'api_key' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'weight' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $loadBalancer = LoadBalancer::create([
            'name' => $validated['name'],
            'hostname' => $validated['hostname'],
            'port' => $validated['port'] ?? 443,
            'api_key' => $validated['api_key'],
            'is_active' => $request->boolean('is_active', true),
            'weight' => $validated['weight'] ?? 10,
        ]);

        activity()
            ->performedOn($loadBalancer)
            ->causedBy(auth()->user())
            ->log('Load Balancer created via admin panel');

        return redirect()->route('admin.load-balancers.index')
            ->with('success', 'Load Balancer created successfully.');
    }

    /**
     * Show the form for editing the specified load balancer.
     */
    public function edit(LoadBalancer $loadBalancer): View
    {
        return view('admin.load-balancers.edit', compact('loadBalancer'));
    }

    /**
     * Update the specified load balancer in storage.
     */
    public function update(Request $request, LoadBalancer $loadBalancer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hostname' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'weight' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'hostname' => $validated['hostname'],
            'port' => $validated['port'] ?? 443,
            'is_active' => $request->boolean('is_active', true),
            'weight' => $validated['weight'] ?? 10,
        ];

        // Only update API key if provided
        if (! empty($validated['api_key'])) {
            $updateData['api_key'] = $validated['api_key'];
        }

        $loadBalancer->update($updateData);

        activity()
            ->performedOn($loadBalancer)
            ->causedBy(auth()->user())
            ->log('Load Balancer updated via admin panel');

        return redirect()->route('admin.load-balancers.index')
            ->with('success', 'Load Balancer updated successfully.');
    }

    /**
     * Remove the specified load balancer from storage.
     */
    public function destroy(LoadBalancer $loadBalancer): RedirectResponse
    {
        $loadBalancerName = $loadBalancer->name;
        $loadBalancer->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_load_balancer' => $loadBalancerName])
            ->log('Load Balancer deleted via admin panel');

        return redirect()->route('admin.load-balancers.index')
            ->with('success', 'Load Balancer deleted successfully.');
    }
}
