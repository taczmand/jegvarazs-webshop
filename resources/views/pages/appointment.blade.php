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
    Fejlesztés alatt...
@endsection
