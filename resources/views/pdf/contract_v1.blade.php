@inject('convertHelper', 'App\Helpers\AmountToText')
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Szerződés</title>
    <style>

        @php
            $imagePath = base_path(env('STATIC_MEDIA_PATH') . '/uj_logo_nagy_opal.png');
            $imageData = base64_encode(file_get_contents($imagePath));
            $mimeType = mime_content_type($imagePath);
            $backgroundImage = "data:$mimeType;base64,$imageData";
        @endphp

        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1;
            font-size: 8pt;
            padding: 0;
            max-width: 100%;
            margin: 0;
            color: #000;
            background-image: url("{{ $backgroundImage }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: contain;
        }
        h1, h2 {
            text-align: center;
            font-size: 14pt;
        }
        .section {
            margin-top: 0;
        }

        /* Két oszlop float-tal */
        .two-column {
           width: 100%;
        }

        .two-column .field {
            width: 49%;
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-sizing: border-box;
        }

        .field .label {
            font-weight: bold;
            margin-right: 5px;
            display: inline-block;
            width: auto;
            white-space: nowrap;
        }

        .field .value {
            display: inline-block;
            max-width: 200px; /* egy kis korlátozás a hosszú szövegekre */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border-bottom: 1px dotted #000;
            vertical-align: top;
        }

        .field {
            margin-bottom: 10px;
        }
        .signature-image div {
            display: inline-block;
            width: 49%;
            text-align: center;
            box-sizing: border-box;
        }

        .signature-line {
            margin-top: 1mm;;
        }
        .signature-line div {
            display: inline-block;
            width: 49%;
            border-top: 1px dotted #000;
            text-align: center;
            padding-top: 5px;
            font-weight: bold;
            box-sizing: border-box;
        }
        /* Középre húzás helyett a margókat használhatod, ha kell */

        .pont {
            position: relative;
            line-height: 1;
            padding-left: 30px; /* hely a számnak */
            margin-bottom: 10px;
        }

        .pont .szam {
            position: absolute;
            left: 0;
            width: 40px; /* szabad hely a számnak */
            text-align: left; /* szám jobb oldalon legyen */
        }
        p {
            line-height: 1;
        }

        ul.options {
            list-style: none;
            padding-left: 0;
        }
        ul.options li::before {
            content: "☐ ";
        }
        .small {
            font-size: 0.9em;
        }
        .cotract-options {
            margin-top: 20px;
            font-size: 12px;
        }
        .cotract-options div {
            display: inline-block;
            margin-right: 15px;
            white-space: nowrap;
        }
        .contact-section {
            text-align: center;
        }
        .contact-section h3 {
            display: inline-block;
            margin-right: 5mm;
        }
        .footer {
            margin-top: 80px;
            font-size: 0.9em;
        }

    </style>
</head>
<body>

<h1>SZERZŐDÉS</h1>

