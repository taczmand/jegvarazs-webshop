<!-- Hero Section Begin -->
<section class="hero {{ $extra_class ?? '' }}">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="hero__categories">
                    <div class="hero__categories__all">
                        <i class="fa fa-bars"></i>
                        <span>Termékek</span>
                    </div>
                    <ul class="category-menu list-unstyled">
                        @foreach ($categories as $category)
                            <li class="category-item">
                                <a href="{{ route('products.resolve', ['slugs' => $category->getFullSlug()]) }}" class="category-link">
                                    <div class="category-header">
                                        {{ $category->title }}
                                        @if($category->children->count())
                                            <button class="subcategory-toggle" aria-label="Almenü megnyitása">
                                                <i class="fa fa-chevron-down"></i>
                                            </button>
                                        @endif
                                    </div>
                                </a>

                                @if($category->children->count())
                                    <div class="subcategory-container">
                                        <div class="subcategory-grid">
                                            @foreach ($category->children as $sub)
                                                <div class="subcategory-item">
                                                    <a href="{{ route('products.resolve', ['slugs' => $sub->getFullSlug()]) }}" class="subcategory-link level-1">
                                                        {{ $sub->title }}
                                                    </a>

                                                    @if($sub->children->count())
                                                        <div class="subcategory-sublist">
                                                            @foreach ($sub->children as $child)
                                                                <a href="{{ route('products.resolve', ['slugs' => $child->getFullSlug()]) }}" class="subcategory-link level-2">
                                                                    {{ $child->title }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>



                </div>
            </div>
            <div class="col-lg-9">
                <div class="hero__search" style="overflow: visible; position: relative;">
                    <div class="hero__search__form">
                        <form action="{{ route('search') }}" method="GET">
                            <input id="search-autocomplete-input" type="text" placeholder="Kereséshez gépeljen ide..." name="query" value="{{ request()->input('query') }}" autocomplete="off">
                            <button type="submit" class="site-btn">Keresés</button>
                        </form>
                    </div>
                    <div id="search-autocomplete" style="position: relative; z-index: 20000; overflow: visible; clear: both;">
                        <div id="search-autocomplete-panel" style="display:none; position:absolute; top:0; margin-top: 8px; background:#fff; border:1px solid rgba(0,0,0,.1); border-radius:6px; z-index: 20001; padding: 10px;"></div>
                    </div>
                    <div class="hero__search__phone">
                        <div class="hero__search__phone__icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="hero__search__phone__text">
                            <h5><a href="tel:{{ $basicdata['support_phone'] ?? '' }}" class="text-dark">{{ $basicdata['support_phone'] ?? '' }}</a></h5>
                            <span>Várjuk hívását!</span>
                        </div>
                    </div>
                </div>

                {{-- Hero Item dinamikusan --}}
                @if (!empty($showHeroItem))
                    @include('partials.heroitem')
                @endif

            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const input = document.getElementById('search-autocomplete-input');
        const panel = document.getElementById('search-autocomplete-panel');
        if (!input || !panel) {
            return;
        }

        let timer = null;
        let lastQuery = '';
        let activeRequest = null;

        function hide() {
            panel.style.display = 'none';
            panel.innerHTML = '';
        }

        function syncPanelWidth() {
            const r = input.getBoundingClientRect();
            const containerRect = panel.parentElement.getBoundingClientRect();

            panel.style.width = r.width + 'px';
            panel.style.left = (r.left - containerRect.left) + 'px';
        }

        function escapeHtml(str) {
            return String(str)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function render(data) {
            const categories = Array.isArray(data.categories) ? data.categories : [];
            const products = Array.isArray(data.products) ? data.products : [];

            if (categories.length === 0 && products.length === 0) {
                hide();
                return;
            }

            let html = '';
            if (categories.length > 0) {
                html += '<div style="font-weight:600; margin-bottom:6px;">Kategóriák</div>';
                html += '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px;">';
                for (const c of categories) {
                    const img = c.image ? ('<img src="' + escapeHtml(c.image) + '" alt="" style="width:18px; height:18px; object-fit:cover; border-radius:4px; border:1px solid rgba(0,0,0,.08);" />') : '';
                    html += '<a href="' + escapeHtml(c.url) + '" style="display:inline-flex; gap:6px; align-items:center; padding:4px 8px; border:1px solid rgba(0,0,0,.1); border-radius:999px; font-size:12px; color:#111; text-decoration:none;">' + img + '<span>' + escapeHtml(c.title) + '</span></a>';
                }
                html += '</div>';
            }

            if (products.length > 0) {
                html += '<div style="font-weight:600; margin-bottom:6px;">Termékek</div>';
                html += '<div style="display:flex; flex-direction:column; gap:8px;">';
                for (const p of products) {
                    let tagsHtml = '';
                    if (Array.isArray(p.tags) && p.tags.length > 0) {
                        tagsHtml += '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:4px;">';
                        for (const t of p.tags.slice(0, 3)) {
                            tagsHtml += '<span style="display:inline-block; padding:2px 6px; border:1px solid rgba(0,0,0,.1); border-radius:999px; font-size:11px; color:#555;">' + escapeHtml(t) + '</span>';
                        }
                        tagsHtml += '</div>';
                    }

                    html += '<a href="' + escapeHtml(p.url) + '" style="display:flex; gap:10px; align-items:center; text-decoration:none; color:#111;">'
                        + '<img src="' + escapeHtml(p.image) + '" alt="" style="width:42px; height:42px; object-fit:cover; border-radius:6px; border:1px solid rgba(0,0,0,.08);" />'
                        + '<div style="display:flex; flex-direction:column;">'
                        + '<div style="font-size:13px; line-height:1.2;">' + escapeHtml(p.title) + '</div>'
                        + tagsHtml
                        + '</div>'
                        + '</a>';
                }
                html += '</div>';
            }

            const q = (input.value || '').trim();
            if (q.length >= 2) {
                html += '<div style="margin-top:10px; padding-top:10px; border-top:1px solid rgba(0,0,0,.08);">'
                    + '<a href="{{ route('search') }}?query=' + encodeURIComponent(q) + '" style="font-weight:600; color:#19ACE2; text-decoration:none;">Az összes eredmény megtekintése</a>'
                    + '</div>';
            }

            panel.innerHTML = html;
            syncPanelWidth();
            panel.style.display = 'block';
        }

        async function fetchSuggest(q) {
            if (activeRequest && typeof activeRequest.abort === 'function') {
                activeRequest.abort();
            }
            const ctrl = new AbortController();
            activeRequest = ctrl;

            const url = '{{ route('search.autocomplete') }}' + '?query=' + encodeURIComponent(q);
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: ctrl.signal,
            });
            if (!res.ok) {
                hide();
                return;
            }
            const data = await res.json();
            render(data);
        }

        input.addEventListener('input', function () {
            const q = (input.value || '').trim();
            lastQuery = q;

            if (timer) {
                clearTimeout(timer);
            }

            if (q.length < 2) {
                hide();
                return;
            }

            timer = setTimeout(function () {
                fetchSuggest(lastQuery).catch(function () {
                    hide();
                });
            }, 220);
        });

        window.addEventListener('resize', function () {
            if (panel.style.display === 'block') {
                syncPanelWidth();
            }
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== input) {
                hide();
            }
        });
    })();
</script>
<!-- Hero Section End -->
