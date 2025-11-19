<?php
/**
 * PAYMENT SUCCESS TRACKING SNIPPET
 * Ödeme başarılı sayfasına eklenecek purchase event tracking kodu
 *
 * KULLANIM:
 * Ödeme başarılı view sayfanızda (tema dosyasında), body'nin sonunda şu kodu ekleyin:
 * <?php if (isset($tracking_data)): ?>
 *     <?php include('payment-success-tracking.php'); ?>
 * <?php endif; ?>
 *
 * NOT: $tracking_data değişkeni Odeme.php -> sonuc() fonksiyonundan gelir
 */

if (!isset($tracking_data)) {
    return;
}
?>

<!-- PURCHASE EVENT TRACKING -->
<script>
    // Sayfa yüklendiğinde purchase event'i gönder
    document.addEventListener('DOMContentLoaded', function() {
        // Tracking verilerini PHP'den JavaScript'e aktar
        var purchaseData = <?php echo json_encode($tracking_data); ?>;

        console.log('%c[Purchase Tracking] Sipariş tamamlandı!', 'background: #34a853; color: white; padding: 10px; font-size: 14px;');
        console.log('Sipariş No:', purchaseData.order_id);
        console.log('Toplam Tutar:', purchaseData.total, 'TL');
        console.log('Ürün Sayısı:', purchaseData.items.length);

        // EcommerceTracking kütüphanesinin yüklenmesini bekle
        var maxAttempts = 50; // 5 saniye bekle (50 * 100ms)
        var attempts = 0;

        var checkAndTrack = setInterval(function() {
            attempts++;

            if (window.EcommerceTracking && typeof window.EcommerceTracking.trackPurchase === 'function') {
                clearInterval(checkAndTrack);

                // PURCHASE EVENT GÖNDER
                window.EcommerceTracking.trackPurchase(purchaseData);

                console.log('%c[Purchase Tracking] Event gönderildi!', 'background: #34a853; color: white; padding: 5px;');

                // LocalStorage'a kaydet (중복 gönderim önleme için)
                var trackedOrders = JSON.parse(localStorage.getItem('tracked_orders') || '[]');
                if (trackedOrders.indexOf(purchaseData.order_id) === -1) {
                    trackedOrders.push(purchaseData.order_id);
                    localStorage.setItem('tracked_orders', JSON.stringify(trackedOrders));
                }

            } else if (attempts >= maxAttempts) {
                clearInterval(checkAndTrack);
                console.error('[Purchase Tracking] EcommerceTracking kütüphanesi yüklenemedi!');
                console.error('Lütfen tracking-init.php dosyasının sayfaya dahil edildiğinden emin olun.');
            }
        }, 100);
    });
</script>

<?php
// Debug modu aktifse ekstra bilgiler göster
if (defined('TRACKING_DEBUG') && TRACKING_DEBUG):
?>
<!-- TRACKING DEBUG INFO -->
<div style="position: fixed; bottom: 10px; right: 10px; background: #f1f1f1; padding: 15px; border-radius: 5px; font-size: 12px; max-width: 300px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 9999;">
    <strong>🎯 Tracking Debug</strong>
    <hr style="margin: 10px 0;">
    <div><strong>Sipariş:</strong> <?php echo $tracking_data['order_id']; ?></div>
    <div><strong>Tutar:</strong> <?php echo number_format($tracking_data['total'], 2); ?> TL</div>
    <div><strong>Ürün:</strong> <?php echo count($tracking_data['items']); ?> adet</div>
    <?php if ($tracking_data['coupon']): ?>
    <div><strong>Kupon:</strong> <?php echo $tracking_data['coupon']; ?></div>
    <?php endif; ?>
    <hr style="margin: 10px 0;">
    <small style="color: #666;">
        ✅ GA4 Purchase Event<br>
        ✅ FB Pixel Purchase<br>
        ✅ Enhanced E-commerce
    </small>
</div>
<?php endif; ?>
