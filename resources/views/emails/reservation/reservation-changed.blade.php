<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 16px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 16px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .center {
            text-align: center;
        }

        .card {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 16px;
        }

        .card h2 {
            font-size: 18px;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .buttons button.email {
            background-color: #007bff;
            color: white;
        }

        .buttons button.print {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="center">
        <img src="data:image/png;base64,{{ $qr_code ?? '' }}" alt="QR Code"
             id="qrcode" />
    </div>

    <div class="card">
        <h2>Reservation Details</h2>

        <p>We are resending this reservation details with correct reservation id. Please present this reservation at the counter. We apologize for this inconvenience. If you already Checked in, please ignore this email.</p>
        <div class="detail-item">
            <span>Reservation ID</span><span id="rsvnId">{{ $reservation->id ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Status</span><span id="status">{{ $reservation->status ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Lot Type</span><span id="lotType">{{ $reservation->lotType ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Parking Preference</span><span id="parkingPreference">{{ $reservation->parkingPreference ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Pick Up Time</span><span id="pickUpTime">{{ $reservation->pickUpTime ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Drop Off Time</span><span id="dropOffTime">{{ $reservation->dropOffTime ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Points</span><span id="points">{{ $reservation->points ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Payment Type</span><span id="paymentType">{{ $reservation->paymentType ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Payment Total</span><span id="paymentTotal">{{ $reservation->paymentTotal ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="card">
        <h2>Driver Details</h2>
        <div class="detail-item">
            <span>Driver Full Name</span><span id="driverFullName">{{$reservation->driver->full_name ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Email</span><span id="email">{{ $reservation->driver->email ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Phone</span><span id="phone">{{ $reservation->driver->phone ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="card">
        <h2>Vehicle Details</h2>
        <div class="detail-item">
            <span>Make & Model</span><span id="makeModel">{{ $reservation->vehicle->makeModel ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Plate</span><span id="plate">{{ $reservation->vehicle->plate ?? 'N/A' }}</span>
        </div>
        <div class="detail-item">
            <span>Vehicle Length</span><span id="vehicleLength">{{ $reservation->vehicle->vehicleLength ?? 'N/A' }}</span>
        </div>
    </div>
</div>

</body>

</html>
