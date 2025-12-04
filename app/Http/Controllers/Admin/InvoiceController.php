<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Invoice Management Controller
 * Handles CRUD operations for invoices in the admin panel.
 */
class InvoiceController extends AdminController
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $query = Invoice::query()->with('user');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.invoices.create', compact('users'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,paid,cancelled'],
            'due_date' => ['nullable', 'date'],
        ]);

        $invoice = Invoice::create([
            'user_id' => $validated['user_id'],
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->log('Invoice created via admin panel');

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.invoices.edit', compact('invoice', 'users'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,paid,cancelled'],
            'due_date' => ['nullable', 'date'],
        ]);

        $invoice->update($validated);

        activity()
            ->performedOn($invoice)
            ->causedBy(auth()->user())
            ->log('Invoice updated via admin panel');

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['deleted_invoice' => $invoiceNumber])
            ->log('Invoice deleted via admin panel');

        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }
}
