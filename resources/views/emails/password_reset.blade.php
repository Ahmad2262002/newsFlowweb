<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .button { 
            display: inline-block; 
            padding: 10px 20px; 
            background-color: #3b82f6; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h2>Password Reset Request</h2>
    <p>You requested to reset your password. Click the button below to proceed:</p>
    
<a href="{{ $resetUrl }}" 
       style="display: inline-block; padding: 10px 20px; background-color: #3490dc; color: white; text-decoration: none; border-radius: 5px;">
        Reset Password
    </a>    
    <p>If you didn't request this, please ignore this email.</p>
    <p>This link will expire in 60 minutes.</p>
</body>
</html>