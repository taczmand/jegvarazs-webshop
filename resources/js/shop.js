import '@fortawesome/fontawesome-free/css/all.min.css';
import "../css/elegant-icons.css";
import "../css/nice-select.css";
import "../css/jquery-ui.min.css";
import "../css/owl.carousel.min.css";
import "../css/slicknav.min.css";

import '../js/jquery.nice-select.min.js';
import '../js/jquery-ui.min.js';
import '../js/jquery.slicknav.js';
import '../js/mixitup.min.js';
import '../js/owl.carousel.min.js';
import '../js/cart.js';
import * as bootstrap from "bootstrap";

'use strict';

(function ($) {

    /*------------------
        Preloader
    --------------------*/
    $(window).on('load', function () {
        $(".loader").fadeOut();
        $("#preloder").delay(200).fadeOut("slow");

        /*------------------
            Gallery filter
        --------------------*/
        $('.featured__controls li').on('click', function () {
            $('.featured__controls li').removeClass('active');
            $(this).addClass('active');
        });
        if ($('.featured__filter').length > 0) {
            var containerEl = document.querySelector('.featured__filter');
            var mixer = mixitup(containerEl);
        }
        fetchCartSummary();
    });

    /*------------------
        Fetch cart count
    --------------------*/
    window.fetchCartSummary = async function() {
        try {
            const response = await fetch(window.appConfig.APP_URL + 'kosar/osszesito', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json', // 👈 EZ KELL, hogy Laravel ne irányítson át
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin' // 👈 biztosítja, hogy a session cookie is elküldésre kerüljön
            });
            const response_data = await response.json();
            if (response_data.result === 'success') {
                renderAfterAddToCart(response_data);
            }
        } catch (error) {
            console.error('Hiba a kosár lekérésekor:', error);
        }
    }

    function renderAfterAddToCart(cartData) {
        const cartCountElements = document.querySelectorAll('.cart_count');
        cartCountElements.forEach(el => {
            el.textContent = cartData.summary.total_items ?? 0;
        });

        const cartTotalElements = document.querySelectorAll('.cart_total_item_amount');
        const price = cartData.summary.total_price ?? 0;
        cartTotalElements.forEach(el => {
            el.textContent = formatter.format(price);
        });
    }


    const formatter = new Intl.NumberFormat('hu-HU', {
        style: 'currency',
        currency: 'HUF',
        minimumFractionDigits: 0
    });

    /*------------------
        Background Set
    --------------------*/
    $('.set-bg').each(function () {
        var bg = $(this).data('setbg');
        $(this).css('background-image', 'url(' + bg + ')');
    });

    $('.set-hero-bg').each(function () {
        var bg = $(this).data('setbg');
        $(this).css('background-image', 'url(' + bg + ')');
    });

    //Humberger Menu
    $(".humberger__open").on('click', function () {
        $(".humberger__menu__wrapper").addClass("show__humberger__menu__wrapper");
        $(".humberger__menu__overlay").addClass("active");
        $("body").addClass("over_hid");
    });

    $(".humberger__menu__overlay").on('click', function () {
        $(".humberger__menu__wrapper").removeClass("show__humberger__menu__wrapper");
        $(".humberger__menu__overlay").removeClass("active");
        $("body").removeClass("over_hid");
    });

    /*------------------
		Navigation
	--------------------*/
    $(".mobile-menu").slicknav({
        prependTo: '#mobile-menu-wrap',
        allowParentLinks: true
    });

    /*-----------------------
        Categories Slider
    ------------------------*/
    $(".categories__slider").owlCarousel({
        loop: true,
        margin: 0,
        items: 4,
        dots: false,
        nav: true,
        navText: ["<span class='fa fa-angle-left'><span/>", "<span class='fa fa-angle-right'><span/>"],
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
        responsive: {

            0: {
                items: 1,
            },

            480: {
                items: 2,
            },

            768: {
                items: 3,
            },

            992: {
                items: 4,
            }
        }
    });


    $('.hero__categories__all').on('click', function(){
        $('.hero__categories ul').slideToggle(400);
    });

    /*--------------------------
        Latest Product Slider
    ----------------------------*/
    $(".latest-product__slider").owlCarousel({
        loop: true,
        margin: 0,
        items: 1,
        dots: false,
        nav: true,
        navText: ["<span class='fa fa-angle-left'><span/>", "<span class='fa fa-angle-right'><span/>"],
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true
    });

    /*-----------------------------
        Product Discount Slider
    -------------------------------*/
    $(".product__discount__slider").owlCarousel({
        loop: true,
        margin: 0,
        items: 3,
        dots: true,
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
        responsive: {

            320: {
                items: 1,
            },

            480: {
                items: 2,
            },

            768: {
                items: 2,
            },

            992: {
                items: 3,
            }
        }
    });

    /*---------------------------------
        Product Details Pic Slider
    ----------------------------------*/
    const productImageCount = document.querySelectorAll('.owl-carousel .item').length;
    $(".product__details__pic__slider").owlCarousel({

        loop: productImageCount > 1,
        margin: 20,
        items: 4,
        dots: true,
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true
    });

    /*-----------------------
		Price Range Slider
	------------------------ */
    var rangeSlider = $(".price-range"),
        minamount = $("#minamount"),
        maxamount = $("#maxamount"),
        minPrice = rangeSlider.data('min'),
        maxPrice = rangeSlider.data('max');
    rangeSlider.slider({
        range: true,
        min: minPrice,
        max: maxPrice,
        values: [minPrice, maxPrice],
        slide: function (event, ui) {
            minamount.val(ui.values[0] + ' Ft');
            maxamount.val(ui.values[1]) + ' Ft';
        }
    });
    minamount.val(rangeSlider.slider("values", 0) + ' Ft');
    maxamount.val(rangeSlider.slider("values", 1) + ' Ft');

    /*--------------------------
        Select
    ----------------------------*/
    //$("select").niceSelect();

    /*------------------
		Single Product
	--------------------*/
    $('.product__details__pic__slider img').on('click', function () {

        var imgurl = $(this).data('imgbigurl');
        var bigImg = $('.product__details__pic__item--large').attr('src');
        if (imgurl != bigImg) {
            $('.product__details__pic__item--large').attr({
                src: imgurl
            });
        }
    });

    /*-------------------
		Quantity change in cart
	--------------------- */
    var proQty = $('.shoping__cart__quantity .pro-qty');
    proQty.prepend('<span class="dec qtybtn" id="dec_qty">-</span>');
    proQty.append('<span class="inc qtybtn" id="inc_qty">+</span>');
    proQty.on('click', '.qtybtn', async function () {
        var $button = $(this);
        var oldValue = $button.parent().find('input').val();
        let itemId = $button.parent().data('item-id');
        let unitQty = $button.parent().data('unit-qty');

        if ($button.hasClass('inc')) {

            // Növelés

            var newVal = parseFloat(oldValue) + unitQty;
            let isChange = await changeQuantity(itemId, newVal);
            if (isChange) {
                $button.parent().find('input').val(newVal);
                location.reload();
            } else {
                $button.parent().find('input').val(oldValue);
            }
        } else {
            // Csökkentés
            if (oldValue > unitQty) {
                var newVal = parseFloat(oldValue) - unitQty;
                let isChange = await changeQuantity(itemId, newVal);
                if (isChange) {
                    location.reload();
                }
            } else {
                newVal = unitQty;
            }
            $button.parent().find('input').val(newVal);
        }

    });

    /*-------------------
		Quantity change in product details
	--------------------- */
    var proQty = $('.product__details__quantity .pro-qty');
    proQty.prepend('<span class="dec qtybtn" id="dec_qty">-</span>');
    proQty.append('<span class="inc qtybtn" id="inc_qty">+</span>');
    proQty.on('click', '.qtybtn', async function () {
        var $button = $(this);
        var oldValue = $button.parent().find('input').val();
        let unitQty = $button.parent().data('unit-qty');

        if ($button.hasClass('inc')) {

            // Növelés

            var newVal = parseFloat(oldValue) + unitQty;
            $button.parent().find('input').val(newVal);

        } else {
            // Csökkentés
            if (oldValue > unitQty) {
                var newVal = parseFloat(oldValue) - unitQty;
            } else {
                newVal = unitQty;
            }
            $button.parent().find('input').val(newVal);
        }

    });


    window.showToast = function(message, type = 'success', duration = 3000) {
        const container = document.getElementById('myCoolToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'myCoolToast';
        toast.textContent = message;

        // Inline stílus a típus alapján
        switch (type) {
            case 'success':
                toast.style.backgroundColor = '#28a745';
                toast.style.color = '#fff';
                break;
            case 'error':
                toast.style.backgroundColor = '#dc3545';
                toast.style.color = '#fff';
                break;
            case 'info':
                toast.style.backgroundColor = '#17a2b8';
                toast.style.color = '#fff';
                break;
            case 'warning':
                toast.style.backgroundColor = '#ffc107';
                toast.style.color = '#000';
                break;
            default:
                toast.style.backgroundColor = '#333';
                toast.style.color = '#fff';
        }

        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, duration);
    };




    window.baseURL = function() {
        const isDev = import.meta.env.MODE === 'development';

        console.log('Futási környezet:', isDev ? 'Fejlesztés' : 'Éles');
        return import.meta.env.VITE_BASE_URL;
    }

    // ha kattint a tag-filter class-ra, akkor a href alapján szűrje a termékeket
    $(document).on('click', '.tag-filter', function (e) {
        e.preventDefault();
        // figyelembe kell venni, hogy már lehet több tag is kiválasztva, ezért a href alapján kell szűrni
        const filter = $(this).attr('href');
        const selected_tag = $(this).val();

        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        const existingTags = searchParams.get('tag');
        let newTags = [];
        if (existingTags) {
            newTags = existingTags.split(',').filter(tag => tag !== filter);
        }
        if (!newTags.includes(selected_tag)) {
            newTags.push(selected_tag);
        } else {
            // ha már benne van, akkor eltávolítjuk
            newTags = newTags.filter(tag => tag !== selected_tag);
        }
        searchParams.set('tag', newTags.join(','));
        currentUrl.search = searchParams.toString();
        window.location.href = currentUrl.toString();

    });

    // ha kattint a brand-filter class-ra, akkor a href alapján szűrje a termékeket
    $(document).on('click', '.brand-filter', function (e) {
        e.preventDefault();
        // figyelembe kell venni, hogy már lehet több brand is kiválasztva, ezért a href alapján kell szűrni
        const filter = $(this).attr('href');
        const selected_brand = $(this).val();

        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        const existingBrands = searchParams.get('brand');
        let newBrands = [];
        if (existingBrands) {
            newBrands = existingBrands.split(',').filter(brand => brand !== filter);
        }
        if (!newBrands.includes(selected_brand)) {
            newBrands.push(selected_brand);
        } else {
            // ha már benne van, akkor eltávolítjuk
            newBrands = newBrands.filter(brand => brand !== selected_brand);
        }
        searchParams.set('brand', newBrands.join(','));
        currentUrl.search = searchParams.toString();
        window.location.href = currentUrl.toString();

    });

    // ha kattint a attribute-filter class-ra, akkor a href alapján szűrje a termékeket
    $(document).on('click', '.attribute-filter', function (e) {
        e.preventDefault();

        const id = $(this).data('id');
        const value = $(this).data('value');
        const selectedAttr = id + ':' + value;

        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        const existingAttributes = searchParams.get('attribute');

        let newAttributes = existingAttributes ? existingAttributes.split(',') : [];

        if (newAttributes.includes(selectedAttr)) {
            newAttributes = newAttributes.filter(a => a !== selectedAttr);
        } else {
            newAttributes.push(selectedAttr);
        }

        searchParams.set('attribute', newAttributes.join(','));
        currentUrl.search = searchParams.toString();
        window.location.href = currentUrl.toString();
    });



    $(document).on('change', '#sortBy', function (e) {
        e.preventDefault();
        const sortBy = $(this).val();
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);

        // Ha már van 'sortBy' paraméter, akkor frissítjük, különben hozzáadjuk
        if (searchParams.has('sortBy')) {
            searchParams.set('sortBy', sortBy);
        } else {
            searchParams.append('sortBy', sortBy);
        }

        currentUrl.search = searchParams.toString();
        window.location.href = currentUrl.toString();
    })

    $(document).on('change', '#itemsPerPage', function (e) {
        e.preventDefault();
        const itemsPerPage = $(this).val();
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);

        // Ha már van 'itemsPerPage' paraméter, akkor frissítjük, különben hozzáadjuk
        if (searchParams.has('itemsPerPage')) {
            searchParams.set('itemsPerPage', itemsPerPage);
        } else {
            searchParams.append('itemsPerPage', itemsPerPage);
        }

        currentUrl.search = searchParams.toString();
        window.location.href = currentUrl.toString();
    })

    updateTagColors();
    updateBrandColors();
    updateAttributeColors();

    function updateTagColors(){
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        const existingTags = searchParams.get('tag');

        if (existingTags) {
            const tags = existingTags.split(',');

            $('.tag-filter').each(function() {
                const tag = $(this).val();
                if (tags.includes(tag)) {
                    $('#tag_label_'+tag).css({'background-color': '#007bff', 'color': 'white'}); // vagy bármilyen szín, ami jelzi, hogy kiválasztott
                } else {
                    $('#tag_label_'+tag).css('background-color', '#f5f5f5'); // alapértelmezett szín
                }
            });
        }
    }

    function updateBrandColors(){
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        const existingBrands = searchParams.get('brand');

        if (existingBrands) {
            const brands = existingBrands.split(',');

            $('.brand-filter').each(function() {
                const brand = $(this).val();
                if (brands.includes(brand)) {
                    $('#brand_label_'+brand).css({'background-color': '#007bff', 'color': 'white'}); // vagy bármilyen szín, ami jelzi, hogy kiválasztott
                } else {
                    $('#brand_label_'+brand).css({'background-color': '#f5f5f5', 'color': 'white'}); // alapértelmezett szín
                }
            });
        }
    }

    function updateAttributeColors(){
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        const existingAttributes = searchParams.get('attribute');

        if (existingAttributes) {
            const attributes = existingAttributes.split(',');

            $('.attribute-label').each(function() {
                const attrKey = $(this).data('attrkey');

                if (attributes.includes(attrKey)) {
                    $(this).css({'background-color': '#007bff', 'color': 'white'});
                } else {
                    $(this).css({'background-color': '#f5f5f5', 'color': 'black'});
                }
            });
        }
    }





    window.addEventListener('click', async function (event) {
        const subscriptionBtn = event.target.closest('[data-subscribe-button]');

        if (!subscriptionBtn) return;

        event.preventDefault();

        const emailInput = document.getElementById('subscription_email');

        if (!emailInput || !emailInput.value.trim()) {
            showToast('Kérjük, adja meg az e-mail címét!', 'error');
            return;
        }

        try {
            const response = await fetch(window.appConfig.APP_URL + 'newsletter/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    email: emailInput.value.trim()
                })
            });

            const res = await response.json();

            if (res.result === 'success') {
                showToast(res.message, 'success');
                emailInput.value = '';
            } else {
                showToast(res.message || 'Ismeretlen hiba történt.', 'error');
            }

        } catch (error) {
            showToast('Hálózati hiba történt.', 'error');
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('contact_form');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const contact_name = document.getElementById('contact_name');
            const contact_email = document.getElementById('contact_email');
            const contact_message = document.getElementById('contact_message');

            if (!contact_name || !contact_name.value.trim()) {
                showToast('Kérjük, adja meg a teljes nevét!', 'error');
                return;
            }

            if (!contact_email || !contact_email.value.trim()) {
                showToast('Kérjük, adja meg az e-mail címét!', 'error');
                return;
            }

            const formData = new FormData(form);

            fetch(window.appConfig.APP_URL + 'contact/add', {
                method: 'POST',
                body: JSON.stringify({
                    contact_name: contact_name.value.trim(),
                    contact_email: contact_email.value.trim(),
                    contact_message: contact_message.value.trim()
                }),

                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Hiba történt a beküldés során.');
                    }
                    return response.json(); // vagy .text() ha nem JSON-t vársz vissza
                })
                .then(data => {
                    if (data.result !== 'success') {
                        throw new Error(data.error_message || 'Ismeretlen hiba történt.');
                    }
                    showToast(data.message, 'success');
                    form.reset();
                })
                .catch(error => {
                    showToast(error || 'Ismeretlen hiba történt.', 'error');
                });
        });
    });

    document.querySelectorAll('.subcategory-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.closest('.category-item');
            parent.classList.toggle('active');
        });
    });


})(jQuery);


