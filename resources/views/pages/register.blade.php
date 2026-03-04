

@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Regisztráció',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')]
        ],
    ]
    ])

    @if($errors->any())
        <div class="shop-validation-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('registration') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box">
        @csrf


        <div class="mb-3">
            <label for="last_name" class="form-label">Vezetéknév*</label>
            <input type="text" name="last_name" id="last_name" class="form-control" value="" placeholder="" required>
        </div>
        <div class="mb-3">
            <label for="fist_name" class="form-label">Keresztnév*</label>
            <input type="text" name="first_name" id="first_name" class="form-control" value="" placeholder="" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Telefonszám*</label>
            <input type="text" name="phone" id="phone" class="form-control" value="" placeholder="" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail cím*</label>
            <input type="email" name="email" id="email" class="form-control" value="" placeholder="" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Jelszó*</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Jelszó újra*</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="" required>
        </div>
        <div class="mb-3">
            <label class="form-label" for="accept_terms">
                <input type="checkbox" name="accept_terms" id="accept_terms" value="1" required>
                Elfogadom az Általános szerződési feltételeket
            </label>
            <div>
                <a href="#aszf">Általános szerződési feltételek megtekinthetők az oldal alján</a>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label" for="is_partner">
                <input type="checkbox" name="is_partner" id="is_partner" value="1">
                Szerelőként regisztrálok
            </label>
        </div>

        <div class="d-none" id="only_partner_fields">
            <div class="mb-3">
                <label for="fgaz" class="form-label">F-Gáz azonosító*</label>
                <input type="fgaz" name="fgaz" id="fgaz" class="form-control" value="" placeholder="">
            </div>
        </div>

        <button type="submit" class="site-btn w-100">Regisztrálok</button>
    </form>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const only_partner_fields = document.getElementById('only_partner_fields');
        const isPartnerRadio = document.getElementById('is_partner');

        function togglePartnerFields() {
            if (isPartnerRadio.checked) {
                only_partner_fields.classList.remove('d-none');
            } else {
                only_partner_fields.classList.add('d-none');
            }
        }

        isPartnerRadio.addEventListener('change', togglePartnerFields);
    });
</script>


