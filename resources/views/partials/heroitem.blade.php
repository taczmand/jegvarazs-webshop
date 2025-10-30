<div class="hero__item set-hero-bg" data-setbg="{{ asset('storage/' . ($basicmedia['hero_image'] ?? 'static_media/no-image.jpg')) }}">
    <div class="hero__text">
        <span>{!! $basicdata['hero_top_title'] ?? '' !!} </span>
        <h2>{!! $basicdata['hero_main_title'] ?? '' !!}</h2>
        <p>{!! $basicdata['hero_subtitle'] ?? '' !!}</p>
        @guest('customer')
          <a href="{{ route('registration') }}" class="cta-btn">Regisztráció</a>
        @endguest

    </div>
</div>
<div class="row mt-3" style="width:95%; padding-left: 5%">
    <div class="categories__slider owl-carousel">
        @php
            // 🔹 Segédfüggvény: megkeresi rekurzívan az első képes terméket
            function findFirstProductWithPhotoRecursive($category)
            {
                // 1️⃣ Először a kategória saját termékeit nézzük
                $product = $category->products()
                    ->where('status', 'active')
                    ->whereHas('photos')
                    ->with(['photos' => function($q) {
                        $q->orderBy('id', 'asc');
                    }])
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($product && $product->photos->isNotEmpty()) {
                    return $product->photos->first();
                }

                // 2️⃣ Ha nincs ilyen termék, nézzük a gyerek kategóriákat rekurzívan
                foreach ($category->children as $child) {
                    $photo = findFirstProductWithPhotoRecursive($child);
                    if ($photo) {
                        return $photo;
                    }
                }

                // 3️⃣ Nincs sehol fotó
                return null;
            }
        @endphp


        @foreach($all_categories as $category)
            @php
                $photo = 'static_media/no-image.jpg';

                // 🔹 Kép keresése az adott kategóriában és gyerekeiben
                $photoObj = findFirstProductWithPhotoRecursive($category);

                if ($photoObj && !empty($photoObj->path)) {
                    $photo = 'storage/' . ltrim($photoObj->path, '/');
                }
            @endphp

            <div class="col-lg-3 pr-3">
                <div class="categories__item set-bg" data-setbg="{{ asset($photo) }}">
                    <h5>
                        <a href="{{ route('products.resolve', ['slugs' => $category->getFullSlug()]) }}">
                            {{ $category->title }}
                        </a>
                    </h5>
                </div>
            </div>
        @endforeach

    </div>
</div>
