@extends('layouts.admin')

@section('content')


    <div class="container p-0">

        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 text-gray-800 mb-0">Ügyviteli folyamatok / Munkalap létrehozása</h1>
        </div>

        <!-- TODO: Létrehozáshoz form inputok, illetve szerződés hozzárendelési lehetősége -->

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Munkalap megnevezése*</label>
                <input type="text" class="form-control" name="contact_name" id="contact_name" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Ország*</label>
                <select name="contact_country" class="form-control w-100" id="contact_country">
                    @foreach(config('countries') as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script type="module">

    </script>
@endsection
