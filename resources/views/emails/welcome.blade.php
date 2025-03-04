<!DOCTYPE html>
<html>
<head>
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body>
    <div>
        <h1>Welcome {{ $user->name }}!</h1>
        
        <p>Thank you for registering with us. To get started, please click the button below to login into your account</p>
        
        <a href="{{ $verificationUrl }}" class="button">Login</a>
        
        <p>If you did not create an account, no further action is required.</p>
        
        <p>Best regards,<br>{{ config('app.name') }} Team</p>
    </div>
</body>
</html>
