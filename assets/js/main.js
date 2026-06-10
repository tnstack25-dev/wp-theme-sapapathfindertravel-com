(function ($) {
    'use strict';

    $(function () {
        var $bookingCard = $('.tour-booking-card');
        var $priceBox = $bookingCard.find('.tour-price');
        var originalPriceHtml = $priceBox.html();
        var originalBasePrice = Number($priceBox.data('base-price')) || 0;
        var currentBasePrice = originalBasePrice;
        var currentPriceHtml = originalPriceHtml;
        var currentTravelersLimit = 0;
        var pulseTimer = null;

        if ($bookingCard.length && $priceBox.length) {
            function formatTourPrice(price) {
                var decimals = Number($priceBox.data('price-decimals'));
                var symbol = $priceBox.data('currency-symbol') || '$';
                var position = $priceBox.data('currency-position') || 'left';
                var amount = Number(price || 0).toLocaleString(undefined, {
                    minimumFractionDigits: Number.isFinite(decimals) ? decimals : 2,
                    maximumFractionDigits: Number.isFinite(decimals) ? decimals : 2
                });

                if (position === 'right') {
                    return amount + symbol;
                }

                if (position === 'right_space') {
                    return amount + ' ' + symbol;
                }

                if (position === 'left_space') {
                    return symbol + ' ' + amount;
                }

                return symbol + amount;
            }

            function getTourPriceMultiplier() {
                var $fields = $('.tour-extra-fields');
                var discount23 = Math.max(0, Number($fields.data('discount-2-3')) || 75);
                var discount45 = Math.max(0, Number($fields.data('discount-4-5')) || 50);
                var discount67 = Math.max(0, Number($fields.data('discount-6-7')) || 25);
                var adults = Math.max(1, Number($('#tour_adults').val()) || 1);
                var children23 = Math.max(0, Number($('#tour_children_2_3').val()) || 0);
                var children45 = Math.max(0, Number($('#tour_children_4_5').val()) || 0);
                var children67 = Math.max(0, Number($('#tour_children_6_7').val()) || 0);

                return Math.max(
                    1,
                    adults +
                    (children23 * Math.max(0, 100 - discount23) / 100) +
                    (children45 * Math.max(0, 100 - discount45) / 100) +
                    (children67 * Math.max(0, 100 - discount67) / 100)
                );
            }

            function getSelectedRoomPrice() {
                var $room = $('#tour_room_type');
                var price = Number($room.find('option:selected').data('room-price'));

                return Number.isFinite(price) ? price : 0;
            }

            function parseTravelersLimit(value) {
                var match = String(value || '').match(/\d+/);
                return match ? Number(match[0]) : 0;
            }

            function getSelectedTravelersLimit(variation) {
                var limit = 0;

                if (variation && variation.attributes) {
                    Object.keys(variation.attributes).some(function (key) {
                        if (key.toLowerCase().indexOf('travelers') !== -1) {
                            limit = parseTravelersLimit(variation.attributes[key]);
                            return true;
                        }

                        return false;
                    });
                }

                if (!limit) {
                    $('.variations_form select[name*="travelers"], .variations_form select[name*="Travelers"]').each(function () {
                        limit = parseTravelersLimit(this.value || $(this).find('option:selected').text());
                        return !limit;
                    });
                }

                return limit;
            }

            function getTourTravelerInputs() {
                return $('#tour_adults, #tour_infants, #tour_children_2_3, #tour_children_4_5, #tour_children_6_7');
            }

            function getTotalTravelers() {
                var total = 0;

                getTourTravelerInputs().each(function () {
                    total += Math.max(0, Number(this.value) || 0);
                });

                return total;
            }

            function updateTravelersNotice() {
                var $notice = $bookingCard.find('.tour-travelers-limit-note');
                var total = getTotalTravelers();

                if (!$notice.length) {
                    $notice = $('<div class="tour-travelers-limit-note" aria-live="polite"></div>');
                    $('.tour-extra-fields').append($notice);
                }

                if (currentTravelersLimit) {
                    $notice
                        .toggleClass('is-over-limit', total > currentTravelersLimit)
                        .text('Travelers selected: ' + total + ' / ' + currentTravelersLimit);
                } else {
                    $notice
                        .removeClass('is-over-limit')
                        .text('Please choose Travelers before confirming the booking.');
                }
            }

            function enforceTravelersLimit(changedInput) {
                var $inputs = getTourTravelerInputs();

                if (!currentTravelersLimit) {
                    $inputs.removeAttr('max');
                    updateTravelersNotice();
                    return;
                }

                $inputs.each(function () {
                    var otherTotal = getTotalTravelers() - (Math.max(0, Number(this.value) || 0));
                    var min = Number($(this).attr('min')) || 0;
                    var max = Math.max(min, currentTravelersLimit - otherTotal);
                    $(this).attr('max', max);
                });

                var total = getTotalTravelers();

                if (total > currentTravelersLimit && changedInput) {
                    var $changed = $(changedInput);
                    var currentValue = Math.max(0, Number($changed.val()) || 0);
                    var minValue = Number($changed.attr('min')) || 0;
                    var reducedValue = Math.max(minValue, currentValue - (total - currentTravelersLimit));
                    $changed.val(reducedValue);

                    $changed.addClass('is-limit-adjusted');
                    window.setTimeout(function () {
                        $changed.removeClass('is-limit-adjusted');
                    }, 650);
                }

                updateTravelersNotice();
            }

            function pulseTourPrice() {
                $priceBox.addClass('is-price-pulse');
                window.clearTimeout(pulseTimer);
                pulseTimer = window.setTimeout(function () {
                    $priceBox.removeClass('is-price-pulse');
                }, 850);
            }

            function renderTourCalculatedPrice() {
                if (!currentBasePrice) {
                    return;
                }

                var multiplier = getTourPriceMultiplier();
                var roomPrice = getSelectedRoomPrice();
                var total = (currentBasePrice * multiplier) + roomPrice;

                if (multiplier === 1 && roomPrice <= 0 && currentPriceHtml) {
                    $priceBox.html(currentPriceHtml);
                } else {
                    $priceBox.html(
                        '<span class="tour-calculated-price">' + formatTourPrice(total) + '</span>' +
                        '<small class="tour-price-note">' +
                        formatTourPrice(currentBasePrice) + ' x ' + multiplier.toFixed(2).replace(/\.?0+$/, '') +
                        ' billable travelers' +
                        (roomPrice > 0 ? ' + room ' + formatTourPrice(roomPrice) : '') +
                        (currentTravelersLimit ? ' / max ' + currentTravelersLimit : '') +
                        '</small>'
                    );
                }

                pulseTourPrice();
            }

            $('.variations_form')
                .on('found_variation', function (event, variation) {
                    if (variation) {
                        if (variation.display_price !== undefined) {
                            currentBasePrice = Number(variation.display_price) || originalBasePrice;
                        }

                        if (variation.price_html) {
                            currentPriceHtml = variation.price_html;
                        }
                    }

                    currentTravelersLimit = getSelectedTravelersLimit(variation);
                    enforceTravelersLimit();
                    $priceBox.addClass('is-variation-priced');
                    renderTourCalculatedPrice();
                })
                .on('reset_data hide_variation', function () {
                    currentBasePrice = originalBasePrice;
                    currentPriceHtml = originalPriceHtml;
                    currentTravelersLimit = 0;
                    enforceTravelersLimit();
                    $priceBox
                        .removeClass('is-variation-priced is-price-pulse');
                    renderTourCalculatedPrice();
                });

            $('.variations_form').on('change', 'select', function () {
                currentTravelersLimit = getSelectedTravelersLimit();
                enforceTravelersLimit();
                renderTourCalculatedPrice();
            });

            $bookingCard.on('input change', '#tour_adults, #tour_infants, #tour_children_2_3, #tour_children_4_5, #tour_children_6_7, #tour_room_type', function () {
                enforceTravelersLimit(this);
                renderTourCalculatedPrice();
            });

            enforceTravelersLimit();
        }

        var $shopPage = $('.sapa-shop-page');
        var $filterForm = $('.sapa-filter-panel');
        var $searchForm = $('.sapa-shop-search');
        var ajaxTimer = null;
        var ajaxRequest = null;

        function initCategorySliders() {
            if (typeof Swiper === 'undefined') {
                return;
            }

            document.querySelectorAll('.sapa-category-slider').forEach(function (slider) {
                if (slider.swiper) {
                    return;
                }

                new Swiper(slider, {
                    loop: true,
                    speed: 550,
                    spaceBetween: 18,
                    slidesPerView: 1,
                    watchOverflow: true,
                    navigation: {
                        nextEl: slider.querySelector('.sapa-category-nav-next'),
                        prevEl: slider.querySelector('.sapa-category-nav-prev')
                    },
                    pagination: {
                        el: slider.querySelector('.sapa-category-pagination'),
                        clickable: true
                    },
                    breakpoints: {
                        521: {
                            slidesPerView: 2,
                            spaceBetween: 18
                        },
                        850: {
                            slidesPerView: 4,
                            spaceBetween: 34
                        }
                    }
                });
            });
        }

        initCategorySliders();

        function initTourMediaGallery() {
            if (typeof Swiper === 'undefined') {
                return;
            }

            document.querySelectorAll('.tour-media-gallery').forEach(function (gallery) {
                var main = gallery.querySelector('.tour-media-main');
                var thumbsSlider = gallery.querySelector('.tour-media-thumbs');
                var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('.tour-media-thumb'));

                if (!main || main.swiper) {
                    return;
                }

                var thumbsSwiper = thumbsSlider ? new Swiper(thumbsSlider, {
                    slidesPerView: 3.25,
                    spaceBetween: 8,
                    freeMode: true,
                    watchSlidesProgress: true,
                    breakpoints: {
                        640: {
                            slidesPerView: 4,
                            spaceBetween: 12
                        },
                        1024: {
                            slidesPerView: 5,
                            spaceBetween: 12
                        }
                    }
                }) : null;

                var swiper = new Swiper(main, {
                    slidesPerView: 1,
                    spaceBetween: 0,
                    speed: 450,
                    autoHeight: false,
                    navigation: {
                        nextEl: gallery.querySelector('.tour-media-next'),
                        prevEl: gallery.querySelector('.tour-media-prev')
                    }
                });

                function setActiveThumb(index) {
                    thumbs.forEach(function (thumb, thumbIndex) {
                        thumb.classList.toggle('is-active', thumbIndex === index);
                    });

                    if (thumbsSwiper) {
                        thumbsSwiper.slideTo(Math.max(0, index - 1));
                    }
                }

                swiper.on('slideChange', function () {
                    gallery.querySelectorAll('video').forEach(function (video) {
                        video.pause();
                    });
                    setActiveThumb(swiper.activeIndex);
                });

                thumbs.forEach(function (thumb) {
                    thumb.addEventListener('click', function () {
                        swiper.slideTo(Number(thumb.getAttribute('data-media-index')) || 0);
                    });
                });
            });
        }

        initTourMediaGallery();

        function initLoopCardReveal(context) {
            var root = context || document;
            var cards = Array.prototype.slice.call(root.querySelectorAll('.sapa-loop-card-inner:not(.is-reveal-ready):not(.is-visible)'));

            if (!cards.length) {
                return;
            }

            if (!('IntersectionObserver' in window)) {
                cards.forEach(function (card) {
                    card.classList.add('is-visible');
                });
                return;
            }

            cards.forEach(function (card, index) {
                card.classList.add('is-reveal-ready');
                card.style.setProperty('--sapa-reveal-delay', Math.min(index % 8, 7) * 70 + 'ms');
            });

            var observer = new IntersectionObserver(function (entries, cardObserver) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    cardObserver.unobserve(entry.target);
                });
            }, {
                threshold: 0.16,
                rootMargin: '0px 0px -8% 0px'
            });

            cards.forEach(function (card) {
                observer.observe(card);
            });
        }

        initLoopCardReveal();
        $(document.body).on('sapa_shop_ajax_refreshed', function () {
            initLoopCardReveal(document);
        });

        function syncPriceRange($range) {
            var minBound = Number($range.data('min')) || 0;
            var maxBound = Number($range.data('max')) || 1000;
            var $minRange = $range.find('.sapa-price-range-min');
            var $maxRange = $range.find('.sapa-price-range-max');
            var $minNumber = $('.sapa-price-number-min');
            var $maxNumber = $('.sapa-price-number-max');
            var minValue = Math.max(minBound, Math.min(Number($minRange.val()) || minBound, maxBound));
            var maxValue = Math.max(minBound, Math.min(Number($maxRange.val()) || maxBound, maxBound));

            if (minValue > maxValue) {
                if ($(document.activeElement).is($minRange) || $(document.activeElement).is($minNumber)) {
                    maxValue = minValue;
                } else {
                    minValue = maxValue;
                }
            }

            $minRange.val(minValue);
            $maxRange.val(maxValue);
            $minNumber.val(minValue);
            $maxNumber.val(maxValue);

            var rangeSpan = Math.max(1, maxBound - minBound);
            var minPercent = ((minValue - minBound) / rangeSpan) * 100;
            var maxPercent = ((maxValue - minBound) / rangeSpan) * 100;
            $range.find('.sapa-price-track span').css({
                left: minPercent + '%',
                right: (100 - maxPercent) + '%'
            });
        }

        function buildShopUrl() {
            var action = $filterForm.attr('action') || window.location.pathname;
            var url = new URL(action, window.location.origin);
            var params = new URLSearchParams();

            [$searchForm, $filterForm].forEach(function ($form) {
                if (!$form.length) {
                    return;
                }

                new FormData($form[0]).forEach(function (value, key) {
                    if (value !== '') {
                        params.append(key, value);
                    }
                });
            });

            $('.sapa-shop-actions .woocommerce-ordering select').each(function () {
                if (this.value) {
                    params.set(this.name, this.value);
                }
            });

            url.search = params.toString();
            return url;
        }

        function openFilterDrawer() {
            $shopPage.addClass('is-filter-drawer-open');
            $('body').addClass('sapa-filter-lock');
            $('.sapa-mobile-filter-toggle').attr('aria-expanded', 'true');
        }

        function closeFilterDrawer() {
            $shopPage.removeClass('is-filter-drawer-open');
            $('body').removeClass('sapa-filter-lock');
            $('.sapa-mobile-filter-toggle').attr('aria-expanded', 'false');
        }

        function refreshShop(pushState) {
            if (!$shopPage.length) {
                return;
            }

            var url = buildShopUrl();
            $shopPage.addClass('is-filtering');

            if (ajaxRequest) {
                ajaxRequest.abort();
            }

            ajaxRequest = $.ajax({
                url: url.toString(),
                method: 'GET'
            }).done(function (html) {
                var $html = $('<div>').append($.parseHTML(html));
                var $newProducts = $html.find('.sapa-shop-products').first();

                if ($newProducts.length) {
                    $('.sapa-shop-products').replaceWith($newProducts);
                    if (pushState !== false) {
                        window.history.pushState({ sapaShopAjax: true }, '', url.toString());
                    }
                    $(document.body).trigger('sapa_shop_ajax_refreshed');
                    if (window.matchMedia('(max-width: 849px)').matches) {
                        closeFilterDrawer();
                    }
                } else {
                    window.location.href = url.toString();
                }
            }).always(function () {
                $shopPage.removeClass('is-filtering');
                ajaxRequest = null;
            });
        }

        function queueRefresh(delay) {
            window.clearTimeout(ajaxTimer);
            ajaxTimer = window.setTimeout(function () {
                refreshShop(true);
            }, delay || 250);
        }

        if ($shopPage.length) {
            $(document).on('click', '.sapa-mobile-filter-toggle', function () {
                openFilterDrawer();
            });

            $(document).on('click', '.sapa-filter-drawer-close, .sapa-filter-drawer-overlay', function () {
                closeFilterDrawer();
            });

            $(document).on('keyup', function (event) {
                if (event.key === 'Escape') {
                    closeFilterDrawer();
                }
            });

            $('.sapa-price-range').each(function () {
                syncPriceRange($(this));
            });

            $filterForm.on('input', '.sapa-price-range input[type="range"]', function () {
                var $range = $(this).closest('.sapa-price-range');
                syncPriceRange($range);
                queueRefresh(350);
            });

            $filterForm.on('input', '.sapa-price-inputs input[type="number"]', function () {
                var $range = $('.sapa-price-range');
                $('.sapa-price-range-min').val($('.sapa-price-number-min').val());
                $('.sapa-price-range-max').val($('.sapa-price-number-max').val());
                syncPriceRange($range);
                queueRefresh(450);
            });

            $filterForm.on('change', 'input[type="checkbox"], select', function () {
                queueRefresh(100);
            });

            $searchForm.on('change', 'select, input', function () {
                queueRefresh(100);
            });

            $filterForm.add($searchForm).on('submit', function (event) {
                event.preventDefault();
                refreshShop(true);
            });

            $(document).on('change', '.sapa-shop-actions .woocommerce-ordering select', function () {
                queueRefresh(100);
            });

            $(document).on('click', '.sapa-shop-products .woocommerce-pagination a', function (event) {
                event.preventDefault();
                var url = new URL(this.href);
                $shopPage.addClass('is-filtering');
                $.get(url.toString()).done(function (html) {
                    var $html = $('<div>').append($.parseHTML(html));
                    var $newProducts = $html.find('.sapa-shop-products').first();
                    if ($newProducts.length) {
                        $('.sapa-shop-products').replaceWith($newProducts);
                        window.history.pushState({ sapaShopAjax: true }, '', url.toString());
                    } else {
                        window.location.href = url.toString();
                    }
                }).always(function () {
                    $shopPage.removeClass('is-filtering');
                });
            });

            window.addEventListener('popstate', function () {
                $.get(window.location.href).done(function (html) {
                    var $html = $('<div>').append($.parseHTML(html));
                    var $newProducts = $html.find('.sapa-shop-products').first();
                    if ($newProducts.length) {
                        $('.sapa-shop-products').replaceWith($newProducts);
                    }
                });
            });
        }
    });
})(jQuery);
