<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Árajánlat</title>
</head>
<body>
@php
    $partnerName = trim(($offer->customer?->last_name ?? '') . ' ' . ($offer->customer?->first_name ?? ''));
    $partnerName = $partnerName !== '' ? $partnerName : 'Partner';
@endphp

<p>Kedves Címzett!</p>
<p>Az árajánlatot csatolmányban küldjük.</p>
@if($offer->note)
    <p><strong>Megjegyzés:</strong></p>
    <p style="white-space: pre-wrap;">{{ $offer->note }}</p>
@endif
<p>Üdvözlettel,<br>{{ $partnerName }}</p>
</body>
</html>
