<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Park n Jet Reservation Cancelled</title>
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
        <h3>Your reservation #{{ $data->reservation->id }} has been canceled for Park n Jet
            {{ str_replace('_', ' ', $data->reservation->lotType) }}</h3>
        <h3 style="text-align: center">Canceled reservation details:</h3>
        <div style="margin: 20px 0px">
            <table>
                <tr>
                    <th class="table-th">Reservation #</th>
                    <td class="table-td">{{ $data->reservation->id }}</td>
                </tr>
                <tr>
                    <th class="table-th">Driver Name</th>
                    <td class="table-td">{{ $data->reservation->driver->full_name }}</td>
                </tr>
                <tr>
                    <th class="table-th">Email</th>
                    <td class="table-td">{{ $data->reservation->driver->email }}</td>
                </tr>
                <tr>
                    <th class="table-th">Phone</th>
                    <td class="table-td">{{ $data->reservation->driver->phone }}</td>
                </tr>
                <tr>
                    <th class="table-th">Address</th>
                    <td class="table-td">{{ $data->lotAddress }}</td>
                </tr>
            </table>
            <h3>Vehicle Info</h3>
            <table>
                <tr>
                    <th class="table-th">Vehicle Make &amp; Model</th>
                    <td class="table-td">{{ $data->reservation->vehicle->makeModel }}</td>
                </tr>
                <tr>
                    <th class="table-th">Vehicle Plate</th>
                    <td class="table-td">{{ $data->reservation->vehicle->plate }}</td>
                </tr>
                <tr>
                    <th class="table-th">Vehicle Size</th>
                    <td class="table-td">
                        @if ($data->reservation->vehicle->vehicleLength == 'STANDARD')
                            Under 17ft.
                        @elseif($data->reservation->vehicle->vehicleLength == 'LARGE')
                            17-19ft.
                        @elseif($data->reservation->vehicle->vehicleLength == 'EXTRA_LARGE')
                            19-21ft.
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="table-th">Return Flight</th>
                    <td class="table-td">
                        {{ $data->reservation->returnAirline }}{{ $data->reservation->returnFlightNo }}
                    </td>
                </tr>
            </table>
            <table style="margin-top: 30px">
                <tr>
                    <th class="table-th">Duration of Stay</th>
                    <td class="table-td">{{ $data->durationInDay }} days</td>
                </tr>
                <tr>
                    <th class="table-th">Drop Off</th>
                    <td class="table-td">{{ date('F j, Y, g:i a', strtotime($data->dropOffDate)) }}</td>
                </tr>
                <tr>
                    <th class="table-th">Pick Up</th>
                    <td class="table-td">{{ date('F j, Y, g:i a', strtotime($data->pickUpDate)) }}</td>
                </tr>
                <tr>
                    <th class="table-th">Preference</th>
                    <td class="table-td">{{ $data->reservation->parkingPreference }}</td>
                </tr>
                <tr>
                    <th class="table-th">Persons</th>
                    <td class="table-td">{{ $data->reservation->paxCount }}</td>
                </tr>
            </table>
        </div>
        <div style="margin: 20px 0px">
            <p style="margin-bottom: 10px">For more detail info about the reservation click :
            </p>
            <p>
                <a href="{{ $data->summaryUrl }}">{{ $data->summaryUrl }}</a>
            </p>
        </div>
        <div class="footer">
            <p>This email was sent to you by Park n Jet. For any queries, please contact us at <a
                    href="mailto:info@parknjetseatac.com">info@parknjetseatac.com</a>.</p>
        </div>
    </div>
</body>

</html>
