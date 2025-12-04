<?php

namespace App\Services;

use App\Models\Bouquet;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Create an invoice for package (bouquet) assignment.
     *
     * @param  User  $user  The user to bill
     * @param  array  $bouquetIds  Array of bouquet IDs to assign
     * @param  float  $amount  Amount to charge
     * @param  string  $currency  Currency code (default: USD)
     * @param  User|null  $reseller  The reseller creating the invoice (optional)
     * @param  array  $options  Additional options:
     *                          - 'due_date' (DateTime|null): Invoice due date (default: 7 days from now)
     *                          - 'payment_method' (string|null): Payment method used
     *                          - 'description' (string): Description for the invoice (default: 'Package subscription')
     */
    public function createPackageInvoice(
        User $user,
        array $bouquetIds,
        float $amount,
        string $currency = 'USD',
        ?User $reseller = null,
        array $options = []
    ): Invoice {
        // Validate bouquet IDs
        if (empty($bouquetIds)) {
            throw new \InvalidArgumentException('At least one bouquet ID is required');
        }

        // Get bouquet details for line items
        $bouquets = Bouquet::whereIn('id', $bouquetIds)->get();

        if (count($bouquets) !== count($bouquetIds)) {
            throw new \InvalidArgumentException('One or more bouquet IDs are invalid');
        }

        $lineItems = $bouquets->map(function ($bouquet) {
            return [
                'bouquet_id' => $bouquet->id,
                'name' => $bouquet->name,
                'description' => $bouquet->description,
                'type' => $bouquet->category_type,
                'region' => $bouquet->region,
            ];
        })->toArray();

        // Create the invoice
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'reseller_id' => $reseller?->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'description' => $options['description'] ?? 'Package subscription',
            'line_items' => $lineItems,
            'due_date' => $options['due_date'] ?? now()->addDays(7),
            'payment_method' => $options['payment_method'] ?? null,
        ]);

        return $invoice;
    }

    /**
     * Process payment and assign packages to user.
     * This method is called when an invoice is paid.
     */
    public function processPaymentAndAssignPackages(
        Invoice $invoice,
        ?string $paymentMethod = null,
        ?string $paymentReference = null
    ): bool {
        return DB::transaction(function () use ($invoice, $paymentMethod, $paymentReference) {
            // Mark invoice as paid
            $invoice->markAsPaid($paymentMethod, $paymentReference);

            // Extract bouquet IDs from line items
            $bouquetIds = collect($invoice->line_items)
                ->pluck('bouquet_id')
                ->filter()
                ->toArray();

            if (empty($bouquetIds)) {
                return true; // No bouquets to assign
            }

            // Assign bouquets to user (replaces any previous bouquets)
            $user = $invoice->user;
            $user->bouquets()->sync($bouquetIds);

            // Upgrade guest to user if applicable
            if ($user->hasRole('guest') && $user->hasPackageAssigned()) {
                $user->upgradeFromGuestToUser();
            }

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy($invoice->reseller ?? $user)
                ->withProperties([
                    'invoice_id' => $invoice->id,
                    'bouquet_ids' => $bouquetIds,
                ])
                ->log('Packages assigned after payment');

            return true;
        });
    }

    /**
     * Create an invoice and immediately mark it as paid (for free packages or manual assignments).
     */
    public function assignFreePackage(User $user, array $bouquetIds, ?User $reseller = null): Invoice
    {
        $invoice = $this->createPackageInvoice(
            user: $user,
            bouquetIds: $bouquetIds,
            amount: 0.00,
            options: [
                'description' => 'Free package assignment',
                'payment_method' => 'manual',
            ],
            reseller: $reseller
        );

        $this->processPaymentAndAssignPackages($invoice, 'manual', 'free-package');

        return $invoice;
    }

    /**
     * Get billing summary for a user.
     */
    public function getUserBillingSummary(User $user): array
    {
        return [
            'total_invoices' => $user->invoices()->count(),
            'pending_invoices' => $user->invoices()->pending()->count(),
            'paid_invoices' => $user->invoices()->paid()->count(),
            'overdue_invoices' => $user->invoices()->overdue()->count(),
            'total_spent' => $user->invoices()->paid()->sum('amount'),
            'pending_amount' => $user->invoices()->pending()->sum('amount'),
            'assigned_packages' => $user->bouquets()->count(),
        ];
    }
}
