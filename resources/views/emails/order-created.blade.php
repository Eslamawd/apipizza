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
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Order ID</div>
                                        <div class="value">#{{ $order->id }}</div>
                                    </td>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Restaurant</div>
                                        <div class="value">{{ $order->restaurant->name ?? 'N/A' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Table</div>
                                        <div class="value">{{ $order->table->name ?? 'N/A' }}</div>
                                    </td>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Type</div>
                                        <div class="value">{{ ucfirst($order->order_type ?? 'N/A') }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Status</div>
                                        <div class="value">{{ ucfirst($order->status) }}</div>
                                    </td>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Customer</div>
                                        <div class="value">{{ $order->customer_name ?? 'N/A' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Address</div>
                                        <div class="value">{{ $order->address ?? 'N/A' }}</div>
                                    </td>
                                    <td width="50%" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Phone</div>
                                        <div class="value">{{ $order->phone ?? 'N/A' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:6px 8px; vertical-align:top;">
                                        <div class="label">Location</div>
                                        @if(!empty($order->latitude) && !empty($order->longitude))
                                            <a
                                                href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                style="font-size:13px; color:#f97316; text-decoration:underline;"
                                            >
                                                View on map
                                            </a>
                                        @else
                                            <span style="color:#a59dc4;">N/A</span>
                                        @endif
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
                                    <td>
                                        {{ $orderItem->item->name ?? 'Item' }}
                                        @if($orderItem->comment)
                                            <div style="font-size:12px; color:#a59dc4;">Comment: {{ $orderItem->comment }}</div>
                                        @endif
                                        @if($orderItem->options && $orderItem->options->count())
                                            <div style="margin-top:6px; font-size:13px; color:#dcd6ff;">
                                                <strong style="color:#a59dc4;">Options:</strong>
                                                <ul style="margin:6px 0 0 0; padding:0 0 0 14px;">
                                                    @foreach($orderItem->options as $opt)
                                                        <li style="margin-bottom:4px; list-style: disc;">
                                                            {{ $opt->name }}
                                                            @if(isset($opt->price) && $opt->price > 0)
                                                                <span style="color:#f97316;"> (+{{ number_format((float) $opt->price, 2) }})</span>
                                                            @endif
                                                            @if(isset($opt->pivot) && isset($opt->pivot->position))
                                                                <span style="color:#a59dc4;"> — {{ ucfirst($opt->pivot->position) }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
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

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:18px;">
                            <tr>
                                <td style="padding:8px 0; color:#a59dc4; font-size:13px;">Subtotal:</td>
                                <td align="left" style="padding:8px 0; color:#ffffff; font-size:13px;">{{ number_format((float) $order->subtotal_price ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0; color:#8ee08a; font-size:13px;">Discount:</td>
                                <td align="left" style="padding:8px 0; color:#ffffff; font-size:13px;">- {{ number_format((float) $order->discount_amount ?? 0, 2) }}</td>
                            </tr>
                            @if(isset($order->delivery_distance) && $order->delivery_distance !== null)
                                <tr>
                                    <td style="padding:8px 0; color:#dcd6ff; font-size:13px;">Delivery distance:</td>
                                    <td align="left" style="padding:8px 0; color:#ffffff; font-size:13px;">{{ number_format((float) $order->delivery_distance, 2) }} mi</td>
                                </tr>
                            @endif
                            @if(isset($order->delivery_fee) && $order->delivery_fee !== null)
                                <tr>
                                    <td style="padding:8px 0; color:#f97316; font-size:13px;">Delivery fee:</td>
                                    <td align="left" style="padding:8px 0; color:#ffffff; font-size:13px;">{{ number_format((float) $order->delivery_fee, 2) }}</td>
                                </tr>
                            @endif
                            @if(isset($order->tax) && $order->tax !== null)
                                <tr>
                                    <td style="padding:8px 0; color:#dcd6ff; font-size:13px;">Tax:</td>
                                    <td align="left" style="padding:8px 0; color:#ffffff; font-size:13px;">{{ number_format((float) $order->tax, 2) }}</td>
                                </tr>
                            @endif
                            @if(isset($order->tips) && $order->tips !== null)
                                <tr>
                                    <td style="padding:8px 0; color:#dcd6ff; font-size:13px;">Tips:</td>
                                    <td align="left" style="padding:8px 0; color:#ffffff; font-size:13px;">{{ number_format((float) $order->tips, 2) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="2" class="total-row">Total: {{ number_format((float) $order->total_price, 2) }}</td>
                            </tr>
                        </table>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px;">
                         
                        </table>
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
