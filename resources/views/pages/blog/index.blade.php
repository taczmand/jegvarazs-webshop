@extends('layouts.shop')

@section('hero')
    @include('partials.hero', ['extra_class' => 'hero-normal'])
@endsection



@section('content')
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        'page_title' => 'Blog',
        'nav' => [
            ['title' => 'Főoldal', 'url' => route('index')],
            ['title' => 'Blog', 'url' => route('blog')]
        ],
    ]
    ])

    <!-- Blog Section Begin -->
    <section class="blog spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">

                        <div class="row">
                            @foreach ($blogs as $blog)
                                <div class="col-lg-6 col-md-6 col-sm-6 mb-4">
                                    <div class="blog__item">
                                        <div class="blog__item__pic">
                                            <img src="{{ asset('storage/' . ($blog->featured_image ?? 'static_media/no-image.jpg')) }}" alt="{{ $blog->title }}">
                                        </div>
                                        <div class="blog__item__text">
                                            <ul>
                                                <li><i class="fa fa-calendar-o"></i> {{ $blog->created_at->format('Y. M d') }}</li>
                                            </ul>
                                            <h5><a href="{{ route('blog.post', $blog->slug) }}">{{ $blog->title }}</a></h5>
                                            @php
                                                $content = strip_tags($blog->content);
                                                if (strlen($content) > 500) {
                                                    $cutPosition = strrpos(substr($content, 0, 500), ' ');
                                                    $shortContent = substr($content, 0, $cutPosition) . '...';
                                                } else {
                                                    $shortContent = $content;
                                                }
                                            @endphp
                                            <p>{!! $shortContent !!}</p>
                                            <a href="{{ route('blog.post', $blog->slug) }}" class="blog__btn">
                                                Tovább <span class="arrow_right"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-lg-12">
                            <div class="product__pagination blog__pagination d-flex justify-content-center">
                                {{ $blogs->links('pagination::bootstrap-4') }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Blog Section End -->

@endsection
