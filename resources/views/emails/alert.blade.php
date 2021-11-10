@component('mail::message')
# FishPott System Alert


Find below an event that needs your attention. 
<br><h5 style="color: black;">{{ $data['reset_code'] }}</h5>



Thank you,<br>
Pott Ai<br>
{{ config('app.name') }}
@endcomponent