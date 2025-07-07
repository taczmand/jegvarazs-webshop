@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['showHeroItem' => true])
@endsection

@section('content')
    @include('partials.categories')
    @include('partials.about')
    @include('partials.blogs')
    @include('partials.brands')
@endsection

