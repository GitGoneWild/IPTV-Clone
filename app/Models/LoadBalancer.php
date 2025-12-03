<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadBalancer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'port',
        'use_ssl',
        'api_key',
        'is_active',
        'weight',
        'max_connections',
        'current_connections',
        'region',
        'status',
        'capabilities',
        'last_heartbeat_at',
        'last_check_at',
        'last_check_status',
        'response_time_ms',
        'cpu_usage',
        'memory_usage',
        'bandwidth_in',
        'bandwidth_out',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'use_ssl' => 'boolean',
            'weight' => 'integer',
            'port' => 'integer',
            'max_connections' => 'integer',
            'current_connections' => 'integer',
            'response_time_ms' => 'integer',
            'cpu_usage' => 'decimal:2',
            'memory_usage' => 'decimal:2',
            'bandwidth_in' => 'integer',
            'bandwidth_out' => 'integer',
            'capabilities' => 'array',
            'last_heartbeat_at' => 'datetime',
            'last_check_at' => 'datetime',
        ];
    }

    /**
     * Scope for active load balancers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'online');
    }

    /**
     * Scope for available load balancers (active and has capacity)
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'online')
            ->where(function ($q) {
                $q->whereNull('max_connections')
                    ->orWhereRaw('current_connections < max_connections');
            });
    }

    /**
     * Check if load balancer has capacity
     */
    public function hasCapacity(): bool
    {
        if (!$this->max_connections) {
            return true;
        }

        return $this->current_connections < $this->max_connections;
    }

    /**
     * Get load percentage
     */
    public function getLoadPercentageAttribute(): float
    {
        if (!$this->max_connections || $this->max_connections === 0) {
            return 0;
        }

        return round(($this->current_connections / $this->max_connections) * 100, 2);
    }

    /**
     * Check if load balancer is healthy
     */
    public function isHealthy(): bool
    {
        if (!$this->is_active || $this->status !== 'online') {
            return false;
        }

        // Check if we received a heartbeat in the last 5 minutes
        if ($this->last_heartbeat_at) {
            return $this->last_heartbeat_at->gt(now()->subMinutes(5));
        }

        return false;
    }

    /**
     * Update heartbeat timestamp
     */
    public function updateHeartbeat(array $stats = []): void
    {
        $updateData = ['last_heartbeat_at' => now()];

        if (isset($stats['current_connections'])) {
            $updateData['current_connections'] = $stats['current_connections'];
        }
        if (isset($stats['cpu_usage'])) {
            $updateData['cpu_usage'] = $stats['cpu_usage'];
        }
        if (isset($stats['memory_usage'])) {
            $updateData['memory_usage'] = $stats['memory_usage'];
        }
        if (isset($stats['bandwidth_in'])) {
            $updateData['bandwidth_in'] = $stats['bandwidth_in'];
        }
        if (isset($stats['bandwidth_out'])) {
            $updateData['bandwidth_out'] = $stats['bandwidth_out'];
        }
        if (isset($stats['response_time_ms'])) {
            $updateData['response_time_ms'] = $stats['response_time_ms'];
        }

        $this->update($updateData);
    }

    /**
     * Build base URL for this load balancer
     */
    public function buildBaseUrl(): string
    {
        // Use explicit use_ssl field if set, otherwise fallback to port-based detection
        $protocol = $this->use_ssl ?? ($this->port === 443) ? 'https' : 'http';
        $port = in_array($this->port, [80, 443]) ? '' : ':' . $this->port;
        
        return "{$protocol}://{$this->hostname}{$port}";
    }
}
