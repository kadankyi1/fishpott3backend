@component('mail::message')
# Password Reset Code


A password reset request has been made on your account.
Use 
<br><h2 style="color: black;">{{ $data['reset_code'] }}</h2>

to reset your password.



Thank you,<br>
{{ config('app.name') }}
@endcomponent