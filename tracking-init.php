<?php
/**
 * TRACKING INITIALIZATION
 * Google Analytics 4 ve Facebook Pixel başlatma dosyası
 *
 * Bu dosyayı header'a dahil edin: <?php include('tracking-init.php'); ?>
 *
 * ÖNEMLI: GA4_MEASUREMENT_ID ve FB_PIXEL_ID değerlerini kendi ID'lerinizle değiştirin!
 */

// ============================================
// AYARLAR - BURADAN DEĞİŞTİRİN
// ============================================

// Google Analytics 4 Measurement ID
// Örnek: G-XXXXXXXXXX (Google Analytics Admin > Data Streams > Measurement ID)
define('GA4_MEASUREMENT_ID', 'G-XXXXXXXXXX');

// Facebook Pixel ID
// Örnek: 1234567890123456 (Meta Events Manager > Pixels)
define('FB_PIXEL_ID', '1234567890123456');

// Debug modu (canlıya alırken false yapın)
define('TRACKING_DEBUG', true);

// User ID tracking (üye girişi varsa)
$user_id = isset($_SESSION['uye_id']) ? $_SESSION['uye_id'] : null;

?>

<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA4_MEASUREMENT_ID; ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?php echo GA4_MEASUREMENT_ID; ?>', {
        'send_page_view': false, // Manuel page view tracking yapacağız
        <?php if ($user_id): ?>
        'user_id': '<?php echo $user_id; ?>',
        <?php endif; ?>
        'cookie_flags': 'SameSite=None;Secure'
    });
</script>

<!-- Facebook Pixel -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?php echo FB_PIXEL_ID; ?>');
    <?php if ($user_id): ?>
    fbq('init', '<?php echo FB_PIXEL_ID; ?>', {
        external_id: '<?php echo $user_id; ?>'
    });
    <?php endif; ?>
</script>
<noscript>
    <img height="1" width="1" style="display:none"
         src="https://www.facebook.com/tr?id=<?php echo FB_PIXEL_ID; ?>&ev=PageView&noscript=1"/>
</noscript>

<!-- E-commerce Tracking Library -->
<script src="<?php echo base_url('ecommerce-tracking.js'); ?>"></script>

<?php if (TRACKING_DEBUG): ?>
<!-- Debug Mode Aktif -->
<script>
    console.log('%c[Tracking Debug] GA4 ID: <?php echo GA4_MEASUREMENT_ID; ?>', 'background: #4285f4; color: white; padding: 5px;');
    console.log('%c[Tracking Debug] FB Pixel ID: <?php echo FB_PIXEL_ID; ?>', 'background: #4267B2; color: white; padding: 5px;');
    <?php if ($user_id): ?>
    console.log('%c[Tracking Debug] User ID: <?php echo $user_id; ?>', 'background: #34a853; color: white; padding: 5px;');
    <?php endif; ?>
</script>
<?php endif; ?>
