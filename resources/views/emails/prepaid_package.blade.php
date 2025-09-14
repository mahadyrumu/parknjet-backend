<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrePaid Package Purchase Confirmation</title>
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2,
        p {
            margin-top: 0;
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #B02D11;
        }

        /* Logo */
        .logo {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        /* Footer */
        .footer {
            text-align: center;
            color: #777777;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #f3f4f6;
            padding: 5px;
            text-align: left;
            width: 50%;
        }

        th {
            background-color: #f3f4f6;
            width: 40%;
        }

        .table-th {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            font-weight: 500;
            background-color: #F3F4F6;
        }

        .table-td {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img class="logo" src="{{ asset('images/logo.png') }}" alt="Park n Jet Logo">
        </div>
        <h3>Your PrePaid Package Details for Park n Jet {{ $data->lotId }}</h3>
        <div style="margin: 20px 0px">
            <table>
                <tr>
                    <th class="table-th">Lot Id</th>
                    <td class="table-td">{{ $data->lotId }}</td>
                </tr>
                <tr>
                    <th class="table-th">Days #</th>
                    <td class="table-td">{{ $data->days }} Days</td>
                </tr>
                <tr>
                    <th class="table-th">Expiration Date</th>
                    <td class="table-td">{{ $data->expirationDate }}</td>
                </tr>
                <tr>
                    <th class="table-th">Your savings</th>
                    <td class="table-td">${{ number_format((float) $data->savings, 2) }}</td>
                </tr>
                <tr>
                    <th class="table-th">Lot Address</th>
                    <td class="table-td">{{ $data->lotAddress }}</td>
                </tr>
            </table>
            <h3>Payment Info</h3>
            <table>
                <tr>
                    <th class="table-th">Pre Paid</th>
                    <td class="table-td">${{ $data->price }}</td>
                </tr>
            </table>
        </div>
        <div class="footer">
            <p>This email was sent to you by Park n Jet. For any queries, please contact us at <a
                    href="mailto:info@parknjetseatac.com">info@parknjetseatac.com</a>.</p>
        </div>
    </div>
</body>

</html>
