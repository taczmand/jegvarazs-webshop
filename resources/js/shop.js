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
        const cartCountElement = document.getElementById('cart_count');
        if (cartCountElement) {
            cartCountElement.textContent = cartData.summary.total_items ? cartData.summary.total_items : 0;
        }

        const cartTotalElement = document.getElementById('cart_total_item_amount');
        if (cartTotalElement) {
            const price = cartData.summary.total_price ?? 0;
            cartTotalElement.textContent = formatter.format(price);
        }
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
    $(".product__details__pic__slider").owlCarousel({
        loop: true,
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
    $("select").niceSelect();

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
		Quantity change
	--------------------- */
    var proQty = $('.pro-qty');
    proQty.prepend('<span class="dec qtybtn" id="dec_qty">-</span>');
    proQty.append('<span class="inc qtybtn" id="inc_qty">+</span>');
    proQty.on('click', '.qtybtn', function () {
        var $button = $(this);
        var oldValue = $button.parent().find('input').val();
        let itemId = $button.parent().data('item-id');

        if ($button.hasClass('inc')) {
            var newVal = parseFloat(oldValue) + 1;
            changeQuantity(itemId, newVal);
        } else {
            // Don't allow decrementing below zero
            if (oldValue > 1) {
                var newVal = parseFloat(oldValue) - 1;
                changeQuantity(itemId, newVal);
            } else {
                newVal = 1;
            }
        }
        $button.parent().find('input').val(newVal);
    });

    window.showToast = function(message, type = 'success') {
        const toastEl = document.getElementById('globalToast');
        const toastBody = document.getElementById('globalToastMessage');

        // Típus alapján módosítjuk a háttér színt (Bootstrap 5)
        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
        toastEl.classList.add(`bg-${type}`);

        toastBody.textContent = message;

        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
    };

    window.baseURL = function() {
        const isDev = import.meta.env.MODE === 'development';

        console.log('Futási környezet:', isDev ? 'Fejlesztés' : 'Éles');
        return import.meta.env.VITE_BASE_URL;
    }

})(jQuery);


