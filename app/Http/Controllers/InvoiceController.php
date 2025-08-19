<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
    public function show($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->with(['game', 'denomination', 'payment', 'user'])
            ->firstOrFail();

        // Generate QR Code for QRIS
        $qrCode = null;
        if ($order->payment && $order->payment->method === 'QRIS' && $order->payment->qris_string) {
            $qrCode = base64_encode(
                QrCode::format('png')->size(250)->generate($order->payment->qris_string)
            );
        }

        return view('invoices.show', compact('order', 'qrCode'));
    }

    public function download($invoiceNo)
    {
        $order = Order::where('invoice_no', $invoiceNo)
            ->with(['game', 'denomination', 'payment', 'user'])
            ->firstOrFail();

        $pdf = Pdf::loadView('invoices.pdf', compact('order'));

        return $pdf->download('invoice-' . $order->invoice_no . '.pdf');
    }
}
