@component('mail::message')
# Reset Your Password

We received a request to reset your password. You can reset it by clicking the button below.

@component('mail::button', ['url' => $actionUrl])
Reset Password
@endcomponent

If you did not request a password reset, no further action is required.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
