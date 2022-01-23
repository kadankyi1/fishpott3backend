@component('mail::message')
# FishPott System Alert


{{ $data['title'] }} 
<br><h5 style="color: black;">{{ $data['message'] }}</h5>



Thank you,<br>
Pott Ai<br>
{{ config('app.fishpott_phone') }}<br>
{{ config('app.fishpott_email_two') }}<br>
{{ config('app.name') }}
@endcomponent