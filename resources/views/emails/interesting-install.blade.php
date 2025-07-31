<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Telepítés iránti érdeklődés</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px;">
<table width="100%" style="max-width: 600px; margin: auto; background: #ffffff; border: 1px solid #e0e0e0; padding: 20px;">
    <tr>
        <td>
            <h2 style="color: #1882E2;">Telepítés iránti érdeklődés</h2>
            <p><strong>Ügyfél neve:</strong> {{ $customer->last_name }} {{ $customer->first_name }}</p>
            <p><strong>Ügyfél e-mail címe:</strong> {{ $customer->email }}</p>
            <hr>
            <p><strong>Termék:</strong> {{ $product->title }} (ID: {{ $product->id }})</p>
            <p><strong>Üzenet:</strong></p>
            <p>{{ $messageText }}</p>
        </td>
    </tr>
</table>
</body>
</html>
