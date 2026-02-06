@extends('layouts.shop')

@section('content')

    @if($errors->any())
        <div class="shop-validation-error">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>

        <script>
            if (typeof fbq === 'function') {
                fbq('track', 'Lead');
            }
        </script>
    @endif

    <script src="https://www.google.com/recaptcha/api.js"></script>

    <script>
        function onSubmit(token) {
            document.getElementById("contact-form").submit();
        }
    </script>


    <form method="POST" id="contact-form" action="{{ route('offer.post') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box mb-5">
        @csrf

        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

        <h4 class="mb-4 text-center">Kérem, töltse ki az adatokat</h4>
        <p class="text-center">Ajánlatkérés</p>

        <div class="mb-3">
            <label for="name" class="form-label">Név*</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Teljes név" value="{{ request()->query('full_name') }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Telefonszám*</label>
            <input type="text" name="phone" id="phone" class="form-control" value="{{ request()->query('phone') }}" placeholder="+36..." required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail cím*</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ request()->query('email_address') }}" placeholder="pelda@email.hu" required>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="zip_code" class="form-label">Irányítószám*</label>
                <input type="text" name="zip_code" id="zip_code" class="form-control" value="{{ request()->query('zip') }}" placeholder="1234" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="city" class="form-label">Város*</label>
                <input type="text" name="city" id="city" class="form-control" value="{{ request()->query('city') }}" placeholder="Budapest" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="address_line" class="form-label">Cím*</label>
                <input type="text" name="address_line" id="address_line" class="form-control" value="{{ request()->query('address') }}" placeholder="Utca, házszám" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label" style="display:block">Megjegyzés</label>
            <textarea name="message" id="message" class="form-control" rows="3" placeholder="Ide írja a megjegyzését..."></textarea>
        </div>

        <button data-sitekey="6Le1aA4sAAAAAPRyuiMD79NOT2oYekHfOdhNC6Fr"
                data-callback='onSubmit'
                data-action='submit' type="submit" class="g-recaptcha site-btn w-100">Ajánlatkérés elküldése</button>
    </form>


@endsection
