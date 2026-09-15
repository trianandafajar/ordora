<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Order History - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-muted text-foreground">
    <div class="mx-auto max-w-md min-h-screen bg-background shadow-xl border-x relative">
        <header class="border-b">
            <div class="flex h-14 items-center justify-between px-4">
                <p class="font-bold">Ordora</p>
                <p class="text-xs text-muted-foreground">History</p>
            </div>
        </header>

        <main class="px-4 py-8 space-y-4">
            <h1 class="text-lg font-bold">Your Orders</h1>
            <div id="historyList" class="space-y-4">
                <p class="text-sm text-muted-foreground">Loading orders...</p>
            </div>
        </main>
    </div>

    <script>
        const historyList = document.getElementById('historyList');
        const orders = JSON.parse(localStorage.getItem('ordora-orders') || '[]');

        if (orders.length === 0) {
            historyList.innerHTML = '<p class="text-sm text-muted-foreground">No order history found.</p>';
        } else {
            historyList.innerHTML = orders.map(order => `
                <div class="rounded-xl border bg-card p-4 space-y-2">
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold">#${order.id}</span>
                        <span class="text-xs px-2 py-1 rounded bg-muted capitalize">${order.status}</span>
                    </div>
                    <div class="text-xs text-muted-foreground">${order.created_at} - Table ${order.table}</div>
                    <div class="font-medium">$ ${order.total.toLocaleString()}</div>
                    <a href="#" class="block text-center text-xs text-primary font-medium mt-2">View Details</a>
                </div>
            `).join('');
        }
    </script>
</body>

</html>