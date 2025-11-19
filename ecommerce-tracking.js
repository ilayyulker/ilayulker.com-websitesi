/**
 * E-COMMERCE EVENT TRACKING LIBRARY
 * Google Analytics 4 + Facebook Pixel + Enhanced E-commerce
 *
 * @version 1.0.0
 * @author ilayulker.com
 * @description Kapsamlı e-ticaret event tracking sistemi
 */

(function(window, document) {
    'use strict';

    // Debug modu (production'da false yapın)
    var DEBUG_MODE = true;

    // Event Tracking Manager
    var EcommerceTracking = {

        /**
         * Console log wrapper (debug mode için)
         */
        log: function(message, data) {
            if (DEBUG_MODE && window.console) {
                console.log('[E-commerce Tracking] ' + message, data || '');
            }
        },

        /**
         * GA4 Event gönder
         */
        sendGA4Event: function(eventName, eventParams) {
            if (typeof gtag === 'function') {
                gtag('event', eventName, eventParams);
                this.log('GA4 Event: ' + eventName, eventParams);
            } else {
                this.log('GA4 not loaded - Event skipped: ' + eventName, eventParams);
            }
        },

        /**
         * Facebook Pixel Event gönder
         */
        sendFBEvent: function(eventName, eventParams) {
            if (typeof fbq === 'function') {
                fbq('track', eventName, eventParams);
                this.log('FB Pixel Event: ' + eventName, eventParams);
            } else {
                this.log('FB Pixel not loaded - Event skipped: ' + eventName, eventParams);
            }
        },

        /**
         * Data Layer Push (Enhanced E-commerce için)
         */
        pushDataLayer: function(data) {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(data);
            this.log('DataLayer Push', data);
        },

        /**
         * PAGE VIEW EVENT
         * Sayfa görüntüleme tracking
         */
        trackPageView: function(pagePath, pageTitle) {
            // GA4
            this.sendGA4Event('page_view', {
                page_path: pagePath || window.location.pathname,
                page_title: pageTitle || document.title,
                page_location: window.location.href
            });

            // Facebook Pixel
            this.sendFBEvent('PageView', {});

            // Data Layer
            this.pushDataLayer({
                event: 'page_view',
                page_path: pagePath || window.location.pathname,
                page_title: pageTitle || document.title
            });
        },

        /**
         * VIEW ITEM EVENT
         * Ürün detay sayfası görüntüleme
         *
         * @param {Object} product - Ürün bilgileri
         * @param {string} product.id - Ürün ID
         * @param {string} product.name - Ürün adı
         * @param {number} product.price - Ürün fiyatı
         * @param {string} product.category - Kategori
         * @param {string} product.brand - Marka (opsiyonel)
         * @param {string} product.variant - Varyant (opsiyonel)
         */
        trackViewItem: function(product) {
            var productData = {
                currency: 'TRY',
                value: parseFloat(product.price),
                items: [{
                    item_id: product.id,
                    item_name: product.name,
                    item_category: product.category || '',
                    item_brand: product.brand || '',
                    item_variant: product.variant || '',
                    price: parseFloat(product.price),
                    quantity: 1
                }]
            };

            // GA4
            this.sendGA4Event('view_item', productData);

            // Facebook Pixel
            this.sendFBEvent('ViewContent', {
                content_ids: [product.id],
                content_name: product.name,
                content_type: 'product',
                value: parseFloat(product.price),
                currency: 'TRY'
            });

            // Data Layer
            this.pushDataLayer({
                event: 'view_item',
                ecommerce: productData
            });
        },

        /**
         * ADD TO CART EVENT
         * Sepete ekleme
         *
         * @param {Object} product - Ürün bilgileri
         * @param {number} quantity - Adet
         */
        trackAddToCart: function(product, quantity) {
            quantity = parseInt(quantity) || 1;
            var totalValue = parseFloat(product.price) * quantity;

            var productData = {
                currency: 'TRY',
                value: totalValue,
                items: [{
                    item_id: product.id,
                    item_name: product.name,
                    item_category: product.category || '',
                    item_brand: product.brand || '',
                    item_variant: product.variant || '',
                    price: parseFloat(product.price),
                    quantity: quantity
                }]
            };

            // GA4
            this.sendGA4Event('add_to_cart', productData);

            // Facebook Pixel
            this.sendFBEvent('AddToCart', {
                content_ids: [product.id],
                content_name: product.name,
                content_type: 'product',
                value: totalValue,
                currency: 'TRY',
                num_items: quantity
            });

            // Data Layer
            this.pushDataLayer({
                event: 'add_to_cart',
                ecommerce: productData
            });
        },

        /**
         * REMOVE FROM CART EVENT
         * Sepetten çıkarma
         */
        trackRemoveFromCart: function(product, quantity) {
            quantity = parseInt(quantity) || 1;
            var totalValue = parseFloat(product.price) * quantity;

            var productData = {
                currency: 'TRY',
                value: totalValue,
                items: [{
                    item_id: product.id,
                    item_name: product.name,
                    item_category: product.category || '',
                    price: parseFloat(product.price),
                    quantity: quantity
                }]
            };

            // GA4
            this.sendGA4Event('remove_from_cart', productData);

            // Data Layer
            this.pushDataLayer({
                event: 'remove_from_cart',
                ecommerce: productData
            });
        },

        /**
         * VIEW CART EVENT
         * Sepet görüntüleme
         *
         * @param {Array} items - Sepetteki ürünler
         * @param {number} totalValue - Toplam tutar
         */
        trackViewCart: function(items, totalValue) {
            var formattedItems = items.map(function(item) {
                return {
                    item_id: item.id,
                    item_name: item.name,
                    item_category: item.category || '',
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity)
                };
            });

            var cartData = {
                currency: 'TRY',
                value: parseFloat(totalValue),
                items: formattedItems
            };

            // GA4
            this.sendGA4Event('view_cart', cartData);

            // Data Layer
            this.pushDataLayer({
                event: 'view_cart',
                ecommerce: cartData
            });
        },

        /**
         * BEGIN CHECKOUT EVENT
         * Ödeme başlangıcı
         */
        trackBeginCheckout: function(items, totalValue, couponCode) {
            var formattedItems = items.map(function(item) {
                return {
                    item_id: item.id,
                    item_name: item.name,
                    item_category: item.category || '',
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity)
                };
            });

            var checkoutData = {
                currency: 'TRY',
                value: parseFloat(totalValue),
                items: formattedItems
            };

            if (couponCode) {
                checkoutData.coupon = couponCode;
            }

            // GA4
            this.sendGA4Event('begin_checkout', checkoutData);

            // Facebook Pixel
            this.sendFBEvent('InitiateCheckout', {
                content_ids: items.map(function(item) { return item.id; }),
                contents: items.map(function(item) {
                    return {
                        id: item.id,
                        quantity: parseInt(item.quantity)
                    };
                }),
                value: parseFloat(totalValue),
                currency: 'TRY',
                num_items: items.length
            });

            // Data Layer
            this.pushDataLayer({
                event: 'begin_checkout',
                ecommerce: checkoutData
            });
        },

        /**
         * ADD PAYMENT INFO EVENT
         * Ödeme bilgisi girişi
         */
        trackAddPaymentInfo: function(items, totalValue, paymentMethod) {
            var formattedItems = items.map(function(item) {
                return {
                    item_id: item.id,
                    item_name: item.name,
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity)
                };
            });

            var paymentData = {
                currency: 'TRY',
                value: parseFloat(totalValue),
                payment_type: paymentMethod,
                items: formattedItems
            };

            // GA4
            this.sendGA4Event('add_payment_info', paymentData);

            // Facebook Pixel
            this.sendFBEvent('AddPaymentInfo', {
                content_ids: items.map(function(item) { return item.id; }),
                value: parseFloat(totalValue),
                currency: 'TRY'
            });

            // Data Layer
            this.pushDataLayer({
                event: 'add_payment_info',
                ecommerce: paymentData
            });
        },

        /**
         * PURCHASE EVENT
         * Satın alma (en önemli event!)
         *
         * @param {Object} orderData - Sipariş bilgileri
         * @param {string} orderData.transaction_id - Sipariş numarası
         * @param {number} orderData.value - Toplam tutar
         * @param {number} orderData.tax - Vergi (opsiyonel)
         * @param {number} orderData.shipping - Kargo (opsiyonel)
         * @param {string} orderData.coupon - Kupon kodu (opsiyonel)
         * @param {Array} orderData.items - Ürünler
         */
        trackPurchase: function(orderData) {
            var formattedItems = orderData.items.map(function(item) {
                return {
                    item_id: item.id,
                    item_name: item.name,
                    item_category: item.category || '',
                    price: parseFloat(item.price),
                    quantity: parseInt(item.quantity)
                };
            });

            var purchaseData = {
                transaction_id: orderData.transaction_id,
                value: parseFloat(orderData.value),
                currency: 'TRY',
                tax: parseFloat(orderData.tax || 0),
                shipping: parseFloat(orderData.shipping || 0),
                items: formattedItems
            };

            if (orderData.coupon) {
                purchaseData.coupon = orderData.coupon;
            }

            // GA4
            this.sendGA4Event('purchase', purchaseData);

            // Facebook Pixel
            this.sendFBEvent('Purchase', {
                content_ids: orderData.items.map(function(item) { return item.id; }),
                contents: orderData.items.map(function(item) {
                    return {
                        id: item.id,
                        quantity: parseInt(item.quantity)
                    };
                }),
                value: parseFloat(orderData.value),
                currency: 'TRY',
                num_items: orderData.items.length
            });

            // Data Layer
            this.pushDataLayer({
                event: 'purchase',
                ecommerce: purchaseData
            });
        },

        /**
         * SEARCH EVENT
         * Site içi arama
         */
        trackSearch: function(searchTerm, searchResults) {
            // GA4
            this.sendGA4Event('search', {
                search_term: searchTerm,
                search_results: parseInt(searchResults || 0)
            });

            // Facebook Pixel
            this.sendFBEvent('Search', {
                search_string: searchTerm
            });

            // Data Layer
            this.pushDataLayer({
                event: 'search',
                search_term: searchTerm,
                search_results: parseInt(searchResults || 0)
            });
        },

        /**
         * VIEW ITEM LIST EVENT
         * Kategori/liste görüntüleme
         */
        trackViewItemList: function(items, listName) {
            var formattedItems = items.map(function(item, index) {
                return {
                    item_id: item.id,
                    item_name: item.name,
                    item_category: item.category || '',
                    price: parseFloat(item.price),
                    index: index,
                    item_list_name: listName
                };
            });

            var listData = {
                item_list_name: listName,
                items: formattedItems
            };

            // GA4
            this.sendGA4Event('view_item_list', listData);

            // Data Layer
            this.pushDataLayer({
                event: 'view_item_list',
                ecommerce: listData
            });
        },

        /**
         * SELECT ITEM EVENT
         * Liste'den ürün seçimi
         */
        trackSelectItem: function(product, listName, index) {
            var selectData = {
                item_list_name: listName,
                items: [{
                    item_id: product.id,
                    item_name: product.name,
                    item_category: product.category || '',
                    price: parseFloat(product.price),
                    index: parseInt(index || 0)
                }]
            };

            // GA4
            this.sendGA4Event('select_item', selectData);

            // Data Layer
            this.pushDataLayer({
                event: 'select_item',
                ecommerce: selectData
            });
        }
    };

    // Global scope'a ekle
    window.EcommerceTracking = EcommerceTracking;

    // jQuery varsa helper fonksiyonlar ekle
    if (typeof jQuery !== 'undefined') {
        (function($) {
            // Otomatik sepete ekleme tracking'i
            $(document).on('click', '[data-track-add-to-cart]', function() {
                var $this = $(this);
                var productData = {
                    id: $this.data('product-id'),
                    name: $this.data('product-name'),
                    price: $this.data('product-price'),
                    category: $this.data('product-category'),
                    brand: $this.data('product-brand'),
                    variant: $this.data('product-variant')
                };
                var quantity = $this.data('product-quantity') || 1;

                EcommerceTracking.trackAddToCart(productData, quantity);
            });

            // Otomatik ürün görüntüleme tracking'i
            $(document).ready(function() {
                var $viewItem = $('[data-track-view-item]');
                if ($viewItem.length) {
                    var productData = {
                        id: $viewItem.data('product-id'),
                        name: $viewItem.data('product-name'),
                        price: $viewItem.data('product-price'),
                        category: $viewItem.data('product-category'),
                        brand: $viewItem.data('product-brand'),
                        variant: $viewItem.data('product-variant')
                    };
                    EcommerceTracking.trackViewItem(productData);
                }
            });
        })(jQuery);
    }

    // Sayfa yüklendiğinde otomatik page view tracking
    if (document.readyState === 'complete') {
        EcommerceTracking.trackPageView();
    } else {
        window.addEventListener('load', function() {
            EcommerceTracking.trackPageView();
        });
    }

})(window, document);
