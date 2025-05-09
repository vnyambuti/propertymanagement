<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .receipt-details {
            margin-bottom: 30px;
        }
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-details table tr td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .receipt-details table tr td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment Receipt</h1>
            <p>Receipt #{{ $payment->id }}</p>
        </div>

        <div class="receipt-details">
            <h2>Payment Details</h2>
            <table>
                <tr>
                    <td>Tenant:</td>
                    <td>{{ $tenant->name }}</td>
                </tr>
                <tr>
                    <td>Property:</td>
                    <td>{{ $property->name }}</td>
                </tr>
                <tr>
                    <td>Unit:</td>
                    <td>{{ $unit->unit_number }}</td>
                </tr>
                <tr>
                    <td>Amount Paid:</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Payment Date:</td>
                    <td>{{ $payment->payment_date }}</td>
                </tr>
                <tr>
                    <td>Payment Method:</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for your payment. For any questions regarding this receipt, please contact us.</p>
        </div>
    </div>
</body>
</html>
