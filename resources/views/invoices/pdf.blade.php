<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: #f4f4f4;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #fbbf24;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #fbbf24;
        }
        .content {
            padding: 20px;
        }
        .invoice-details {
            margin: 20px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background: #f4f4f4;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        .table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #fbbf24;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }
        .status-paid { background: #10b981; }
        .status-unpaid { background: #ef4444; }
        .status-pending { background: #f59e0b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">⚡ TAKAPEDIA</div>
        <p>Platform Top-up Game Terpercaya</p>
    </div>
    
    <div class="content">
        <h2>INVOICE</h2>
        
        <div class="invoice-details">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%;">
                        <strong>Invoice No:</strong> {{ $order->invoice_no }}<br>
                        <strong>Tanggal:</strong> {{ $order->created_at->format('d F Y H:i') }}<br>
                        <strong>Status:</strong> 
                        <span class="status status-{{ strtolower($order->status) }}">
                            {{ $order->status }}
                        </span>
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <strong>Pelanggan:</strong><br>
                        {{ $order->email }}<br>
                        {{ $order->whatsapp }}
                    </td>
                </tr>
            </table>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $order->game->name }}</td>
                    <td>{{ $order->denomination->name }}</td>
                    <td>{{ $order->quantity }}</td>
                    <td>Rp {{ number_format($order->denomination->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td style="width: 70%; text-align: right;">Subtotal:</td>
                <td style="text-align: right;">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($order->discount > 0)
            <tr>
                <td style="text-align: right;">Diskon:</td>
                <td style="text-align: right; color: green;">-Rp {{ number_format($order->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td style="text-align: right;">Biaya Admin:</td>
                <td style="text-align: right;">Rp {{ number_format($order->fee, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="text-align: right; font-weight: bold; font-size: 16px;">TOTAL:</td>
                <td style="text-align: right; font-weight: bold; font-size: 16px; color: #fbbf24;">
                    Rp {{ number_format($order->total, 0, ',', '.') }}
                </td>
            </tr>
        </table>
        
        @if($order->payment)
        <div style="margin-top: 30px;">
            <h3>Informasi Pembayaran</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>Metode:</strong> {{ $order->payment->method }}</td>
                </tr>
                @if($order->payment->va_number)
                <tr>
                    <td><strong>VA Number:</strong> {{ $order->payment->va_number }}</td>
                </tr>
                @endif
                @if($order->payment->paid_at)
                <tr>
                    <td><strong>Dibayar pada:</strong> {{ $order->payment->paid_at->format('d F Y H:i') }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif
        
        <div style="margin-top: 30px;">
            <h3>Detail Akun Game</h3>
            <table style="width: 100%;">
                <tr>
                    <td><strong>User ID:</strong> {{ $order->account_id }}</td>
                </tr>
                @if($order->server_id)
                <tr>
                    <td><strong>Server:</strong> {{ $order->server_id }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    
    <div class="footer">
        <p>Terima kasih telah berbelanja di Takapedia!</p>
        <p>Jika ada pertanyaan, hubungi support@takapedia.com</p>
        <p>&copy; 2024 Takapedia. All rights reserved.</p>
    </div>
</body>
</html>