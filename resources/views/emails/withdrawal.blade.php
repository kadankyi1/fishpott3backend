@component('mail::message')
# Withdrawal Request


A user has requested a withdrawal from their pott.
<br><p style="color: black;">User Pottname: {{ $data['user_pottname'] }}</p>
<br><p style="color: black;">User Withdrawal ID: {{ $data['withdrawal_id'] }}</p>
<br><p style="color: black;">Amount: {{ $data['amount'] }}</p>
<br><p style="color: black;">Bank/Momo Network: {{ $data['withdrawal_receiving_bank_or_momo_name'] }}</p>
<br><p style="color: black;">Account Name: {{ $data['withdrawal_receiving_bank_or_momo_account_name'] }}</p>
<br><p style="color: black;">Account Number: {{ $data['withdrawal_receiving_bank_or_momo_account_number'] }}</p>
<br><p style="color: black;">Routing Number: {{ $data['withdrawal_receiving_bank_routing_number'] }}</p>
<br><p style="color: black;">Time: {{ $data['time'] }}</p>

Once it's paid, mark it in the database as paid.


Thank you,<br>
{{ config('app.name') }}
@endcomponent