<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rent Invoice</title>
    <style>
        body {
            font-family: 'Roboto', serif; /* Replace 'YourFontName' with the desired font name */
        }
        h4 {
            margin: 0;
        }
        .w-full {
            width: 100%;
        }
        .w-half {
            width: 50%;
        }
        .margin-top {
            margin-top: 1.25rem;
        }
        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }
        table {
            width: 100%;
            border-spacing: 0;
        }
        table.products {
            font-size: 0.875rem;
            text-align: center;
        }
        table.products tr {
            background-color: rgb(96 165 250);
        }
        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }
        table tr.items {
            background-color: rgb(241 245 249);
        }
        table tr.items td {
            padding: 0.5rem;
        }
        .total {
            text-align: right;
            margin-top: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <table class="w-full">
        <tr>
            <td class="w-half">
                <h2>Invoice ID: {{ $record->rent_number }}</h2>
            </td>
        </tr>
    </table>
 
    <div class="margin-top">
        <table class="w-full">
            <tr>
                <td class="w-half">
                    <div><h4>To:</h4></div>
                    <div>{{ $record->user->name }}</div>
                    <div>{{ $record->address }}</div>
                    <div>Contact: {{ $record->contact }}</div>
                </td>
                <td class="w-half">
                    <div><h4>From:</h4></div>
                    <div>JAD marketing</div>
                    <div>Pan-Philippine Hwy, Sorsogon City, Sorsogon</div>
                    <!-- Add more sender information as needed -->
                </td>
            </tr>
        </table>
    </div>
 
    <div class="margin-top">
        <table class="products">
            <tr>
                <th>Qty</th>
                <th>Description</th>
                <th>Type</th>
                <th>Other Fees</th>
                <th>Unit Price</th>
                <th>Total Price</th>
                <th>Start</th>
                <th>End</th>
            </tr>
            <tr class="items">
                <td>{{ $record->quantity }}</td>
                <td>{{ $record->equipment->name }}</td>
                <td>{{ $record->type }}</td>
                <td>{{ $record->delivery_fee }}</td>
                <td>{{ $record->unit_price }}</td>
                <td>{{ $record->total_price }}</td>
                <td>{{ $record->delivery }}</td>
                <td>{{ $record->return }}</td>
            </tr>
            <!-- Add more rows if you have additional items in the record -->
        </table>
    </div>
 
    <div class="total margin-top">
        Total: PESO {{ $record->total_price }}
    </div>
 
    <div class="footer margin-top">
        <div>Thank you</div>
        <div>&copy; JAD marketing</div>
    </div>
</body>
</html>
