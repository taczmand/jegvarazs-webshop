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

    @php
        $ctaTitle = trim((string) ($blog->cta_title ?? ''));
        $ctaUrl = trim((string) ($blog->cta_url ?? ''));
        $hasCta = $ctaTitle !== '' && $ctaUrl !== '' ;
    @endphp

    <style>
        .blog-cta-wrap {
            margin: 18px 0;
        }

        .blog-cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            background: #0f1a2b;
            border: 1px solid rgba(255, 255, 255, .10);
            color: #ffffff;
            transition: box-shadow .2s ease, filter .2s ease;
            will-change: filter;
        }

        .blog-cta-btn::after {
            content: '';
            position: absolute;
            top: -20%;
            left: -60%;
            width: 60%;
            height: 140%;
            background: linear-gradient(
                115deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, .55) 45%,
                rgba(255, 255, 255, 0) 90%
            );
            transform: translateX(0);
            animation: blogCtaWave 2.6s ease-in-out infinite;
            pointer-events: none;
        }

        .blog-cta-btn:hover,
        .blog-cta-btn:focus {
            filter: brightness(1.02);
            box-shadow: 0 10px 18px rgba(0, 0, 0, .12);
        }

        .blog-cta-btn:hover::after,
        .blog-cta-btn:focus::after {
            animation-play-state: paused;
            opacity: .35;
        }

        @keyframes blogCtaWave {
            0% {
                transform: translateX(0);
                opacity: .25;
            }
            45% {
                opacity: .55;
            }
            100% {
                transform: translateX(240%);
                opacity: .25;
            }
        }
    </style>
    <!-- Blog Details Section Begin -->
    <section class="blog-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="blog__details__text">
                        <h3>{{ $blog->title }}</h3>

                        @if($hasCta)
                            <div class="blog-cta-wrap">
                                <a href="{{ $ctaUrl }}" class="site-btn blog-cta-btn">{{ $ctaTitle }} <span class="arrow_right"></span></a>
                            </div>
                        @endif

                        {!! $blog->content !!}

                        @if($hasCta)
                            <div class="blog-cta-wrap">
                                <a href="{{ $ctaUrl }}" class="site-btn blog-cta-btn">{{ $ctaTitle }} <span class="arrow_right"></span></a>
                            </div>
                        @endif

                    </div>
                    <small class="text-muted">
                        <i class="fa fa-calendar-o"></i> {{ $blog->created_at->format('Y-m-d') }}
                    </small>
                </div>
            </div>
        </div>
@endsection
