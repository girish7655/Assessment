<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body>
    <h1>Welcome, {{ @$notifiable->name }}!</h1>
    <p>Thank you for registering. Please login into your account by clicking the link below:</p>
    <a href="{{ $verificationUrl }}">Login</a>
</body>
</html>
