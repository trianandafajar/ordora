<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->id }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #000;
            width: 80mm;
            margin: 0 auto;
            padding: 16px 12px;
        }

        .center {
            text-align: center;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 0;
        }

        .muted {
            color: #555;
            font-size: 11px;
            margin: 2px 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 3px 0;
        }

        .item-name {
            font-weight: bold;
        }

        .item-detail {
            color: #555;
            font-size: 11px;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            margin-top: 14px;
            font-size: 11px;
        }

        @media print {
            body {
                padding: 8px;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="center">
        <h1 class="brand">{{ strtoupper(config('app.name')) }}</h1>
        <p class="muted">Order #{{ $order->id }}</p>
        <p class="muted">{{ ($order->paid_at ?? $order->created_at)?->format('d M Y, H:i') }}</p>
    </div>

    <div class="divider"></div>

    <div class="row"><span>Customer</span><span>{{ $order->customer_name ?? '-' }}</span></div>
    <div class="row"><span>Table</span><span>{{ $order->table?->number ?? '-' }}</span></div>

    <div class="divider"></div>

    @foreach($order->orderItems as $item)
    <div class="item-name">{{ $item->product->name }}</div>
    <div class="row item-detail">
        <span>{{ $item->quantity }} x $ {{ number_format($item->product->price, 2) }}</span>
        <span>$ {{ number_format($item->quantity * $item->product->price, 2) }}</span>
    </div>
    @endforeach

    <div class="divider"></div>

    <div class="row total">
        <span>TOTAL</span>
        <span>$ {{ number_format($order->total_price, 2) }}</span>
    </div>
    <div class="row">
        <span>Payment</span>
        <span>{{ $order->payment_method?->label() ?? '-' }}</span>
    </div>

    <div class="divider"></div>

    <div class="center footer">
        <p>Thank you for your order!</p>
        <p>See you again</p>
    </div>
</body>

</html>