<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Services\ReportExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test report service can generate user stats.
     */
    public function test_generates_user_stats_report(): void
    {
        User::factory()->count(5)->create(['is_active' => true]);
        User::factory()->count(2)->create(['is_active' => false]);

        $service = new ReportExportService;
        $report = $service->getUserStatsReport(now()->subMonth(), now());

        $this->assertArrayHasKey('total_users', $report);
        $this->assertArrayHasKey('active_users', $report);
        $this->assertEquals(7, $report['total_users']);
        $this->assertEquals(5, $report['active_users']);
    }

    /**
     * Test report service can generate billing report.
     */
    public function test_generates_billing_report(): void
    {
        $user = User::factory()->create();

        Invoice::create([
            'invoice_number' => 'INV-001',
            'user_id' => $user->id,
            'amount' => 100,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        Invoice::create([
            'invoice_number' => 'INV-002',
            'user_id' => $user->id,
            'amount' => 50,
            'status' => 'pending',
        ]);

        $service = new ReportExportService;
        $report = $service->getBillingReport(now()->subMonth(), now());

        $this->assertArrayHasKey('total_invoices', $report);
        $this->assertArrayHasKey('total_revenue', $report);
        $this->assertEquals(2, $report['total_invoices']);
        $this->assertEquals(100, $report['total_revenue']);
        $this->assertEquals(50, $report['pending_amount']);
    }

    /**
     * Test report can be exported to CSV.
     */
    public function test_can_export_to_csv(): void
    {
        $service = new ReportExportService;
        $data = [
            'users' => ['total' => 10, 'active' => 8],
            'revenue' => 1000,
        ];

        $csv = $service->exportToCsv($data, 'test.csv');

        $this->assertStringContainsString('users.total', $csv);
        $this->assertStringContainsString('10', $csv);
        $this->assertStringContainsString('revenue', $csv);
    }

    /**
     * Test full report generation.
     */
    public function test_generates_full_report(): void
    {
        $service = new ReportExportService;
        $report = $service->getFullReport(now()->subMonth(), now());

        $this->assertArrayHasKey('report_period', $report);
        $this->assertArrayHasKey('generated_at', $report);
        $this->assertArrayHasKey('users', $report);
        $this->assertArrayHasKey('streams', $report);
        $this->assertArrayHasKey('billing', $report);
    }
}
