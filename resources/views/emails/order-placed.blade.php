<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body        { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container  { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header     { background: #2d3748; color: #fff; padding: 24px 32px; }
        .header h1  { margin: 0; font-size: 22px; }
        .header p   { margin: 4px 0 0; font-size: 13px; color: #a0aec0; }
        .body       { padding: 32px; }
        .body p     { color: #4a5568; line-height: 1.6; }
        table       { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th          { background: #f7fafc; color: #2d3748; text-align: left; padding: 10px 12px; font-size: 13px; border-bottom: 2px solid #e2e8f0; }
        td          { padding: 10px 12px; color: #4a5568; font-size: 14px; border-bottom: 1px solid #e2e8f0; }
        .total-row td { font-weight: bold; color: #2d3748; background: #f7fafc; }
        .status     { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; background: #c6f6d5; color: #276749; }
        .footer     { background: #f7fafc; padding: 20px 32px; text-align: center; color: #a0aec0; font-size: 12px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>Order Confirmed</h1>
        <p>Thank you for your order!</p>
    </div>

    <div class="body">
        <p>Hi <strong>{{ $order->user->name }}</strong>,</p>
        <p>Your order has been placed successfully and is now being processed.</p>

        <p>
            <strong>Order ID:</strong> #{{ $order->id }}<br>
            <strong>Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}<br>
            <strong>Status:</strong> <span class="status">{{ ucfirst($order->status) }}</span>
        </p>

        <h3 style="color:#2d3748; margin-bottom:8px;">Order Summary</h3>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td>${{ number_format($order->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <p style="color:#718096; font-size:13px;">
            If you have any questions about your order, please contact our support team.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Mini Order API. All rights reserved.
    </div>

</div>
</body>
</html>