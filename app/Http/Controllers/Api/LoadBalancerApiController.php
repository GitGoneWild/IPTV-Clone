<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoadBalancer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Load Balancer API Controller
 *
 * Handles load balancer registration, health reporting, and management.
 */
class LoadBalancerApiController extends Controller
{
    /**
     * Register a new load balancer
     *
     * @group Load Balancer Management
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hostname' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'region' => 'nullable|string|max:50',
            'max_connections' => 'nullable|integer|min:1',
            'weight' => 'nullable|integer|min:1|max:100',
            'capabilities' => 'nullable|array',
        ]);

        // Generate unique API key for this load balancer
        $apiKey = Str::random(64);

        $loadBalancer = LoadBalancer::create([
            'name' => $validated['name'],
            'hostname' => $validated['hostname'],
            'ip_address' => $validated['ip_address'],
            'port' => $validated['port'] ?? 80,
            'region' => $validated['region'] ?? null,
            'max_connections' => $validated['max_connections'] ?? null,
            'weight' => $validated['weight'] ?? 1,
            'capabilities' => $validated['capabilities'] ?? [],
            'api_key' => Hash::make($apiKey),
            'status' => 'offline',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Load balancer registered successfully',
            'data' => [
                'id' => $loadBalancer->id,
                'api_key' => $apiKey, // Only returned once during registration
                'name' => $loadBalancer->name,
            ],
            'timestamp' => now()->toIso8601String(),
        ], 201);
    }

    /**
     * Send heartbeat from load balancer
     *
     * @group Load Balancer Management
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $loadBalancer = $this->authenticateLoadBalancer($request);

        if (! $loadBalancer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        $validated = $request->validate([
            'current_connections' => 'nullable|integer|min:0',
            'cpu_usage' => 'nullable|numeric|min:0|max:100',
            'memory_usage' => 'nullable|numeric|min:0|max:100',
            'bandwidth_in' => 'nullable|integer|min:0',
            'bandwidth_out' => 'nullable|integer|min:0',
            'response_time_ms' => 'nullable|integer|min:0',
            'status' => ['nullable', Rule::in(['online', 'offline', 'maintenance'])],
        ]);

        $stats = [
            'current_connections' => $validated['current_connections'] ?? 0,
            'cpu_usage' => $validated['cpu_usage'] ?? null,
            'memory_usage' => $validated['memory_usage'] ?? null,
            'bandwidth_in' => $validated['bandwidth_in'] ?? 0,
            'bandwidth_out' => $validated['bandwidth_out'] ?? 0,
            'response_time_ms' => $validated['response_time_ms'] ?? null,
        ];

        $loadBalancer->updateHeartbeat($stats);

        if (isset($validated['status'])) {
            $loadBalancer->update(['status' => $validated['status']]);
        } elseif ($loadBalancer->status === 'offline') {
            $loadBalancer->update(['status' => 'online']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
            'data' => [
                'id' => $loadBalancer->id,
                'status' => $loadBalancer->status,
                'is_active' => $loadBalancer->is_active,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get load balancer configuration
     *
     * @group Load Balancer Management
     */
    public function getConfig(Request $request): JsonResponse
    {
        $loadBalancer = $this->authenticateLoadBalancer($request);

        if (! $loadBalancer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $loadBalancer->id,
                'name' => $loadBalancer->name,
                'hostname' => $loadBalancer->hostname,
                'port' => $loadBalancer->port,
                'region' => $loadBalancer->region,
                'weight' => $loadBalancer->weight,
                'max_connections' => $loadBalancer->max_connections,
                'is_active' => $loadBalancer->is_active,
                'capabilities' => $loadBalancer->capabilities,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get optimal load balancer for a client
     *
     * @group Load Balancer Management
     */
    public function getOptimal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'region' => 'nullable|string|max:50',
            'client_ip' => 'nullable|ip',
        ]);

        $query = LoadBalancer::available();

        // Prefer load balancers in the same region if specified
        if (isset($validated['region'])) {
            $query->orderByRaw('CASE WHEN region = ? THEN 0 ELSE 1 END', [$validated['region']]);
        }

