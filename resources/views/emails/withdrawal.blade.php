@component('mail::message')
# Withdrawal Request


A user has requested a withdrawal from their pott.
<br><p style="color: black;">User Pottname: {{ $data['user_pottname'] }}</p>
<br><p style="color: black;">User Withdrawal ID: {{ $data['withdrawal_id'] }}</p>
<br><p style="color: black;">Amount: {{ $data['amount'] }}</p>
<br><p style="color: black;">Time: {{ $data['time'] }}</p>

Once it's paid, mark it in the database as paid.


Thank you,<br>
{{ config('app.name') }}
@endcomponent