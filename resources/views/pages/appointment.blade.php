@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Időpontfoglalás',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Időpontfoglalás', 'url' => route('appointment')]
        ],
    ]
    ])

    @if($errors->any())
        <div class="shop-validation-error">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif



    <form method="POST" action="{{ route('appointment.post') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box">
        @csrf

        <h4 class="mb-4 text-center">Kérem, töltse ki az adatokat</h4>

        <div class="mb-3">
            <label for="name" class="form-label">Név*</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Teljes név" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Telefonszám*</label>
            <input type="text" name="phone" id="phone" class="form-control" placeholder="+36..." required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail cím*</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="pelda@email.hu" required>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="zip_code" class="form-label">Irányítószám*</label>
                <input type="text" name="zip_code" id="zip_code" class="form-control" placeholder="1234" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="city" class="form-label">Város*</label>
                <input type="text" name="city" id="city" class="form-control" placeholder="Budapest" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="address_line" class="form-label">Cím*</label>
                <input type="text" name="address_line" id="address_line" class="form-control" placeholder="Utca, házszám" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="appointment_type" class="form-label" style="display:block">Kérem, válasszon az alábbi felsorolásból*</label>
            <select name="appointment_type" id="appointment_type" class="form-control" required>
                <option value="" disabled selected>Válasszon típust</option>
                <option value="Karbantartás">Karbantartás</option>
                <option value="Felmérés">Ingyenes helyszíni felmérés</option>
                <option value="Egyéb">Egyéb</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label" style="display:block">Megjegyzés</label>
            <textarea name="message" id="message" class="form-control" rows="3" placeholder="Ide írja a megjegyzését..."></textarea>
        </div>

        <button type="submit" class="site-btn w-100">Foglalás elküldése</button>
    </form>


@endsection
