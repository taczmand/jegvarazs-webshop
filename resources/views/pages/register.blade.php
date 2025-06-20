

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
        <div style="color:red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('registration') }}" class="w-100" style="max-width: 400px; margin: auto;">
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
            <label for="name" class="form-label">Szerelőként regisztrálok</label>
            <input type="checkbox" name="is_partner" id="is_partner" class="" value="1">
        </div>

        <div class="d-none" id="only_partner_fields">
            <div class="mb-3">
                <label for="fgaz" class="form-label">F-Gáz azonosító*</label>
                <input type="fgaz" name="fgaz" id="fgaz" class="form-control" value="" placeholder="" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Regisztrálok</button>
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


