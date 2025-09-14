<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        <p> Hello {{ $userName }}, </p>
        <p> To create new password for your Park N Jet account click on the button below
            within the next 48 hours.</p>
        <p style=" text-align: center"> <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}"
                class="button" style="color: white">Reset Password</a>
        </p>
        <div class="footer">
            <p>This email was sent to you by Park n Jet. For any queries, please contact us at <a
                    href="mailto:info@parknjetseatac.com">info@parknjetseatac.com</a>.</p>
        </div>
    </div>
</body>

</html>
