<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Mail\OrderConfirmation;
use App\Mail\PaymentReceived;
use App\Mail\OrderDelivered;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    public function sendOrderConfirmation(Order $order): void
    {
        // Send email
        if ($order->email) {
            Mail::to($order->email)->send(new OrderConfirmation($order));
        }
        
        // Send WhatsApp (via API like Fonnte, Wablas, etc)
        if ($order->whatsapp) {
            $this->sendWhatsApp($order->whatsapp, $this->formatOrderMessage($order));
        }
        
        // Send in-app notification
        if ($order->user) {
            $order->user->notify(new \App\Notifications\OrderCreated($order));
        }
    }
    
    public function sendPaymentReceived(Order $order): void
    {
        if ($order->email) {
            Mail::to($order->email)->send(new PaymentReceived($order));
        }
        
        if ($order->whatsapp) {
            $message = "Pembayaran diterima! Order #{$order->invoice_no} sedang diproses. Total: Rp " . number_format($order->total);
            $this->sendWhatsApp($order->whatsapp, $message);
        }
    }
    
    public function sendOrderDelivered(Order $order): void
    {
        if ($order->email) {
            Mail::to($order->email)->send(new OrderDelivered($order));
        }
        
        if ($order->whatsapp) {
            $message = "Order #{$order->invoice_no} telah berhasil! Item sudah dikirim ke akun game Anda.";
            $this->sendWhatsApp($order->whatsapp, $message);
        }
    }
    
    protected function sendWhatsApp(string $number, string $message): void
    {
        // Implementation depends on your WhatsApp API provider
        // Example using Fonnte:
        
        $token = config('services.whatsapp.token');
        $url = config('services.whatsapp.url');
        
        Http::withHeaders([
            'Authorization' => $token,
        ])->post($url, [
            'target' => $number,
            'message' => $message,
        ]);
    }
    
    protected function formatOrderMessage(Order $order): string
    {
        return sprintf(
            "Order Konfirmasi\n\nInvoice: %s\nGame: %s\nItem: %s\nTotal: Rp %s\n\nSilakan lakukan pembayaran sebelum %s\n\nLihat invoice: %s",
            $order->invoice_no,
            $order->game->name,
            $order->denomination->name,
            number_format($order->total),
            $order->expires_at->format('d M Y H:i'),
            route('invoices.show', $order->invoice_no)
        );
    }
}