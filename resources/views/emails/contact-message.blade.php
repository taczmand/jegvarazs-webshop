<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Érdeklődés a weboldalról</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px;">
<table width="100%" style="max-width: 600px; margin: auto; background: #ffffff; border: 1px solid #e0e0e0; padding: 20px;">
    <tr>
        <td>
            <h2 style="color: #1882E2;">Érdeklődés</h2>
            <p><strong>Érdeklődő neve:</strong> {{ $message_data['contact_name'] }}</p>
            <p><strong>Érdeklődő e-mail címe:</strong> {{ $message_data['contact_email'] }}</p>
            <hr>
            <p><strong>Üzenet:</strong></p>
            <p>{{ $message_data['contact_message'] }}</p>
        </td>
    </tr>
</table>
</body>
</html>
