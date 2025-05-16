<!-- Breadcrumb Section Begin -->
<section class="breadcrumb-section set-bg" data-setbg="{{ asset('static_media/william-christen-o4xVOHa3FXw-unsplash.jpg') }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="breadcrumb__text">
                    <h2>{{ $breadcrumbs['page_title'] }}</h2>
                    <div class="breadcrumb__option">
                        @foreach ($breadcrumbs['nav'] as $item)
                            <a href="{{ $item['url'] }}"><span>{{ $item['title'] }}</span></a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Breadcrumb Section End -->
