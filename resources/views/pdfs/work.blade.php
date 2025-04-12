
<!DOCTYPE html>
<html>
<head>
    <title>Work Invoice</title>
    <style>
        body { font-family: 'Arial', sans-serif; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0,0,0,.15); }
        .invoice-header { border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .invoice-details { margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .total { font-size: 18px; font-weight: bold; margin-top: 20px; text-align: right; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="invoice-header">
            <h1>Work Details</h1>
            <p>Date: {{ $work->date }}</p>
            <p>Reference: #{{ $work->id }}</p>
        </div>
        
        <div class="invoice-details">
            <h3>{{ $work->name }}</h3>
            <p>{{ $work->description }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Diamond</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($work->workItems as $item)
                <tr>
                    <td>{{ $item->type }}</td>
                    <td>{{ $item->diamond }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->price * $item->diamond, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total">
            Total Amount: ${{ number_format($work->total, 2) }}
        </div>
    </div>
</body>
</html>
