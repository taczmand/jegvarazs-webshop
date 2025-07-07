<li class="category-item">
    <a href="{{ route('products.resolve', ['slugs' => $category->getFullSlug()]) }}">
        {{ $category->title }}
    </a>

    @if($category->children->count())
        <ul class="subcategory-list">
            @foreach($category->children as $child)
                @include('partials.category-item', ['category' => $child])
            @endforeach
        </ul>
    @endif
</li>
