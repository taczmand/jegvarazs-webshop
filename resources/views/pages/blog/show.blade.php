@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection



@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'cover_image' => asset('storage/' . $blog->featured_image),
        'page_title' => $blog->title,
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Blog', 'url' => route('blog')]
        ],
    ]
    ])
    <!-- Blog Details Section Begin -->
    <section class="blog-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="blog__details__text">
                        <h3>{{ $blog->title }}</h3>

                        {!! $blog->content !!}

                    </div>
                    <small class="text-muted">
                        <i class="fa fa-calendar-o"></i> {{ $blog->created_at->format('Y-m-d') }}
                    </small>
                </div>
            </div>
        </div>
@endsection
