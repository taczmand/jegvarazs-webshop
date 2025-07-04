@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rólunk',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Időpontfoglalás', 'url' => route('appointment')]
        ],
    ]
    ])

    @if($errors->any())
        <div style="color:red;">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif



    <form method="POST" action="{{ route('appointment.post') }}" class="w-100" style="max-width: 400px; margin: auto;">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Név*</label>
            <input type="text" name="name" id="name" class="form-control" value="" placeholder="" required>
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
            <label for="zip_code" class="form-label">Irányítószám*</label>
            <input type="text" name="zip_code" id="zip_code" class="form-control" value="" placeholder="" required>
        </div>
        <div class="mb-3">
            <label for="city" class="form-label">Város*</label>
            <input type="email" name="city" id="city" class="form-control" value="" placeholder="" required>
        </div>
        <div class="mb-3">
            <label for="address_line" class="form-label">Cím*</label>
            <input type="email" name="address_line" id="address_line" class="form-control" value="" placeholder="" required>
        </div>

        <div class="mb-3">
            <label for="address_line" class="form-label">Kérem, válasszon az alábbi felsorolásból*</label>
            <select name="appointment_type" id="appointment_type" class="form-control" required>
                <option value="Karbantartás">Karbantartás</option>
                <option value="Felmérés">Felmérés</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="message class="form-label">Megjegyzés</label>
            <textarea name="message" id="message" class="form-control" rows="3" placeholder="Ide írja a megjegyzését..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">Foglalás elküldése</button>
    </form>

@endsection
