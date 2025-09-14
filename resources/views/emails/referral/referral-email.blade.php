<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Park n Jet Referral Program</title>
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

        /* Referral message */
        .referral-message {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        /* Additional content */
        .additional-content {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .additional-content h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        /* Button */
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #B02D11;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        .button:hover {
            background-color: #cc0000;
        }

        /* Footer */
        .footer {
            text-align: center;
            color: #777777;
        }

        .highlight {
            color: red;
        }

        .highlight4 {
            color: #0867ec;
            font-weight: bold;
        }

        /* Centered button container */
        .button-container {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img class="logo" src="{{ asset('images/logo.png') }}" alt="Park n Jet Logo">
        </div>
        <div class="referral-message">
            <p>Hello,</p>
            <p>We are Park N Jet 2 and offer off-site airport parking at Seatac International Airport. You were <span
                    class="highlight">referred to us by {{ $sender }} to receive FREE airport parking!</span></p>
            <p>At Park N Jet 2 you receive the best service for the best price:</p>
            <ul>
                <li>Valet and Self Park options</li>
                <li>Your spot is 100% guaranteed with a reservation</li>
                <li>We offer covered drive-thru check-in service</li>
                <li>Our lot is fully fenced, well-lit, and has complete video surveillance</li>
                <li>We offer a warm indoor waiting area with a fireplace, WiFi, and complimentary coffee and tea</li>
                <li>Prompt pick up from the airport</li>
                <li>We are continuously striving to perfect our service and we recently expanded Park N Jet Lot 2 to
                    <span class="highlight">over 1,300 stalls!</span>
                </li>
            </ul>
        </div>
        <div class="additional-content">
            <u>
                <h2>Limited Time Offer</h2>
            </u>
            <p>We would like to offer you a <span class="highlight">limited time promotion</span>. If you sign up to
                become a PNJ member we will offer you <span class="highlight4">ONE FREE DAY</span> to be credited when
                you make your first reservation! Please be sure to use the email address you are receiving this email at
                when you sign up for an account in order to receive this limited time offer.</p>
            <div class="button-container">
                <a href="{{ $url }}" class="button" style="color: white">Park N Jet Member Sign Up</a>
            </div>
            <p>We hope you choose Park N Jet for your parking needs and hope you take advantage of this limited time
                offer. See you soon!</p>
        </div>
        <div class="footer">
            <p>This email was sent to you by Park n Jet. For any queries, please contact us at <a
                    href="mailto:info@parknjetseatac.com">info@parknjetseatac.com</a>.</p>
        </div>
    </div>
</body>

</html>
