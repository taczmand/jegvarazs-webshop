<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Időpont visszaigazolás</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px;">
<table width="100%" style="max-width: 600px; margin: auto; background: #ffffff; border: 1px solid #e0e0e0; padding: 20px;">
    <tr>
        <td>
            <h2 style="color: #1882E2;">Klímatisztítás időpontjának visszaigazolása</h2>

            <p>
                Tisztelt Ügyfelünk!<br><br>
                Ezúton visszaigazoljuk az Ön által lefoglalt időpontot a klímaberendezés tisztítására:
                <strong>{{ \Carbon\Carbon::parse($worksheet->installation_date)->format('Y.m.d') }}</strong>.
            </p>

            <p>
                Kollégánk a megadott napon, egyeztetett időpontban érkezik,
                és elvégzi a szükséges karbantartási munkálatokat.
            </p>

            <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 20px 0;">

            <h3 style="color: #1882E2;">Árak</h3>

            <p><strong>Általunk beszerelt klímaberendezés esetén:</strong></p>
            <ul>
                <li>Alap tisztítás: 12.000 Ft / készülék</li>
                <li>Mosás: 17.000 Ft / készülék</li>
            </ul>

            <p><strong>Nem általunk beszerelt klímaberendezés esetén:</strong></p>
            <ul>
                <li>Alap tisztítás: 15.000 Ft / készülék</li>
                <li>Mosás: 20.000 Ft / készülék</li>
            </ul>

            <p>
                Kollégánk a helyszínen készséggel válaszol minden felmerülő kérdésére,
                és szükség esetén további tájékoztatást ad.
            </p>

            <p>
                Amennyiben módosítani szeretné az időpontot, kérjük, jelezze felénk elérhetőségeinken.
            </p>

            <p style="margin-top: 30px;">
                Köszönjük, hogy minket választott!<br>
                <strong>Jégvarázs Klíma Jászberény</strong>
            </p>
        </td>
    </tr>
</table>
</body>
</html>

