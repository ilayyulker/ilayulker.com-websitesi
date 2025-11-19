/**
 * CART TRACKING HELPER
 * Sepet işlemleri için otomatik event tracking
 *
 * Bu dosyayı sepet sayfası ve ürün sayfalarında kullanın
 */

(function($) {
    'use strict';

    if (typeof $ === 'undefined') {
        console.error('[Cart Tracking] jQuery bulunamadı!');
        return;
    }

    // Sepete Ekleme İşlemi Wrapper
    window.trackAndAddToCart = function(productData, callback) {
        var quantity = parseInt(productData.quantity) || 1;

        // Event tracking gönder
        if (window.EcommerceTracking) {
            window.EcommerceTracking.trackAddToCart({
                id: productData.id,
                name: productData.name,
                price: productData.price,
                category: productData.category || '',
                brand: productData.brand || '',
                variant: productData.variant || ''
            }, quantity);
        }

        // Callback fonksiyonu varsa çalıştır
        if (typeof callback === 'function') {
            callback();
        }
    };

    // Sepetten Çıkarma İşlemi Wrapper
    window.trackAndRemoveFromCart = function(productData, callback) {
        var quantity = parseInt(productData.quantity) || 1;

        // Event tracking gönder
        if (window.EcommerceTracking) {
            window.EcommerceTracking.trackRemoveFromCart({
                id: productData.id,
                name: productData.name,
                price: productData.price,
                category: productData.category || ''
            }, quantity);
        }

        // Callback fonksiyonu varsa çalıştır
        if (typeof callback === 'function') {
            callback();
        }
    };

    // Sepet görüntüleme tracking (sayfa yüklendiğinde)
    $(document).ready(function() {
        // Sepet sayfasında sepet verilerini track et
        if (typeof window.cartData !== 'undefined' && window.cartData.items) {
            setTimeout(function() {
                if (window.EcommerceTracking) {
                    window.EcommerceTracking.trackViewCart(
                        window.cartData.items,
                        window.cartData.total
                    );
                }
            }, 500);
        }
    });

    // Ödeme başlangıcı tracking
    window.trackBeginCheckout = function(cartItems, totalValue, couponCode) {
        if (window.EcommerceTracking) {
            window.EcommerceTracking.trackBeginCheckout(
                cartItems,
                totalValue,
                couponCode || null
            );
        }
    };

    // Ödeme bilgisi ekleme tracking
    window.trackPaymentMethod = function(cartItems, totalValue, paymentMethod) {
        if (window.EcommerceTracking) {
            window.EcommerceTracking.trackAddPaymentInfo(
                cartItems,
                totalValue,
                paymentMethod
            );
        }
    };

    // Satın alma tracking (success sayfası için)
    window.trackPurchase = function(orderData) {
        if (window.EcommerceTracking) {
            window.EcommerceTracking.trackPurchase({
                transaction_id: orderData.order_id,
                value: orderData.total,
                tax: orderData.tax || 0,
                shipping: orderData.shipping || 0,
                coupon: orderData.coupon || '',
                items: orderData.items
            });
        }
    };

    // Arama tracking
    window.trackSearch = function(searchTerm, resultsCount) {
        if (window.EcommerceTracking) {
            window.EcommerceTracking.trackSearch(searchTerm, resultsCount);
        }
    };

    console.log('[Cart Tracking Helper] Yüklendi');

})(jQuery);
