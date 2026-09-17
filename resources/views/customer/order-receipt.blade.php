<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->id }} - Ordora</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1a1a1a;
            font-size: 13px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #eee;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .brand {
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-weight: bold;
            color: #4f46e5;
            font-size: 14px;
        }

        h1 {
            font-size: 22px;
            font-weight: bold;
            margin: 4px 0 0;
        }

        .meta {
            margin-bottom: 20px;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 2px 0;
            font-size: 13px;
        }

        .label {
            color: #888;
            font-size: 11px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .items-table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            padding: 8px 0;
        }

        .items-table td {
            padding: 6px 0;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            text-align: right;
        }

        .total-row td {
            padding-top: 10px;
            font-weight: bold;
            font-size: 15px;
        }

        .summary {
            width: 100%;
            margin-top: 8px;
        }

        .summary td {
            padding: 3px 0;
            font-size: 13px;
        }

        .summary .amount {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 24px;
            padding-top: 12px;
            border-top: 1px solid #eee;
            font-size: 11px;
            color: #888;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            background: #d1fae5;
            color: #065f46;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="header">
        <div>
            <p class="brand">Ordora</p>
            <h1>Payment Receipt</h1>
        </div>
        <span class="status">{{ $order->status->value }}</span>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td class="label">Order</td>
                <td><strong>#{{ $order->id }}</strong></td>
                <td class="label">Date</td>
                <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Customer</td>
                <td>{{ $order->customer_name }}</td>
                <td class="label">Table</td>
                <td>{{ $order->table?->number ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Items</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>{{ $item->quantity }} &times; {{ $item->product->name }}</td>
                <td>$ {{ number_format($item->subtotal, 0, '.', ',') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total</td>
                <td>$ {{ number_format($order->total_price, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Payment method</td>
            <td class="amount">{{ $order->payment_method?->value ?? '-' }}</td>
        </tr>
        @if($order->paid_at)
        <tr>
            <td>Paid at</td>
            <td class="amount">{{ $order->paid_at->format('d M Y, H:i') }}</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        <p>Order token: {{ $order->order_token }}</p>
        <p>Thank you for dining with Ordora!</p>
    </div>
</body>

</html>