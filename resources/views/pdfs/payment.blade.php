
<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt</title>
    <style>
        body { font-family: 'Arial', sans-serif; }
        .receipt-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0,0,0,.15); }
        .receipt-header { border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-details { margin-bottom: 40px; }
        .amount { font-size: 24px; font-weight: bold; color: #333; margin: 20px 0; }
        .meta { color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="receipt-header">
            <h1>Payment Receipt</h1>
            <p>Date: {{ $payment->date }}</p>
            <p>Reference: #{{ $payment->id }}</p>
        </div>
        
        <div class="receipt-details">
            <h3>Payment Details</h3>
            <p>From: {{ $payment->from }}</p>
            <p>Source: {{ $payment->source->name }}</p>
            <p>Description: {{ $payment->description }}</p>
        </div>

        <div class="amount">
            Amount Paid: ${{ number_format($payment->amount, 2) }}
        </div>

        <div class="meta">
            <p>Category: {{ $payment->category }}</p>
            <p>Generated on: {{ now() }}</p>
        </div>
    </div>
</body>
</html>
