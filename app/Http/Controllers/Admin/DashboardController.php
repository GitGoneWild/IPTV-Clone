<?php

namespace App\Http\Controllers\Admin;

use App\Models\Stream;
use App\Models\User;
use App\Models\Category;
use App\Models\Bouquet;
use Illuminate\View\View;

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
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'guest_users' => User::role('guest')->count(),
            'total_streams' => Stream::count(),
            'online_streams' => Stream::where('last_check_status', 'online')->count(),
            'total_categories' => Category::count(),
            'total_bouquets' => Bouquet::count(),
            'recent_users' => User::latest()->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
