<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('order_type'), fn ($query) => $query->where('order_type', $request->string('order_type')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load('items.product', 'user');

        return view('orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,cancelled'],
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated.');
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {

        $validated = $request->validate([
            'method' => ['required', 'in:cash,card,gcash,bank_transfer'],
            'amount' => ['required', 'numeric', 'min:0'],
            // reference required when method is not cash
            'reference' => ['required_unless:method,cash', 'nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Create payment record
        $order->payments()->create([
            'user_id' => $request->user()->id,
            'method' => $validated['method'],
            'amount' => $validated['amount'],
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Mark order as completed
        $order->update(['status' => Order::STATUS_COMPLETED]);

        return back()->with('success', 'Payment recorded and order completed.');
    }
}
