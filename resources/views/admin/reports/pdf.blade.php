<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; padding: 24px; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; font-weight: bold; color: #111827; }
        .header p { color: #6b7280; margin-top: 4px; }
        .sub-header { margin-bottom: 16px; display: flex; justify-content: space-between; }
        .sub-header span { font-size: 11px; color: #6b7280; }
        .stats { display: flex; gap: 12px; margin-bottom: 24px; }
        .stat-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; text-align: center; }
        .stat-box .label { font-size: 11px; color: #6b7280; text-transform: uppercase; }
        .stat-box .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        h2 { font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background-color: #f3f4f6; text-align: left; padding: 8px; font-size: 11px; text-transform: uppercase; color: #374151; border: 1px solid #e5e7eb; }
        td { padding: 8px; border: 1px solid #e5e7eb; font-size: 12px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 24px; left: 24px; right: 24px; text-align: center; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
        .brand { font-size: 12px; font-weight: bold; color: #374151; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ordora Coffee Shop</h1>
        <p>Laporan Penjualan</p>
    </div>

    <div class="sub-header">
        <span>Periode: <strong>{{ date('d M Y', strtotime($startDate)) }}</strong> - <strong>{{ date('d M Y', strtotime($endDate)) }}</strong></span>
        <span>Dicetak: {{ now()->format('d M Y H:i') }}</span>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="label">Revenue</div>
            <div class="value">Rp {{ number_format($stats['period_revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Paid Orders</div>
            <div class="value">{{ $stats['period_paid_orders'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Avg Per Order</div>
            <div class="value">Rp {{ number_format($stats['aov'], 0, ',', '.') }}</div>
        </div>
        <div class="stat-box">
            <div class="label">All-Time Revenue</div>
            <div class="value">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
        </div>
    </div>

    <h2>Produk Terlaris</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">#</th>
                <th>Produk</th>
                <th class="text-center">Terjual</th>
                <th class="text-right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts as $i => $prod)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $prod->name }}</td>
                <td class="text-center">{{ $prod->sold }}</td>
                <td class="text-right">Rp {{ number_format($prod->revenue, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-center">Tidak ada data untuk periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Riwayat Transaksi Lunas</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Meja</th>
                <th>Pembayaran</th>
                <th>Kasir</th>
                <th class="text-right">Total</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orderHistory as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->customer_name ?? '-' }}</td>
                <td>{{ $order->table->number ?? '-' }}</td>
                <td>{{ strtoupper($order->payment_method?->value ?? '-') }}</td>
                <td>{{ $order->user->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center">Tidak ada transaksi untuk periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span class="brand">Ordora Coffee Shop</span> &mdash; Sistem QR Ordering &copy; {{ now()->year }}
    </div>
</body>
</html>