<div class="two-column">
    <div class="field">
        <span class="label" style="font-weight: normal">mely létrejött egyrészről</span>
    </div>
    <div class="field">
        <span class="label">Szerelés dátuma:</span>
        <span class="value">{{ $contract['installation_date'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">Név / Cégnév:</span>
        <span class="value">{{ $contract['name'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">Születési hely, idő:</span>
        <span class="value">{{ $contract['place_of_birth'] ?? "" }}, {{ $contract['date_of_birth'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">Anyja neve:</span>
        <span class="value">{{ $contract['mothers_name'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">Szem. szám / adószám:</span>
        <span class="value">{{ $contract['id_number'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">Telefonszám:</span>
        <span class="value">{{ $contract['phone'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">E-mail cím:</span>
        <span class="value">{{ $contract['email'] ?? "" }}</span>
    </div>
    <div class="field">
        <span class="label">Lakcím / Székhely:</span>
        <span class="value">{{ $contract['zip_code'] ?? "" }} {{ $contract['city'] }}, {{ $contract['address_line'] }}</span>
    </div>
</div>

<p style="margin-top: 1px">szám alatti lakos (továbbiakban: <b>Megrendelő</b>), másrészről Gál-Ker Jégvarázs Klíma és Biztonságtechnikai Kft.
    (továbbiakban: <b>Kivitelező</b>) között alulírott helyen és időben az alábbi feltételek szerint: </p>

<div class="section">
    <div class="pont">
        <span class="szam">1.</span> A megrendelő megveszi, a kivitelező pedig a megtekintett ingatlanra vállalja, hogy a szerződés mellékletében szereplő klimatizáló berendezést beszereli és beüzemeli.
    </div>

    <div class="pont">
        @php
            $total_gross = $contract['data']['price'] ?? null;
            if (is_numeric($total_gross)) {
                $teljes_vetelar_formazva = number_format($total_gross, 0, ',', ' ');
            } else {
                $teljes_vetelar_formazva = "?";
            }
            $total_gross_text = $convertHelper->convert($total_gross);
        @endphp
        <span class="szam">2.</span> A szerződő felek által kölcsönösen kialkudott vételár <b style="display: inline-block; border-bottom: 1px dotted #000;">{!! $teljes_vetelar_formazva !!} Ft</b>, azaz <b style="display: inline-block; border-bottom: 1px dotted #000;">{!! $total_gross_text !!}</b> forint, mely munkadíjjal együtt értendő.
    </div>

    <div class="pont">
        <span class="szam">3.</span> A teljes vételárat és munkadíjat a megrendelő az alábbiak szerint fizeti meg a kivitelezőnek:
    </div>

    <div class="pont">
        @php
            \Carbon\Carbon::setLocale('hu');
            $spaces = str_repeat("\u{00A0}", 20);

            $cash_amount = $spaces;
            $cash_amount_text = $spaces;

            $transfer_amount = $spaces;
            $transfer_amount_text = $spaces;

            $due_date = '…………év …………….hónap …………..nap';

            $deposit_amount = $contract['data']['deposit_amount'] ?? null;

            if ($contract['data']['deposit_due_date']) {
                $carbon = \Carbon\Carbon::parse($contract['data']['deposit_due_date']);
                $due_date = '<b style="border-bottom: 1px dotted black">'.$carbon->year . '</b> év <b style="border-bottom: 1px dotted black">' . $carbon->translatedFormat('F') . '</b> hónap <b style="border-bottom: 1px dotted black">' . $carbon->day . '</b> nap';
            }

            if ($contract['data']['deposit_payment_method'] === "Készpénz") {
                if (!empty($deposit_amount)) {
                    $cash_amount_text = $convertHelper->convert($deposit_amount);
                    $cash_amount = number_format($deposit_amount, 0, ',', ' ');
                }
            }

            if ($contract['data']['deposit_payment_method'] === "Átutalás") {
                if (!empty($deposit_amount)) {
                    $transfer_amount_text = $convertHelper->convert($deposit_amount);
                    $transfer_amount = number_format($deposit_amount, 0, ',', ' ');
                }
            }
        @endphp
        <span class="szam"><b>1.</b></span> <b style="display: inline-block; border-bottom: 1px dotted #000;">a*, {{ $cash_amount }} Ft</b> azaz <b style="display: inline-block; border-bottom: 1px dotted #000;">{{ $cash_amount_text }}</b> forint foglaló jogcímen a szerződés aláírásával egyidőben kerül átadásra.<br>
        <b style="display: inline-block; border-bottom: 1px dotted #000;">b*, {{ $transfer_amount }} Ft</b> azaz <b style="display: inline-block; border-bottom: 1px dotted #000;">{{ $transfer_amount_text }}</b> forint foglaló jogcímen a <b>10104260-72764100-01005001</b> számlaszámra kerül átutalásra. A szerződés a foglaló jóváírásának
        megtörténtekor válik véglegessé, a kialkudott határidőre ({!! $due_date !!}) történő
        történő átutalás elmulasztásával a szerződés semmisnek tekintendő.
    </div>

    <p>Szerződő felek kijelentik, hogy ismerik a foglaló jogi természetét. Amennyiben a szerződésben vállalt munka olyan
        ok miatt nem kerül végrehajtásra, amelyért egyik fél sem felelős vagy mindkét fél egyenlő mértékig felelős, a foglaló
        visszajár.</p>

    <div class="pont">
        @php
            $cash_amount = $spaces;
            $cash_amount_text = $spaces;

            $transfer_amount = $spaces;
            $transfer_amount_text = $spaces;

            $due_date = '…………év …………….hónap …………napjáig';

            if (is_numeric($total_gross) && $deposit_amount < $total_gross) {
                $amount = $total_gross - $deposit_amount;
            }

            if ($contract['data']['transfer_payment_due_date']) {
                $carbon = \Carbon\Carbon::parse($contract['data']['transfer_payment_due_date']);
                $due_date = '<b style="border-bottom: 1px dotted black">'.$carbon->year . '</b> év <b style="border-bottom: 1px dotted black">' . $carbon->translatedFormat('F') . '</b> hónap <b style="border-bottom: 1px dotted black">' . $carbon->day . '</b> napjáig';
            }

            if ($contract['data']['purchase_price_payment_method'] === "Készpénz") {
                if (!empty($amount)) {
                    $cash_amount_text = $convertHelper->convert($amount);
                    $cash_amount = number_format($amount, 0, ',', ' ');
                }
            }

            if ($contract['data']['purchase_price_payment_method'] === "Átutalás") {
                if (!empty($amount)) {
                    $transfer_amount_text = $convertHelper->convert($amount);
                    $transfer_amount = number_format($amount, 0, ',', ' ');
                }
            }
        @endphp
        <span class="szam"><b>2.</b></span><b style="border-bottom: 1px dotted black">a*, {{ $cash_amount }} Ft</b> azaz <b style="border-bottom: 1px dotted black">{{ $cash_amount_text }}</b> forint a munka elvégzésével kerül készpénzben kifizetésre.<br>
        <b style="border-bottom: 1px dotted black">b*, {{ $transfer_amount }} Ft</b> azaz <b style="border-bottom: 1px dotted black">{{ $transfer_amount_text }}</b> forint a munka elvégzésével kerül átutalásra a <b>10104260-72764100-01005001</b> ({!! $due_date !!})
        számlaszámra.
    </div>

    <div class="pont">
        <span class="szam">4.</span> A kivitelező nyilatkozik, hogy az szerződés tárgyát képező berendezés rejtett hibájáról tudomása nincsen.
    </div>

    <div class="pont">
        <span class="szam">5.</span> Szerződő felek nyilatkoznak, hogy magyar állampolgárok és ügyletkötési képességük nem áll semmiféle korlátozás alatt.
    </div>

    <div class="pont">
        <span class="szam">6.</span> A szerződésben szereplő és kölcsönösen kialkudott klímaberendezés a teljes vételár megfizetéséig a kivitelező
        tulajdonát képezi, azt a kifizetés elmulasztása esetén a kivitelező a megrendelő költségén leszereli és elszállítja.
    </div>

    <div class="pont">
        <span class="szam">7.</span> A szerződés létrejöttekor közösön megbeszélt klíma berendezés telepítési helyektől csak közös megegyezéssel
        lehet eltérni. Amennyiben a telepítés helye mégis változik, úgy a szerződésben szereplő munkadíj is változik. 3 méter
        csőhossz fölötti szerelés esetén méterenként 10 000 Ft többlet költséggel kell számolni.
    </div>

    <div class="pont">
        <span class="szam">8.</span> Amennyiben a szerződésben szereplő klímaberendezést Magyarországon nem beszerezhető, úgy a kivitelező
        a szerződésben szereplő klímával megegyező tudású készüléket ajánl a megrendelőnek, mely ha elfogadásra kerül,
        úgy a klímaberendezés többlet költsége a megrendelőt terheli. Amennyiben nem kerül elfogadásra a kiajánlott
        készülék, úgy az eredeti berendezés beszerzésére a kiviteleőzek további 1 hónap áll rendelkezésére, ha ez idő alatt
        sem beszerezhető a készülék és nem tudnak a szerződő felek megállapodni a készülék tekintetében, úgy a foglaló
        vissza jár. A munka végrehajtásának dátuma ez esetben egy hónappal meghosszabbodik.
    </div>

    <div class="pont">
        <span class="szam">9.</span> A kivitelező vállalja, hogy a munkát, e szerződésben rögzített időpontig elvégzi.
    </div>

    <div class="pont">
        @php

        if (isset($contract['data']['completion_due_date'])) {
            $carbon = \Carbon\Carbon::parse($contract['data']['completion_due_date'] ?? '');
            $completion_due_date = '<b style="border-bottom: 1px dotted black">'.$carbon->year . '</b> év <b style="border-bottom: 1px dotted black">' . $carbon->translatedFormat('F') . '</b> hónap <b style="border-bottom: 1px dotted black">' . $carbon->day . '</b> nap';
        } else {
            $completion_due_date = '… év … hónap … nap';
        }

        @endphp
        <span class="szam">10.</span> Jelen szerződésben rögzített munka teljesítésének dátuma: {!! $completion_due_date !!}.
    </div>

    <div class="pont">
        <span class="szam">11.</span> A szerződésben nem szabályozott kérdésekben a Ptk. szabályai az irányadók.
        A szerződés a Felek mindenre kiterjedő szándékát pontosan tartalmazza, ezért a szerződésbe foglalt nyilatkozatuk
        egyben tényvázlatnak minősül.
    </div>

    <p>Jelen szerződést a felek felolvasás és értelmezés után, mint akaratukkal mindenben megegyezőt, jóváhagyólag
        aláírták. </p>

</div>

<div class="section cotract-options">
    @php
        $has_ground_bracket_checked = isset($contract['data']['has_ground_bracket']) && $contract['data']['has_ground_bracket'] ? 'checked' : '';
        $has_roof_mounting_checked = isset($contract['data']['roof_mounting']) && $contract['data']['roof_mounting'] ? 'checked' : '';
        $has_decor_piping_checked = isset($contract['data']['has_decor_and_piping']) && $contract['data']['has_decor_and_piping'] ? 'checked' : '';
        $has_concrete_wall_checked = isset($contract['data']['has_concrete_wall']) && $contract['data']['has_concrete_wall'] ? 'checked' : '';

        $bracket = $contract['data']['bracket'] ?? null; // Konzol
        $insulation_cm = $contract['data']['insulation_thickness_cm'] ?? null; // Szigetelés cm-ben

        // Ezek akkor legyenek "checked", ha van értékük
        $has_bracket_checked = $bracket ? 'checked' : '';
        $has_insulation_cm_checked = $insulation_cm ? 'checked' : '';
    @endphp

    <div>
        <input type="checkbox" {{ $has_ground_bracket_checked }}>
        <span>Talpkonzol</span>
    </div>

    <div>
        <input type="checkbox" {{ $has_bracket_checked }}>
        <span>{{ $bracket ?? '... konzol' }}</span>
    </div>

    <div>
        <input type="checkbox" {{ $has_roof_mounting_checked }}>
        <span>Tetőszerelés</span>
    </div>

    <div>
        <input type="checkbox" {{ $has_decor_piping_checked }}>
        <span>Dekor és csövezés</span>
    </div>

    <div>
        <input type="checkbox" {{ $has_insulation_cm_checked }}>
        <span>{{ $insulation_cm ? $insulation_cm . ' cm szigetelés' : '... cm szigetelés' }}</span>
    </div>

    <div>
        <input type="checkbox" {{ $has_concrete_wall_checked }}>
        <span>Betonfal</span>
    </div>

</div>

<div class="contact-section">
    <h3>Krisztián: 06-70/675-9245</h3>
    <h3>Norbi: 06-20/778-9928</h3>
</div>

<div class="signature-image">
    <div>
    @if(!empty($signature_path) && file_exists($signature_path))
        <img src="{{ $signature_path }}" style="max-height: 2cm;">
    @endif
    </div>
    <div>
        @if(file_exists(storage_path("app/private/signatures/jegvarazs_szerzodes_v1_kivitelezo_alairas.png")))
            <img src="{{ storage_path("app/private/signatures/jegvarazs_szerzodes_v1_kivitelezo_alairas.png") }}" style="width: 200px; height: auto;">
        @endif
    </div>
</div>
<div class="signature-line">
    <div>Megrendelő</div>
    <div>Kivitelező</div>
</div>

<div class="section">
    <h2>Nyilatkozat foglaló megfizetéséről</h2>
    <p>Mint kivitelező felelősségem teljes tudatában nyilatkozom, hogy az általam elvállalt munka ellenértéke :
        <b style="display: inline-block; border-bottom: 1px dotted #000;">{!! $teljes_vetelar_formazva !!}</b> Ft, melyből </p>
    @php
        $cash_amount = $spaces;
        $transfer_amount = $spaces;

        $amount = $contract['data']['deposit_amount'] ?? null;

        if ($contract['data']['deposit_payment_method'] === "Készpénz") {
            if (!empty($amount)) {
                $cash_amount = number_format($amount, 0, ',', ' ');
            }
        }

        if ($contract['data']['deposit_payment_method'] === "Átutalás") {
            if (!empty($amount)) {
                $transfer_amount = number_format($amount, 0, ',', ' ');
            }
        }
        $due_date = '…………év …………….hónap …………..napjáig';

        if ($contract['data']['deposit_due_date']) {
            $carbon = \Carbon\Carbon::parse($contract['data']['deposit_due_date']);
            $due_date = '<b style="border-bottom: 1px dotted black">'.$carbon->year . '</b> év <b style="border-bottom: 1px dotted black">' . $carbon->translatedFormat('F') . '</b> hónap <b style="border-bottom: 1px dotted black">' . $carbon->day . '</b> napjáig';
        }
    @endphp
    <div class="pont">
        <span class="szam">1.</span> a <b>foglaló <b style="border-bottom: 1px dotted black">{!! $cash_amount !!}</b> Ft</b> ami a szerződés aláírásakor <b>megfizetésre került</b>.
    </div>
    <div class="pont">
        <span class="szam">2.</span> a <b>foglaló <b style="border-bottom: 1px dotted black">{!! $transfer_amount !!}</b> Ft mely</b> átutalásra kerül a ({!! $due_date !!}) <b>10104260-72764100-01005001</b> számlaszámra. A kialkudott határidőre történő
        átutalás elmulasztásával a szerződés semmisnek tekintendő
    </div>

</div>

<div class="section">
    <h2>Megállapodás klíma berendezésekről</h2>
    <p>Mint megrendelő felelősségem teljes tudatában nyilatkozom, hogy az általam az alábbiakban felsorolt
        telepítettni kívánt klíma berendezéseket megismertem így ezen klíma berendezéseket kívánom a
        kivitelezővel felszereltetni.</p>
    <p>Ezen készülékektől csak a szerződő felek közös megegyezésével lehet eltérőt telepíteni és beüzemelni. </p>

    <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 10px;">
        <thead>
            <tr>
                <th>Megnevezés</th>
                <th>Mennyiség</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product['title'] }}</td>
                <td>{{ $product['product_qty'] }} db</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <h2>Hozzájárulás személyes adat kezeléshez</h2>
    <p>
        Alulírott hozzájárulok a következő személyes adataimnak: név, születési hely és idő, lakcím, levelezési cím, elektronikus levelezési cím,
        telefonszám a szerződésben nevezett egyéni vállalkozó által vezetett nyilvántartásban történő rögzítéséhez, kezeléséhez.
        <br>
        1. Az adatkezelés célja a szerződésben nevezett Gál-Ker Jégvarázs Klíma és Biztonságtechnikai Kft.
            mint adatkezelő szervezet engedélyezett, cél szerinti tevékenységének megvalósítása, jogos érdekeinek érvényesítése, a jogosultaknak
            szolgáltatás nyújtása.
        <br>
        2. A GDPR 2. cikke alapján a személyes adatai nyilvántartásba vételével, és ott történő megőrzésével valósul meg.
        <br>
        3. Az adatok megőrzésére a szerződéses jogviszony fennállásának időtartamáig kerül sor, majd azok megsemmisítésre kerülnek. Az adatok
        külön törvényi felhatalmazás illetve kötelezettség híján harmadik személynek nem kerülnek továbbításra, azokba kizárólagosan csak az
        adatkezelőnél tevékenységet végző, valamint tevékenységüket ellenőrző személyek tekinthetnek be, az adatkezelő adatfeldolgozót nem vesz
        igénybe.
        <br>
        4. Önt mint érintettet személyes adataival kapcsolatosan megilleti a
        <br>
        - betekintés joga, amely alapján az Önről kezelt személyes adatokat megismerheti, helyesbíttetheti, töröltetheti azokat
        <br>
        - az adathordozhatósághoz való jog automatizáltan kezelt adatok esetén, amely alapján Ön jogosult arra, az adatkezelő rendelkezésére bocsátott
        személyes adatokat tagolt, széles körben használt, géppel olvasható formátumban megkapja, és ezeket az adatokat egy másik adatkezelőnek
        továbbítsa anélkül, hogy ezt az adatkezelő akadályozná.
        <br>
        - az elfeledtetés joga , amely alapján Ön a nyilvánosságra hozott személyes adatot, töröltette , úgy az adatkezelő köteles megtenni az elérhető
        technológia és a megvalósítás költségeinek figyelembevételével minden ésszerűen elvárható lépést – ideértve technikai intézkedést – annak
        érdekében, hogy tájékoztassa az esetlegesen az adatokat kezelő (további) adatkezelőket, hogy az érintett kérelmezte a szóban forgó személyes
        adatokra mutató linkek vagy e személyes adatok másolatának, illetve másodpéldányának törlését.
        <br>
        - tiltakozási jog adatvédelmi önrendelkezési joga sérelme esetén, amelyet az adatkezelővel szemben közvetlenül terjeszthet elő, megilleti
        továbbá a felügyeleti hatóságnál történő panasztételhez való jog (77. cikk), a felügyeleti hatósággal szembeni hatékony bírósági jogorvoslathoz
        való jog (78. cikk), az adatkezelővel vagy az adatfeldolgozóval szembeni hatékony bírósági jogorvoslathoz való jog (79. cikk).
        <br>
        5. Az Ön adatkezeléshez való hozzájárulása önkéntes, azonban annak hiányában az adatkezelő szolgáltatásait nem tudja Önnek biztosítani.
    </p>
    <p>Fent nevezett a fenti tájékoztatást megértettem, tudomásul vettem és személyes adataim rögzítéséhez , kezeléséhez szabad akaratomból
        hozzájárulok. </p>

    @php
        $carbon = \Carbon\Carbon::parse($contract['data']['contract_datetime'] ?? '');
        $contract_date = '<b style="border-bottom: 1px dotted black">'.$carbon->year . '</b> év <b style="border-bottom: 1px dotted black">' . $carbon->translatedFormat('F') . '</b> hónap <b style="border-bottom: 1px dotted black">' . $carbon->day . '</b> nap';
    @endphp
    <p style="margin-top: 20px">{{ $contract['data']['contract_location'] ?? '?'  }}, {!! $contract_date !!}</p>
</div>
<div class="signature-image">
    <div>
        @if(!empty($signature_path) && file_exists($signature_path))
            <img src="{{ $signature_path }}" style="max-height: 2cm;">
        @endif
    </div>
    <div>
        @if(file_exists(storage_path("app/private/signatures/jegvarazs_szerzodes_v1_kivitelezo_alairas.png")))
            <img src="{{ storage_path("app/private/signatures/jegvarazs_szerzodes_v1_kivitelezo_alairas.png") }}" style="width: 200px; height: auto;">
        @endif
    </div>
</div>
<div class="signature-line">
    <div>Megrendelő</div>
    <div>Kivitelező</div>
</div>


</body>
</html>
