@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection

@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'TOP Plusz',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'TOP Plusz', 'url' => route('topplusz')]
        ],
    ]
    ])

    <div class="row">
        <div class="col-12 mt-4">
            <p>
                A Gál-Ker Jégvarázs Kft. a GINOP Plusz-3.2.5-24-2024-00001 – Munkakörülmények fejlesztése pályázati program keretében 6 863 730 Ft összegű, 100%-os vissza nem térítendő támogatásban részesült.
            </p>
            <p>
                A projekt célja munkavállalóink munkakörülményeinek javítása, a munkabiztonság növelése, valamint a fizikai megterhelés csökkentése korszerű munkavédelmi, ergonómiai és anyagmozgatási eszközök beszerzésével.
            </p>
            <p>
                A fejlesztés eredményeként biztonságosabb, hatékonyabb és egészségesebb munkakörnyezetet biztosítunk munkatársaink számára, amely hozzájárul a magas színvonalú szolgáltatásaink fenntartásához és vállalkozásunk hosszú távú fejlődéséhez.
            </p>
            <p>
                <b>Projekt címe:</b> A Gál-Ker Jégvarázs Kft. munkakörülményeinek fejlesztése korszerű munkavédelmi és ergonómiai eszközök beszerzésével.
                <br>
                <b>Támogatási program:</b> GINOP Plusz-3.2.5-24-2024-00001 – Munkakörülmények fejlesztése
                <br>
                <b>Megítélt támogatás összege:</b> 6 863 730 Ft
                <br>
                <b>A támogatás intenzitása:</b> 100%
                <br>
                <b>A támogatás formája:</b> Vissza nem térítendő
            </p>

            <img src="{{ asset('static_media/2026-61-7_10-40-5_a2_fekvo_nyomda_page-0001.jpg') }}" alt="GINOP Plusz támogatás" class="img-fluid" style="max-width: 100%; height: auto;">
        </div>
    </div>
@endsection