        // Order by weight (higher weight = higher priority) and load percentage (lower = better)
        $loadBalancers = $query->get()->sortBy(function ($lb) {
            // Calculate a score: higher weight and lower load = better score
            $loadScore = $lb->load_percentage;
            $weightScore = 100 - $lb->weight; // Invert weight so lower is better for sorting

            return ($loadScore + $weightScore) / 2;
        });

        $optimal = $loadBalancers->first();

        if (! $optimal) {
            return response()->json([
                'success' => false,
                'message' => 'No available load balancers',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $optimal->id,
                'name' => $optimal->name,
                'hostname' => $optimal->hostname,
                'port' => $optimal->port,
                'base_url' => $optimal->buildBaseUrl(),
                'region' => $optimal->region,
                'load_percentage' => $optimal->load_percentage,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * List all active load balancers (Admin endpoint)
     *
     * @group Load Balancer Management
     */
    public function index(): JsonResponse
    {
        $loadBalancers = LoadBalancer::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $loadBalancers->map(function ($lb) {
                return [
                    'id' => $lb->id,
                    'name' => $lb->name,
                    'hostname' => $lb->hostname,
                    'ip_address' => $lb->ip_address,
                    'port' => $lb->port,
                    'region' => $lb->region,
                    'status' => $lb->status,
                    'is_active' => $lb->is_active,
                    'weight' => $lb->weight,
                    'current_connections' => $lb->current_connections,
                    'max_connections' => $lb->max_connections,
                    'load_percentage' => $lb->load_percentage,
                    'is_healthy' => $lb->isHealthy(),
                    'last_heartbeat_at' => $lb->last_heartbeat_at?->toIso8601String(),
                    'cpu_usage' => $lb->cpu_usage,
                    'memory_usage' => $lb->memory_usage,
                ];
            }),
            'count' => $loadBalancers->count(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get statistics for a specific load balancer
     *
     * @group Load Balancer Management
     */
    public function stats(int $id): JsonResponse
    {
        $loadBalancer = LoadBalancer::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $loadBalancer->id,
                'name' => $loadBalancer->name,
                'status' => $loadBalancer->status,
                'is_healthy' => $loadBalancer->isHealthy(),
                'current_connections' => $loadBalancer->current_connections,
                'max_connections' => $loadBalancer->max_connections,
                'load_percentage' => $loadBalancer->load_percentage,
                'cpu_usage' => $loadBalancer->cpu_usage,
                'memory_usage' => $loadBalancer->memory_usage,
                'bandwidth_in' => $loadBalancer->bandwidth_in,
                'bandwidth_out' => $loadBalancer->bandwidth_out,
                'response_time_ms' => $loadBalancer->response_time_ms,
                'last_heartbeat_at' => $loadBalancer->last_heartbeat_at?->toIso8601String(),
                'uptime_percentage' => $this->calculateUptimePercentage($loadBalancer),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Authenticate load balancer using API key
     *
     * Note: This implementation loads all load balancers for authentication.
     * For production with many load balancers, consider implementing a more
     * efficient authentication mechanism such as:
     * - Storing a hash of the API key with an indexed plain text prefix
     * - Using Laravel Sanctum tokens for load balancers
     * - Implementing a dedicated load balancer authentication service
     */
    private function authenticateLoadBalancer(Request $request): ?LoadBalancer
    {
        $apiKey = $request->header('X-LB-API-Key') ?? $request->input('api_key');

        if (! $apiKey) {
            return null;
        }

        // Cache load balancers to reduce database queries
        $loadBalancers = Cache::remember('load_balancers_auth', 300, function () {
            return LoadBalancer::all();
        });

        foreach ($loadBalancers as $lb) {
            if (Hash::check($apiKey, $lb->api_key)) {
                return $lb;
            }
        }

        return null;
    }

    /**
     * Calculate uptime percentage (placeholder - would need historical data)
     */
    private function calculateUptimePercentage(LoadBalancer $loadBalancer): float
    {
        // This is a simplified calculation
        // In production, you would track historical uptime data
        if ($loadBalancer->isHealthy()) {
            return 99.9; // Healthy LB
        }

        return 0.0; // Unhealthy LB
    }
}
