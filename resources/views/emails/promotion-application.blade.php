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
                        @if(!empty($header_image_url))
                            <img src="{{ $header_image_url }}" alt="Karácsonyi promóció" style="display:block; width:100%; max-width:540px; height:auto; margin:0 auto 18px; border-radius:10px;" />
                        @endif
                    </tr>

                    <!-- Bevezető -->
                    <tr>
                        <td style="padding:30px 34px 10px; text-align:center;">

                            <p style="font-size:16px; line-height:1.7; margin:0 0 22px;">
                                Kedves <strong>{{ $data['name'] }}</strong>!
                            </p>

                            <div style="font-size:18px; font-weight:bold; color:#b3122e; margin-bottom:6px;">
                                🎁 A VÁLASZTOTT AJÁNDÉK 🎁
                            </div>

                            <div style="font-size:22px; font-weight:bold; color:#1b5e20; margin-bottom:22px;">
                                {{ $data['gift_name'] ?? 'DUAL ZONE AIR FRYER' }}
                            </div>

                            <p style="font-size:16px; line-height:1.7; margin:0 0 18px;">
                                Jelentkezését sikeresen rögzítettük. 🎄
                            </p>

                            <p style="font-size:15px; line-height:1.7; font-weight:bold; margin:0 0 20px;">
                                A jelentkezés során megjelölt konstrukció alapján {{ $data['gift_sets'] ?? 'a kiválasztott' }} vásárlása esetén {{ $data['gift_name'] ?? 'a kiválasztott ajándéktermékre' }} ajándéktermékre jogosult, a promóció részvételi feltételeinek teljesülése mellett.
                            </p>

                        </td>
                    </tr>

                    <!-- Kupon -->
                    @if(!empty($data['coupon_code']))
                        <tr>
                            <td style="padding:10px 34px 25px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:2px dashed #b3122e; border-radius:12px; background-color:#fff8f8;">
                                    <tr>
                                        <td style="padding:22px 20px; text-align:center;">
                                            <div style="font-size:13px; letter-spacing:2px; text-transform:uppercase; color:#b3122e; font-weight:bold; margin-bottom:8px;">
                                                🎁 A TE KUPONKÓDOD
                                            </div>

                                            <div style="font-size:30px; font-weight:bold; letter-spacing:4px; color:#1b5e20; background-color:#e8f5e9; display:inline-block; padding:12px 28px; border-radius:10px;">
                                                {{ $data['coupon_code'] }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <!-- További információk -->
                    <tr>
                        <td style="padding:5px 34px 10px; text-align:center;">

                            <p style="font-size:15px; line-height:1.7; margin:0 0 18px;">
                                Munkatársaink hamarosan felveszik Önnel a kapcsolatot a promócióval kapcsolatos további tudnivalók és a vásárlás részleteinek egyeztetése érdekében.
                            </p>

                            <p style="font-size:16px; line-height:1.7; font-weight:bold; font-style:italic; color:#b3122e; margin:0 0 18px;">
                                Köszönjük a bizalmat, és kellemes karácsonyi készülődést kívánunk!
                            </p>

                            <p style="font-size:16px; font-weight:bold; margin:0 0 25px;">
                                Jégvarázs Klíma csapata
                            </p>

                        </td>
                    </tr>

                    <!-- Promóciós feltételek -->
                    <tr>
                        <td style="padding:10px 34px 30px;">

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border-radius:10px;">
                                <tr>
                                    <td style="padding:20px 22px; font-size:14px; line-height:1.7; color:#333333;">

                                        <p style="font-size:15px; font-weight:bold; text-decoration:underline; margin:0 0 14px;">
                                            A promóció részvételi feltételei:
                                        </p>

                                        <ul style="padding-left:20px; margin:0;">
                                            <li style="margin-bottom:8px;">
                                                A promóció időtartama: <strong>2026. november 20. – december 31.</strong>
                                            </li>

                                            <li style="margin-bottom:8px;">
                                                A promócióban 2 vagy 3 szett klímaberendezés vásárlásával lehet részt venni.
                                            </li>

                                            <li style="margin-bottom:8px;">
                                                2 szett klímaberendezés vásárlása esetén 1 db Dual Zone Air Fryer, 3 szett klímaberendezés vásárlása esetén 1 db 40" LED televízió jár ajándékba.
                                            </li>

                                            <li style="margin-bottom:8px;">
                                                A promócióban való részvételhez online regisztráció szükséges.
                                            </li>

                                            <li style="margin-bottom:8px;">
                                                Az ajándék kiszállítása a vásárlási szerződés megkötését követően, az ügyfél részére történik.
                                            </li>

                                            <li style="margin-bottom:8px;">
                                                A klímaberendezések telepítésének időpontja előzetes egyeztetés alapján kerül meghatározásra.
                                            </li>

                                            <li>
                                                Az ajándék készpénzre nem váltható, és kizárólag a promóció valamennyi feltételének teljesülése esetén jár.
                                            </li>
                                        </ul>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Lábléc -->
                    <tr>
                        <td style="background-color:#1b7a3d; padding:20px 30px; text-align:center;">

                            <p style="margin:0 0 6px; color:#ffffff; font-size:14px; font-weight:bold;">
                                {{ $basic_data['company_name'] ?? '' }}
                            </p>

                            <p style="margin:0; color:#d7ecd9; font-size:13px; line-height:1.6;">
                                {{ $basic_data['company_address'] ?? '' }}<br />
                                {{ $basic_data['company_appointment_phone'] ?? '' }} · {{ $basic_data['company_appointment_email'] ?? '' }}
                            </p>

                        </td>
                    </tr>

                    <!-- Jogi lábléc -->
                    <tr>
                        <td style="padding:16px 30px; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#999999;">
                                Kérjük, erre az e-mail címre ne válaszoljon.<br />
                                © {{ now()->year }} Jegvarazsbolt.hu – Minden jog fenntartva.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>
