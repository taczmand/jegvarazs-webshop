<!DOCTYPE html>
<html lang="hu">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Szerződés</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.6;
            padding: 40px;
            max-width: 100%;
            margin: 0;
            color: #000;
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 0;
            font-size: 18px;
        }
        .section {
            margin-top: 0;
        }

        /* Két oszlop float-tal */
        .two-column {
            font-size: 14px;
            /* clearfix */
            zoom: 1;
        }
        .two-column::after {
            content: "";
            display: block;
            clear: both;
        }

        .two-column .field {
            width: 48%;
            float: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            box-sizing: border-box;
            margin-bottom: 10px;
        }
        /* Ha szeretnéd, hogy egymás alatt legyenek a kisebb képernyőkön, akkor lehet media query-vel szélességet 100%-ra állítani */

        .field .label {
            font-weight: bold;
            margin-right: 5px;
            display: inline-block;
            width: auto;
            white-space: nowrap;
            flex-shrink: 0; /* ez nem kell már flex nélkül */
        }

        .field .value {
            display: inline-block;
            max-width: calc(100% - 100px); /* egy kis korlátozás a hosszú szövegekre */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border-bottom: 1px dotted #000;
            vertical-align: bottom;
        }

        .field {
            margin-bottom: 10px;
        }

        .signature-line {
            margin-top: 60px;
            /* két oszlop float-tal */
            zoom: 1;
        }
        .signature-line::after {
            content: "";
            display: block;
            clear: both;
        }
        .signature-line div {
            float: left;
            width: 45%;
            border-top: 1px solid #000;
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
            font-size: 14px;
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
            font-size: 14px;
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
            margin-top: 20px;
            text-align: center;
        }
        .footer {
            margin-top: 80px;
            font-size: 0.9em;
        }

        @media print {
            @page {
                margin: 5mm;
            }

            body {
                margin: 0;
            }
        }

        /* Kis képernyőn a two-column legyen egy oszlop */
        @media screen and (max-width: 600px) {
            .two-column .field {
                width: 100%;
                float: none;
            }
            .signature-line div {
                width: 100%;
                float: none;
                margin-bottom: 20px;
            }
            .cotract-options div {
                display: block;
                margin-bottom: 5px;
            }
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
        <span class="value">{{ $data['installation_datetime'] }}</span>
    </div>
    <div class="field">
        <span class="label">Név / Cégnév:</span>
        <span class="value">Teszt Elek Kft.</span>
    </div>
    <div class="field">
        <span class="label">Születési hely, idő:</span>
        <span class="value">Budapest, 1990.01.01.</span>
    </div>
    <div class="field">
        <span class="label">Anyja neve:</span>
        <span class="value">Kovács Ilona</span>
    </div>
    <div class="field">
        <span class="label">Szem. szám / adószám:</span>
        <span class="value">123456AA / 9876543210</span>
    </div>
    <div class="field">
        <span class="label">Telefonszám:</span>
        <span class="value">+36 30 123 4567</span>
    </div>
    <div class="field">
        <span class="label">E-mail cím:</span>
        <span class="value">teszt@ceg.hu</span>
    </div>
    <div class="field">
        <span class="label">Lakcím / Székhely:</span>
        <span class="value">1024 Budapest, Fő utca 1.</span>
    </div>
</div>

<p style="margin-top: 1px">szám alatti lakos (továbbiakban: <b>Megrendelő</b>), másrészről Gál-Ker Jégvarázs Klíma és Biztonságtechnikai Kft.
    (továbbiakban: <b>Kivitelező</b>) között alulírott helyen és időben az alábbi feltételek szerint: </p>

<div class="section">
    <div class="pont">
        <span class="szam">1.</span> A megrendelő megveszi, a kivitelező pedig a megtekintett ingatlanra vállalja, hogy a szerződés mellékletében szereplő klimatizáló berendezést beszereli és beüzemeli.
    </div>

    <div class="pont">
        <span class="szam">2.</span> A szerződő felek által kölcsönösen kialkudott vételár ………………………., azaz …………………………………… forint, mely munkadíjjal együtt értendő.
    </div>

    <div class="pont">
        <span class="szam">3.</span> A teljes vételárat és munkadíjat a megrendelő az alábbiak szerint fizeti meg a kivitelezőnek:
    </div>

    <div class="pont">
        <span class="szam"><b>1.</b></span> <b>a*, ………………….. Ft</b> azaz ……………………………………………….. Ft foglaló jogcímen a szerződés aláírásával egyidőben kerül átadásra.<br>
        <b>b*, ………………….. Ft</b> azaz ……………………………………….. Ft foglaló jogcímen a <b>10104260-72764100-01005001</b> számlaszámra kerül átutalásra. A szerződés a foglaló jóváírásának
        megtörténtekor válik véglegessé, a kialkudott határidőre (…………év …………….hónap …………..nap)
        történő átutalás elmulasztásával a szerződés semmisnek tekintendő.
    </div>

    <p>Szerződő felek kijelentik, hogy ismerik a foglaló jogi természetét. Amennyiben a szerződésben vállalt munka olyan
        ok miatt nem kerül végrehajtásra, amelyért egyik fél sem felelős vagy mindkét fél egyenlő mértékig felelős, a foglaló
        visszajár.</p>

    <div class="pont">
        <span class="szam"><b>2.</b></span><b>a*, ……………………… Ft</b> azaz ……………………………. forint a munka elvégzésével kerül készpénzben kifizetésre.<br>
        <b>b*, ………………….. Ft</b> azaz …………………………………….. forint a munka elvégzésével kerül átutalásra a <b>10104260-72764100-01005001</b> (…………év …………….hónap …………..napjáig)
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
        <span class="szam">10.</span> Jelen szerződésben rögzített munka teljesítésének dátuma: …….…….év………….…..hónap………….nap.
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
    <div>
        <input type="checkbox">
        <span>Talpkonzol</span>
    </div>
    <div>
        <input type="checkbox">
        <span>... konzol</span>
    </div>
    <div>
        <input type="checkbox">
        <span>Tetőszerelés</span>
    </div>
    <div>
        <input type="checkbox">
        <span>Dekor és csövezés</span>
    </div>
    <div>
        <input type="checkbox">
        <span>... cm szigetelés</span>
    </div>
    <div>
        <input type="checkbox">
        <span>Betonfal</span>
    </div>
</div>

<div class="contact-section">
    <h3>Krisztián: 06-70/675-9245</h3>
    <h3>Norbi: 06-20/778-9928</h3>
</div>

<div class="signature-line">
    <div>Megrendelő</div>
    <div>Kivitelező</div>
</div>

<div class="section">
    <h2>Nyilatkozat foglaló megfizetéséről</h2>
    <p>Mint kivitelező felelősségem teljes tudatában nyilatkozom, hogy az általam elvállalt munka ellenértéke :
        .……….…………………….Ft, melyből </p>

    <div class="pont">
        <span class="szam">1.</span> a <b>foglaló …………………………… Ft</b> ami a szerződés aláírásakor <b>megfizetésre került</b>.
    </div>
    <div class="pont">
        <span class="szam">2.</span> a <b>foglaló …………………………… Ft mely</b> átutalásra kerül a (…………év …………….hónap
        …………..napjáig) <b>10104260-72764100-01005001</b> számlaszámra. A kialkudott határidőre történő
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
                <th>Teljesítmény</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Klíma 1</td>
                <td>3.5 kW</td>
            </tr>
            <tr>
                <td>Klíma 2</td>
                <td>5.0 kW</td>
            </tr>
            <tr>
                <td>Klíma 3</td>
                <td>2.5 kW</td>
            </tr>
            <tr>
                <td>Klíma 4</td>
                <td>4.0 kW</td>
            </tr>
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
    <p style="margin-top: 20px">…………………,………………. …………..év………………hónap……..nap</p>
</div>

<div class="signature-line">
    <div>Megrendelő</div>
    <div>Kivitelező</div>
</div>


</body>
</html>
