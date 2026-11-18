<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Promóció jelentkezés megerősítése</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#333333;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:30px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.08);">

                    <!-- Fejléc -->
                    <tr>
                        <td style="background-color:#b3122e; background:linear-gradient(135deg,#b3122e 0%,#e63946 55%,#1b7a3d 100%); padding:34px 30px; text-align:center;">
                            @if(!empty($logo_url))
                                <img src="{{ $logo_url }}" alt="Jégvarázs" style="max-height:70px; max-width:240px; margin-bottom:18px;" />
                            @endif
                            <div style="color:#ffd166; font-size:13px; letter-spacing:3px; text-transform:uppercase; font-weight:bold; margin-bottom:8px;">❄ Különleges ajánlat ❄</div>
                            <h1 style="margin:0; color:#ffffff; font-size:30px; font-weight:bold;">{{ $data['promotion_name'] }}</h1>
                        </td>
                    </tr>

                    <!-- Bevezető -->
                    <tr>
                        <td style="padding:30px 34px 10px;">
                            <p style="font-size:16px; line-height:1.7; margin:0 0 14px;">Kedves <strong>{{ $data['name'] }}</strong>!</p>
                            <p style="font-size:16px; line-height:1.7; margin:0 0 14px;">Köszönjük, hogy jelentkeztél a promóciónkra! Az alábbiakban összegzünk néhány fontos információt a jelentkezéseddel kapcsolatban.</p>
                        </td>
                    </tr>

                    <!-- Kupon -->
                    @if(!empty($data['coupon_code']))
                    <tr>
                        <td style="padding:10px 34px 20px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed #b3122e; border-radius:12px; background-color:#fff8f8;">
                                <tr>
                                    <td style="padding:22px 20px; text-align:center;">
                                        <div style="font-size:13px; letter-spacing:2px; text-transform:uppercase; color:#b3122e; font-weight:bold; margin-bottom:8px;">🎁 A te kuponkódod</div>
                                        <div style="font-size:30px; font-weight:bold; letter-spacing:4px; color:#1b5e20; background-color:#e8f5e9; display:inline-block; padding:12px 28px; border-radius:10px;">{{ $data['coupon_code'] }}</div>
                                        <div style="font-size:13px; color:#666666; margin-top:12px;">A kuponkódot a vásárlás során tudod felhasználni.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- Adatok -->
                    <tr>
                        <td style="padding:0 34px 26px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border-radius:10px;">
                                <tr>
                                    <td style="padding:18px 22px; font-size:15px; line-height:1.9;">
                                        <strong style="color:#b3122e;">Név:</strong> {{ $data['name'] }}<br />
                                        <strong style="color:#b3122e;">E-mail:</strong> {{ $data['email'] }}<br />
                                        <strong style="color:#b3122e;">Telefon:</strong> {{ $data['phone'] }}<br />
                                        <strong style="color:#b3122e;">Cím:</strong> {{ $data['address'] }}<br />
                                        <strong style="color:#b3122e;">Promóció:</strong> {{ $data['promotion_name'] }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Lábléc -->
                    <tr>
                        <td style="background-color:#1b7a3d; padding:20px 30px; text-align:center;">
                            <p style="margin:0 0 6px; color:#ffffff; font-size:14px; font-weight:bold;">{{ $basic_data['company_name'] ?? '' }}</p>
                            <p style="margin:0; color:#d7ecd9; font-size:13px; line-height:1.6;">
                                {{ $basic_data['company_address'] ?? '' }}<br />
                                {{ $basic_data['company_appointment_phone'] ?? '' }} · {{ $basic_data['company_appointment_email'] ?? '' }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 30px; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#999999;">Kérjük, erre az e-mail címre ne válaszoljon.<br />© {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>