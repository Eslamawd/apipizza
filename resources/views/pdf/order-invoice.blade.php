<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; margin: 0; padding: 24px; }
        .invoice-wrapper { width: 100%; max-width: 800px; margin: 0 auto; }
        .invoice-header { margin-bottom: 24px; }
        .invoice-header h1 { margin: 0 0 8px 0; font-size: 24px; }
        .invoice-header .meta { color: #555; font-size: 14px; }
        .section { margin-bottom: 20px; }
        .section-title { margin-bottom: 10px; font-size: 16px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 10px 12px; border: 1px solid #ddd; }
        th { background: #f4f4f4; text-align: right; }
        td { vertical-align: top; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .totals td { border: none; padding: 6px 12px; }
        .totals .label { color: #555; }
        .totals .value { font-weight: bold; }
        .summary-card { border: 1px solid #ddd; padding: 12px; border-radius: 6px; }
        .summary-card p { margin: 6px 0; font-size: 14px; }
        .notes { font-size: 13px; color: #555; }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="invoice-header">
            <h1>فاتورة طلب رقم #{{ $order->id }}</h1>
            <div class="meta">
                <p>تاريخ الطلب: {{ optional($order->created_at)->format('Y-m-d h:i A') ?? 'N/A' }}</p>

        <div class="section summary-card">
            <p><strong>المطعم:</strong> {{ $order->restaurant->name ?? 'N/A' }}</p>
            <p><strong>نوع الطلب:</strong> {{ ucfirst($order->order_type ?? 'N/A') }}</p>
            <p><strong>العميل:</strong> {{ $order->customer_name ?? 'N/A' }}</p>
            <p><strong>الهاتف:</strong> {{ $order->phone ?? 'N/A' }}</p>
            <p><strong>العنوان:</strong> {{ $order->address ?? 'N/A' }}</p>
            @if(!empty($order->delivery_distance))
                <p><strong>المسافة:</strong> {{ number_format((float) $order->delivery_distance, 2) }} ميل</p>
            @endif
        </div>

        <div class="section">
            <div class="section-title">تفاصيل الطلب</div>
            <table>
                <thead>
                    <tr>
                        <th>البند</th>
                        <th>الكمية</th>
                        <th>سعر الوحدة</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderItems as $orderItem)
                        <tr>
                            <td>
                                <strong>{{ $orderItem->item->name ?? 'Item' }}</strong>
                                @if($orderItem->comment)
                                    <div style="margin-top:6px; font-size:13px; color:#555;">{{ $orderItem->comment }}</div>
                                @endif
                                @if($orderItem->options && $orderItem->options->count())
                                    <div style="margin-top:8px; font-size:13px; color:#555;">
                                        <strong>الإضافات:</strong>
                                        <ul style="margin:6px 0 0 14px; padding:0;">
                                            @foreach($orderItem->options as $opt)
                                                <li>
                                                    {{ $opt->name }}
                                                    @if(isset($opt->price) && $opt->price > 0)
                                                        (+{{ number_format((float) $opt->price, 2) }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </td>
                            <td class="text-right">{{ $orderItem->quantity }}</td>
                            <td class="text-right">{{ number_format((float) $orderItem->price, 2) }}</td>
                            <td class="text-right">{{ number_format((float) $orderItem->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section totals">
            <table>
                <tbody>
                    <tr>
                        <td class="label text-right">المجموع الفرعي:</td>
                        <td class="value text-left">{{ number_format((float) $order->subtotal_price ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label text-right">الخصم:</td>
                        <td class="value text-left">- {{ number_format((float) $order->discount_amount ?? 0, 2) }}</td>
                    </tr>
                    @if(isset($order->delivery_fee) && $order->delivery_fee !== null)
                        <tr>
                            <td class="label text-right">رسوم التوصيل:</td>
                            <td class="value text-left">{{ number_format((float) $order->delivery_fee, 2) }}</td>
                        </tr>
                    @endif
                    @if(isset($order->tax) && $order->tax !== null)
                        <tr>
                            <td class="label text-right">الضريبة:</td>
                            <td class="value text-left">{{ number_format((float) $order->tax, 2) }}</td>
                        </tr>
                    @endif
                    @if(isset($order->tips) && $order->tips !== null)
                        <tr>
                            <td class="label text-right">البخشيش:</td>
                            <td class="value text-left">{{ number_format((float) $order->tips, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label text-right"><strong>الإجمالي الكلي:</strong></td>
                        <td class="value text-left"><strong>{{ number_format((float) $order->total_price, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="section notes">
            <p>شكراً لطلبك معنا. يمكنك الاحتفاظ بهذه الفاتورة للمراجعة أو مشاركتها عند الحاجة.</p>
        </div>
    </div>
</body>
</html>
