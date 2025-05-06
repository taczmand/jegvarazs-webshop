@extends('layouts.app')

@section('hero')
    @include('partials.hero', ['showHeroItem' => true])
@endsection

@section('content')
    @include('partials.categories')
@endsection
