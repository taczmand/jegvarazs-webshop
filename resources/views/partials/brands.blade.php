<section class="about-section py-5 bg-light mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title from-blog__title">
                    <h2 class="mb-5">Kiemelt márkáink</h2>

                    @php
                        $chunks = $brands->chunk(6); // max 6 márka soronként
                    @endphp

                    @foreach($chunks as $chunk)
                        <div class="row justify-content-center">
                            @foreach($chunk as $brand)
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4 d-flex justify-content-center">
                                    <div class="brand__item text-center" style="height: 100px;">
                                        <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <img src="{{ asset('storage/' . ($brand->logo ?? 'static_media/no-image.jpg')) }}"
                                                 alt="{{ $brand->title }}"
                                                 class="img-fluid"
                                                 style="max-height: 60px; object-fit: contain;">
                                        </div>
                                        <h6 class="mt-2 text-truncate">{{ $brand->name }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</section>
