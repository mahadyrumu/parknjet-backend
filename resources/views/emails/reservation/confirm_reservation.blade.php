<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Park n Jet Reservation Confirmation</title>
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
        <h3 style="text-align: center">Your Reservation Details for Park n Jet
            {{ str_replace('_', ' ', $data->reservation->lotType) }}</h3>
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
            <h3>Pricing Info</h3>
            <table>
                <tr>
                    <th class="table-th">Avg Daily Rate</th>
                    <td class="table-td">USD {{ $data->pricing->averageRate }}</td>
                </tr>
                <tr>
                    <th class="table-th">Base Price</th>
                    <td class="table-td">USD {{ $data->pricing->subTotal }}</td>
                </tr>
                <tr>
                    <th class="table-th">State Tax</th>
                    <td class="table-td">USD {{ $data->pricing->stateTax }}</td>
                </tr>
                <tr>
                    <th class="table-th">City Tax</th>
                    <td class="table-td">USD {{ $data->pricing->cityTax }}</td>
                </tr>
                <tr>
                    <th class="table-th">Port Fees</th>
                    <td class="table-td">USD {{ $data->pricing->portFee }}</td>
                </tr>

                <tr>
                    <th class="table-th">Applied Earned/Prepaid Days</th>
                    <td class="table-td"> {{ $data->discountDays }} days</td>
                </tr>
                <tr>
                    <th class="table-th">Discounted Amount</th>
                    <td class="table-td">USD {{ $data->pricing->discountAmount }}</td>
                </tr>
                <tr>
                    <th class="table-th">Total Price</th>
                    <td class="table-td">USD {{ $data->pricing->total }}</td>
                </tr>
            </table>
            <h3>Payment Info</h3>
            <table>
                <tr>
                    <th class="table-th">Pre Paid</th>
                    <td class="table-td">
                        @if ($data->reservation->isPaid)
                            USD {{ $data->pricing->total }}
                        @else
                            USD 0.00
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="table-th">Amount due</th>
                    <td class="table-td">
                        @if ($data->reservation->isPaid)
                            USD 0.00
                        @else
                            USD {{ $data->pricing->total }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <h3>How do I cancel a reservation that I made beforehand?</h3>
        <p>
            All cancellations must be submitted by midnight of the day before you are planning to drop-off your vehicle.
            To
            cancel or modify your reservation, please visit the link below:
            <!-- email us at service@parknjetSeaTac.com Include your name and
            reservation number which you received in your confirmation email. If you have an account with us, you can cancel
            it online. -->
        </p>
        <p><a href="{{ $data->summaryUrl }}">{{ $data->summaryUrl }}</a></p>
        <p>
            If you have prepaid online for your reservation, you will automatically receive a refund when you cancel
            your
            reservation. Please allow a couple of weeks after cancellation for the refund to appear on your statement.
        </p>
        <p>
            You can also email us at service@parknjetSeaTac.com Include your name and
            reservation number which you received in your confirmation email.
        </p>
        <h3>Important Info</h3>
        <h4>Facility and Shuttle Hours</h4>
        <p>
            Our facilities are open 24 hours, 7 days a week, 365 days a year. Our shuttles operate 24/7 as well.
        </p>
        <h4>Oversized Vehicles</h4>
        <p>
            In case vehicle size was not chosen correctly, vehicles longer than 17 feet (Including accessories) will be
            charged
            an additional $1.50/day and vehicles longer than 19 feet (Including accessories), pick Up trucks with crew
            cab, four
            doors, or dual rear wheels will be charged an additional $3.00/day.
        </p>
        <h3>Directions</h3>
        <h4>From I-5 :</h4>
        <ul>
            <li> Take exit #152.</li>
            <li> Follow 188th street 2 miles to West.</li>
            <li> Take left on 8th Ave S (the second traffic light after the tunnel).</li>
            <li> We are located on the left side.</li>
        </ul>
        <h4>From 509 :</h4>
        <ul>
            <li> Take the Normandy Park Exit onto DES MOINES MEMORIAL Drive.</li>
            <li> Take left at 8th Ave S. We are located on the left side.</li>
        </ul>
        <h4>From I-405 :</h4>
        <ul>
            <li> Take I- 5 South bound, take exit #152,</li>
            <li> Follow 188th street 2 miles to West.</li>
            <li> Take left on 8th Ave S (the second traffic light after the tunnel).</li>
            <li> We are located on the left side.</li>
        </ul>
        <h4>From Seatac Airport :</h4>
        <ul>
            <li>Take Right on International Blvd and turn right on 188th Street.</li>
            <li>Drive 1 mile take left on 8th Avenue South (the second traffic light after the tunnel).</li>
            <li>We are located on the left side.</li>
        </ul>
        <h3>When you arrive at a Park n Jet location to drop off your vehicle</h3>
        <ol>
            <li>
                Drive up to the drive thru check in window under the covered canopy
                and have your reservation ready.
            </li>
            <li>
                Please present this confirmation or have your reservation# ready.
            </li>
            <li>
                After checking in, you will be directed to park your car in a spot near the shuttle pick-up area.
            </li>
            <li>
                <p>
                    For Valet Service:
                <ol style='list-style-type: upper-roman;'>
                    <li>
                        Remove luggage to shuttle pick up area, our staff will be more than happy to assist you.
                    </li>
                    <li>
                        Return keys to the office.
                    </li>
                    <li>
                        Inform the staff that you are Ready For Pick-up (RFP)
                    </li>
                </ol>
                </p>
            </li>
            <li>
                After you have entered the shuttle, please tell the shuttle driver which airline you will be flying on,
                or if
                you know which island number you need to be dropped off by
            </li>
        </ol>
        <h3>When you return from your trip</h3>
        <ol>
            <li>
                <p>
                    Please call us at facility number on claim ticket (you were given at check in) after you have
                    claimed your
                    luggage.
                <ul>
                    <li>
                        Park n Jet Lot-1: 206-241-6600
                    </li>
                    <li>
                        Park n Jet Lot-2: 206-244-4500

                    </li>
                </ul>
                </p>
                <p>
                    If you do not have a phone, you can call us from a courtesy phone by dialing
                <ul>
                    <li>
                        *71 for Park n Jet Lot-1
                    </li>
                    <li>
                        *91 for Park n Jet Lot-2.
                    </li>
                </ul>
                </p>
            <li>
                <p>
                    You will be directed to enter
                <ol style='list-style-type: upper-roman;'>
                    <li>
                        Your 5 digit claim id. so please have this ready.
                    </li>
                    <li>
                        your island location (1 or 3? Usually whichever you were dropped off at)
                    </li>
                    <li>
                        <b>Number of minutes you will be ready in, please try to be accurate so our shuttle doesn't miss
                            you.
                            It takes 5-7 minutes to get to the islands from our lot so we have found it is most
                            beneficial to
                            you if you call us once you have your luggage and are about to cross the sky bridge.</b>
                    </li>
                </ol>
                </p>
            </li>
            <li>
                Please flag down the shuttle (raise hand) so that the shuttle driver knows that you need to be picked
                up.
                If the shuttle driver is unable to find you, they will call you back on the number that you called in
                with
            </li>
            <li>
                Show the shuttle driver your claim ticket, so he can check that you are headed to the correct lot.
            </li>
        </ol>
        <h3>Frequently Asked Questions</h3>
        <div>
            <p>
            <h4>When should I arrive at the parking facility?</h4>
            We request that you arrive at the parking facility 20-25 minutes before you want to arrive at the airport
            (not 25
            minutes before your flight departure). You may want to give yourself some extra time to account for traffic,
            weather, and unexpected delays etc.
            </p>
            <h4>When do I pay?</h4>
            <p>
                You would pay when you drop-off your vehicle.
            </p>
            <h4>When do I redeem a coupon?</h4>
            <p>
                Coupons will be redeemd at check in.
            </p>
            <h4>What if I return before the scheduled date?</h4>
            <p>
                On early pickups, customers will be given a free parking voucher for the unused days to use on future
                trips.
            </p>
            <h4>What if I return after the scheduled date?</h4>
            <p>
                On late pickups, customers will be charged the current daily rate without a discount after the scheduled
                pickup
                date.
            </p>
            <h4>How do I extend my reservation?</h4>
            <p>
                Please make new reservation for the extension time frame and send us an email at
                service@parknjetseatac.com.
                Reservations can only be made for the future time frames. Otherwise you might be charged regular rates
                without
                discount for unreserved late dates.
            </p>
            {{-- <p>If you're late, please extend your reservation, otherwise you'll be charged the walk-in rate. And if you
                arrive
                early, you'll get extra points on your next reservation. For now, we allow online payment for extend
                reservation.</p> --}}
        </div>
        <div class="footer">
            @if ($data->user)
                <p>
                    This reservation is created by Park n Jet Seatac member user {{ $data->user->full_name }} / id
                    {{ $data->user->id }}.
                </p>
            @endif
            <p>This email was sent to you by Park n Jet. For any queries, please contact us at <a
                    href="mailto:info@parknjetseatac.com">info@parknjetseatac.com</a>.</p>
        </div>
    </div>
</body>

</html>
