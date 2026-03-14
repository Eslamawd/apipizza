<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #0d0b14;
            direction: rtl;
        }
        table {
            border-collapse: collapse;
        }
        .container {
            width: 100%;
            max-width: 600px;
        }
        .header {
            background: linear-gradient(135deg, #f97316, #ea580c);
        }
        .content {
            padding: 28px;
            color: #e6e0f8;
            line-height: 1.8;
            font-size: 15px;
        }
        .label {
            color: #a59dc4;
            font-size: 13px;
        }
        .value {
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
        }
        .card {
            background: #1f1b2d;
            border: 1px solid #2e2840;
            border-radius: 12px;
            padding: 12px;
        }
        .items-table {
            width: 100%;
            border: 1px solid #2e2840;
            border-radius: 10px;
            overflow: hidden;
        }
        .items-table th {
            background: #221e32;
            color: #f1ecff;
            padding: 10px;
            font-size: 13px;
            border-bottom: 1px solid #2e2840;
        }
        .items-table td {
            color: #ddd6fe;
            padding: 10px;
            font-size: 13px;
            border-bottom: 1px solid #2e2840;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .total-row {
            margin-top: 16px;
            padding: 14px;
            border-radius: 10px;
            background: #221e32;
            border: 1px solid #2e2840;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
        }
        @media only screen and (max-width: 620px) {
            .container {
                width: 100% !important;
            }
            .content {
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>
<table align="center" width="100%" cellpadding="0" cellspacing="0" bgcolor="#0d0b14" style="padding:20px 0;">
    <tr>
        <td align="center">
            <table class="container" width="600" cellpadding="0" cellspacing="0" bgcolor="#161320" style="border-radius:16px; overflow:hidden;">
                <tr>
                    <td class="header" align="center" style="padding:22px; font-size:22px; font-weight:bold; color:#ffffff; border-radius:16px 16px 0 0;">
                        New Order Received
                    </td>
                </tr>

                <tr>
                    <td class="content">
                        <div class="card">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:6px 0;">
                                        <div class="label">Order ID</div>
                                        <div class="value">#{{ $order->id }}</div>
                                    </td>
                                    <td style="padding:6px 0;">
                                        <div class="label">Restaurant</div>
                                        <div class="value">{{ $order->restaurant->name ?? 'N/A' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;">
                                        <div class="label">Table</div>
                                        <div class="value">{{ $order->table->name ?? 'N/A' }}</div>
                                    </td>
                                    <td style="padding:6px 0;">
                                        <div class="label">Type</div>
                                        <div class="value">{{ ucfirst($order->order_type ?? 'N/A') }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;">
                                        <div class="label">Status</div>
                                        <div class="value">{{ ucfirst($order->status) }}</div>
                                    </td>
                                    <td style="padding:6px 0;">
                                        <div class="label">Customer</div>
                                        <div class="value">{{ $order->customer_name ?? 'N/A' }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <h3 style="margin:22px 0 12px 0; color:#f1ecff; font-size:17px;">Order Items</h3>

                        <table class="items-table" cellpadding="0" cellspacing="0">
                            <thead>
                            <tr>
                                <th align="right">Item</th>
                                <th align="right">Qty</th>
                                <th align="right">Unit Price</th>
                                <th align="right">Subtotal</th>
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
                                    <td colspan="4" style="text-align:center;">No items found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        <div class="total-row">
                            Total: {{ number_format((float) $order->total_price, 2) }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td bgcolor="#0d0b14" align="center" style="padding:20px; color:#8b7cae; font-size:12px; border-top:1px solid #2e2840;">
                        &copy; {{ date('Y') }} <strong>Pizza & Gyro Party</strong>. All rights reserved.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
