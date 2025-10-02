@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['showHeroItem' => true])
@endsection

@section('content')
    @include('partials.blogs')
    @include('partials.about')
    @include('partials.brands')
@endsection

