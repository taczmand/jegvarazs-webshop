@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection


@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Rólunk',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Rólunk', 'url' => route('about')]
        ],
    ]
    ])
@endsection
