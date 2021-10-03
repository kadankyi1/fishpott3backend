@component('mail::message')
# Password Reset Code


A password request has been made on your account.
Use this {{ $data['reset_code'] }} passcode to complete your login.



Thanks,<br>
{{ config('app.name') }}
@endcomponent
