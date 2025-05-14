<!DOCTYPE html>
<html>
<head>
    <title>Your OTP Code</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .otp-code { 
            font-size: 24px; 
            font-weight: bold; 
            letter-spacing: 2px; 
            color: #2563eb;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registration Verification</h2>
        <p>Your OTP code is:</p>
        <div class="otp-code">{{ $otp }}</div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you didn't request this, please ignore this email.</p>
    </div>
</body>
</html>