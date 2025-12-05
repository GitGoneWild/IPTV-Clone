<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bouquet;
use App\Models\Category;
use App\Models\Stream;
use App\Models\User;
use Illuminate\View\View;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/**
 * Admin Dashboard Controller
 * Handles the main admin dashboard and statistics.
 */
class DashboardController extends AdminController
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // Check if guest role exists before querying
        $guestUsersCount = 0;
        try {
            $guestUsersCount = User::role('guest')->count();
        } catch (RoleDoesNotExist $e) {
            // Guest role doesn't exist, use default value of 0
        }

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'guest_users' => $guestUsersCount,
            'total_streams' => Stream::count(),
            'online_streams' => Stream::where('last_check_status', 'online')->count(),
            'total_categories' => Category::count(),
            'total_bouquets' => Bouquet::count(),
            'recent_users' => User::latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
