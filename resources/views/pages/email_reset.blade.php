@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Elfelejtett jelszó',
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

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="w-100 p-4 bg-light rounded shadow-sm light-box">
        @csrf
        <div class="mb-3">
            <label>E-mail címed:</label>
            <input type="email" name="email" class="form-control"  required>
        </div>

        <button type="submit" class="site-btn w-100">Elküldés</button>
    </form>

@endsection


