<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test invoice number generation.
     */
    public function test_generates_unique_invoice_numbers(): void
    {
        $user = User::factory()->create();

        // Create first invoice
        $invoice1 = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 10,
            'status' => 'pending',
        ]);

        // Create second invoice
        $invoice2 = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 20,
            'status' => 'pending',
        ]);

        $this->assertStringStartsWith('INV-', $invoice1->invoice_number);
        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
    }

    /**
     * Test invoice creation.
     */
    public function test_can_create_invoice(): void
    {
        $user = User::factory()->create();

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 29.99,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('invoices', [
            'user_id' => $user->id,
            'amount' => 29.99,
            'status' => 'pending',
        ]);
    }

    /**
     * Test marking invoice as paid.
     */
    public function test_can_mark_invoice_as_paid(): void
    {
        $user = User::factory()->create();

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 29.99,
            'status' => 'pending',
        ]);

        $invoice->markAsPaid('stripe', 'ch_123456');

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    /**
     * Test overdue invoice detection.
     */
    public function test_detects_overdue_invoice(): void
    {
        $user = User::factory()->create();

        $overdueInvoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 29.99,
            'status' => 'pending',
            'due_date' => now()->subDay(),
        ]);

        $currentInvoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 29.99,
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        $this->assertTrue($overdueInvoice->isOverdue());
        $this->assertFalse($currentInvoice->isOverdue());
    }

    /**
     * Test invoice scopes.
     */
    public function test_invoice_scopes_work(): void
    {
        $user = User::factory()->create();

        Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 10,
            'status' => 'pending',
        ]);

        Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'amount' => 20,
            'status' => 'paid',
        ]);

        $this->assertEquals(1, Invoice::pending()->count());
        $this->assertEquals(1, Invoice::paid()->count());
    }
}
