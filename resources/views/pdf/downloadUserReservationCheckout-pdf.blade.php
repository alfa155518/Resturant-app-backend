<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Reservation Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            color: #2c3e50;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 20px;
        }

        .logo-container {
            margin-bottom: 15px;
            text-align: center;
        }

        .logo-container img {
            max-width: 150px;
            height: auto;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .info-table td {
            padding: 12px;
            border: 1px solid #e1e1e1;
        }

        .info-table td:first-child {
            font-weight: bold;
            width: 40%;
            background-color: #f8f9fa;
            color: #2c3e50;
        }

        .title {
            color: #2c3e50;
            margin: 15px 0;
            font-size: 24px;
        }

        .restaurant-name {
            color: #3498db;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
            color: #7f8c8d;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        @if(isset($logoData))
            <div class="logo-container">
                <img src="data:image/png;base64,{{ $logoData }}" alt="Restaurant Logo">
            </div>
        @endif
        <h2 class="restaurant-name">Welcome to Gourmet Haven</h2>
        <h1 class="title">Reservation Checkout Details</h1>
    </div>

    <table class="info-table">
        <tr>
            <td>Checkout ID</td>
            <td>{{ $id }}</td>
        </tr>
        <tr>
            <td>Reservation ID</td>
            <td>{{ $reservation_id }}</td>
        </tr>
        <tr>
            <td>Table</td>
            <td>{{ $table_name }}</td>
        </tr>
        <tr>
            <td>Reservation Time</td>
            <td>{{ $reservation_time }}</td>
        </tr>
        <tr>
            <td>Number of Guests</td>
            <td>{{ $guest_count }}</td>
        </tr>
        <tr>
            <td>Payment Status</td>
            <td>{{ ucfirst($payment_status) }}</td>
        </tr>
        <tr>
            <td>Payment Method</td>
            <td>{{ ucfirst($payment_method) }}</td>
        </tr>
        <tr>
            <td>Payment Date</td>
            <td>{{ \Carbon\Carbon::parse($payment_date)->timezone('Africa/Cairo')->format('Y-m-d h:i A') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for dining with us!</p>
        <p>For any inquiries, please contact us at support@gourmethaven.com</p>
        <p>Generated on {{ now()->timezone('Africa/Cairo')->format('F j, Y \a\t g:i A') }} (Egypt Time)</p>
    </div>
</body>

</html>