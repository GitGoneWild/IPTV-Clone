<?php

namespace App\Services;

use App\Models\ApiUsageLog;
use App\Models\ConnectionLog;
use App\Models\Invoice;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportExportService
{
    /**
     * Generate user statistics report.
     */
    public function getUserStatsReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'expired_users' => User::where('expires_at', '<', now())->count(),
            'resellers' => User::where('is_reseller', true)->count(),
            'admins' => User::where('is_admin', true)->count(),
            'users_by_status' => [
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ];
    }

    /**
     * Generate stream statistics report.
     */
    public function getStreamStatsReport(): array
    {
        return [
            'total_streams' => Stream::count(),
            'active_streams' => Stream::where('is_active', true)->count(),
            'online_streams' => Stream::where('last_check_status', 'online')->count(),
            'offline_streams' => Stream::where('last_check_status', 'offline')->count(),
            'unchecked_streams' => Stream::whereNull('last_check_status')->count(),
            'streams_by_type' => Stream::select('stream_type', DB::raw('count(*) as count'))
                ->groupBy('stream_type')
                ->pluck('count', 'stream_type')
                ->toArray(),
        ];
    }

    /**
     * Generate connection/usage report.
     */
    public function getConnectionReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $connections = ConnectionLog::whereBetween('started_at', [$startDate, $endDate]);

        // Calculate average duration in a database-agnostic way
        $completedConnections = ConnectionLog::whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('ended_at')
            ->get();

        $avgDuration = 0;
        if ($completedConnections->count() > 0) {
            $totalDuration = $completedConnections->sum(function ($conn) {
                return $conn->started_at->diffInSeconds($conn->ended_at);
            });
            $avgDuration = $totalDuration / $completedConnections->count();
        }

        // Get connections by day in a database-agnostic way
        $connectionsByDay = ConnectionLog::whereBetween('started_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($conn) => $conn->started_at->format('Y-m-d'))
            ->map(fn ($group) => $group->count())
            ->toArray();

        return [
            'total_connections' => $connections->count(),
            'unique_users' => (clone $connections)->distinct('user_id')->count('user_id'),
            'total_bytes_transferred' => (clone $connections)->sum('bytes_transferred'),
            'average_duration_seconds' => round($avgDuration),
            'connections_by_day' => $connectionsByDay,
        ];
    }

    /**
     * Generate API usage report.
     */
    public function getApiUsageReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $logs = ApiUsageLog::whereBetween('created_at', [$startDate, $endDate]);

        // Get requests by endpoint in a database-agnostic way
        $requestsByEndpoint = ApiUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('endpoint')
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->take(20)
            ->toArray();

        // Get requests by day in a database-agnostic way
        $requestsByDay = ApiUsageLog::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($log) => $log->created_at->format('Y-m-d'))
            ->map(fn ($group) => $group->count())
            ->toArray();

        return [
            'total_requests' => $logs->count(),
            'successful_requests' => (clone $logs)->whereBetween('response_status', [200, 299])->count(),
            'failed_requests' => (clone $logs)->where('response_status', '>=', 400)->count(),
            'average_response_time_ms' => (clone $logs)->avg('response_time_ms') ?? 0,
            'requests_by_endpoint' => $requestsByEndpoint,
            'requests_by_day' => $requestsByDay,
        ];
    }

    /**
     * Generate billing/revenue report.
     */
    public function getBillingReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $invoices = Invoice::whereBetween('created_at', [$startDate, $endDate]);

        // Get invoices by status in a database-agnostic way
        $invoicesByStatus = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('status')
            ->map(fn ($group) => $group->count())
            ->toArray();

        // Get revenue by month in a database-agnostic way
        $revenueByMonth = Invoice::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($inv) => $inv->paid_at->format('Y-m'))
            ->map(fn ($group) => $group->sum('amount'))
            ->toArray();

        return [
            'total_invoices' => $invoices->count(),
            'total_revenue' => (clone $invoices)->where('status', 'paid')->sum('amount'),
            'pending_amount' => (clone $invoices)->where('status', 'pending')->sum('amount'),
            'refunded_amount' => (clone $invoices)->where('status', 'refunded')->sum('amount'),
            'invoices_by_status' => $invoicesByStatus,
            'revenue_by_month' => $revenueByMonth,
        ];
    }

    /**
     * Generate full system report.
     */
    public function getFullReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return [
            'report_period' => [
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s'),
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'users' => $this->getUserStatsReport($startDate, $endDate),
            'streams' => $this->getStreamStatsReport(),
            'connections' => $this->getConnectionReport($startDate, $endDate),
            'api_usage' => $this->getApiUsageReport($startDate, $endDate),
            'billing' => $this->getBillingReport($startDate, $endDate),
        ];
    }

    /**
     * Export report to CSV format.
     */
    public function exportToCsv(array $data, string $filename): string
    {
        $csv = '';
        $flatData = $this->flattenArray($data);

        foreach ($flatData as $key => $value) {
            $csv .= '"'.str_replace('"', '""', $key).'","'.str_replace('"', '""', (string) $value)."\"\n";
        }

        return $csv;
    }

    /**
     * Flatten nested array for CSV export.
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
