@component('mail::message')
# Password Reset Code


A password request has been made on your account.
Use this {{ $data['reset_code'] }} code to reset your password.



Thanks,<br>
{{ config('app.name') }}
@endcomponent
