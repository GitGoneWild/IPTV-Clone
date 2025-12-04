<?php

namespace App\Http\Controllers\Admin;

use App\Models\Device;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Device Management Controller
 * Handles CRUD operations for devices in the admin panel.
 */
class DeviceController extends AdminController
{
    /**
     * Display a listing of devices.
     */
    public function index(Request $request): View
    {
        $query = Device::query()->with('user');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('device_name', 'like', "%{$search}%")
                    ->orWhere('user_agent', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by user
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $devices = $query->latest()->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.devices.index', compact('devices', 'users'));
    }

    /**
     * Show the form for editing the specified device.
     */
    public function edit(Device $device): View
    {
        return view('admin.devices.edit', compact('device'));
    }

    /**
     * Update the specified device in storage.
     */
    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'device_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $device->update([
            'device_name' => $validated['device_name'] ?? $device->device_name,
            'is_active' => $request->boolean('is_active'),
        ]);

        activity()
            ->performedOn($device)
            ->causedBy(auth()->user())
            ->log('Device updated via admin panel');

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified device from storage.
     */
    public function destroy(Device $device): RedirectResponse
    {
        $deviceName = $device->device_name ?? $device->id;
        $device->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_device' => $deviceName])
            ->log('Device deleted via admin panel');

        return redirect()->route('admin.devices.index')
            ->with('success', 'Device deleted successfully.');
    }
}
