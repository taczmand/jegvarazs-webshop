<!-- Blog Section Begin -->
    <section class="from-blog spad">
        <div class="container">

            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title from-blog__title">
                        <h2>Legfrissebb bejegyzéseink</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach($last_blogs as $blog)
                    <div class="col-lg-4 col-md-4 col-sm-6 mb-4">
                        <div class="blog__item h-100">
                            <div class="blog__item__pic">
                                <img src="{{ asset('storage/' . ($blog->featured_image ?? 'static_media/no-image.jpg')) }}" alt="{{ $blog->title }}">
                            </div>
                            <div class="blog__item__text">
                                <ul class="mb-2">
                                    <li><i class="fa fa-calendar-o"></i> {{ $blog->created_at->format('Y-m-d') }}</li>
                                    <li><i class="fa fa-comment-o"></i> {{ $blog->comments_count ?? 0 }}</li>
                                </ul>
                                <h5><a href="{{ route('blog.post', $blog->slug) }}">{{ $blog->title }}</a></h5>
                                <p>{{ Str::limit(strip_tags($blog->content), 100) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <!-- Blog Section End -->
