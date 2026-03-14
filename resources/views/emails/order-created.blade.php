<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order</title>
</head>
<body>
    <h2>New Order Received</h2>

    <p><strong>Order ID:</strong> #{{ $order->id }}</p>
    <p><strong>Restaurant:</strong> {{ $order->restaurant->name ?? 'N/A' }}</p>
    <p><strong>Table:</strong> {{ $order->table->name ?? 'N/A' }}</p>
    <p><strong>Type:</strong> {{ $order->order_type ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ $order->status }}</p>
    <p><strong>Total:</strong> {{ number_format((float) $order->total_price, 2) }}</p>

    <h3>Items</h3>
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th align="left">Item</th>
                <th align="left">Qty</th>
                <th align="left">Unit Price</th>
                <th align="left">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->orderItems as $orderItem)
                <tr>
                    <td>{{ $orderItem->item->name ?? 'Item' }}</td>
                    <td>{{ $orderItem->quantity }}</td>
                    <td>{{ number_format((float) $orderItem->price, 2) }}</td>
                    <td>{{ number_format((float) $orderItem->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
