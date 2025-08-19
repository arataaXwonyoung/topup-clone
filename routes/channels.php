<?php

use Illuminate\Support\Facades\Broadcast;

// Broadcast channels for real-time features (optional)

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{invoiceNo}', function ($user, $invoiceNo) {
    // Allow access to order channel if user owns the order or is admin
    $order = \App\Models\Order::where('invoice_no', $invoiceNo)->first();
    
    if (!$order) {
        return false;
    }
    
    return $user->id === $order->user_id || $user->is_admin;
});

Broadcast::channel('payment.{reference}', function ($user, $reference) {
    // Allow access to payment channel if user owns the payment
    $payment = \App\Models\Payment::where('reference', $reference)->first();
    
    if (!$payment) {
        return false;
    }
    
    return $user->id === $payment->order->user_id || $user->is_admin;
});