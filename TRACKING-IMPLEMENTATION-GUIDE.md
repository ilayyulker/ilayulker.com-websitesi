# 📊 E-COMMERCE EVENT TRACKING - UYGULAMA REHBERİ

## 🎯 GEREKLİ ADIMLAR

Bu rehber, Google Analytics 4 ve Facebook Pixel event tracking sisteminin sitenize nasıl entegre edileceğini açıklar.

---

## ✅ ADIM 1: TRACKING ID'LERİNİ ALIN

### Google Analytics 4 (GA4)
1. [Google Analytics](https://analytics.google.com/) hesabınıza giriş yapın
2. **Admin** > **Data Streams** > **Web Stream** seçin
3. **Measurement ID** kopyalayın (Örnek: `G-XXXXXXXXXX`)

### Facebook Pixel
1. [Meta Business Suite](https://business.facebook.com/) hesabınıza giriş yapın
2. **Events Manager** > **Pixels** bölümüne gidin
3. **Pixel ID** kopyalayın (Örnek: `1234567890123456`)

---

## ✅ ADIM 2: TRACKING BAŞLATICI DOSYASINI YAPILANDIRIN

`tracking-init.php` dosyasını açın ve şu satırları düzenleyin:

```php
// Google Analytics 4 Measurement ID
define('GA4_MEASUREMENT_ID', 'G-XXXXXXXXXX'); // 👈 Kendi ID'nizi yazın

// Facebook Pixel ID
define('FB_PIXEL_ID', '1234567890123456'); // 👈 Kendi ID'nizi yazın

// Debug modu (canlıya alırken false yapın)
define('TRACKING_DEBUG', true); // 👈 Test ederken true, canlıda false
```

---

## ✅ ADIM 3: TEMA DOSYALARINIZA TRACKING KODLARINI EKLEYİN

### 3.1. Header Dosyasına Tracking Başlatıcıyı Ekleyin

Tema dosyanızın `<head>` bölümünde, **diğer script'lerden ÖNCE** ekleyin:

```php
<!-- TRACKING BAŞLATICI -->
<?php include('tracking-init.php'); ?>
```

**ÖNEMLI:** Bu kod mutlaka şu dosyalarda olmalı:
- Ana header dosyası (her sayfada yüklenen)
- Ürün sayfası
- Sepet sayfası
- Ödeme sayfası
- Ödeme başarılı sayfası

### 3.2. Cart Tracking Helper'ı Ekleyin

Sepet ve ürün sayfalarında, `</body>` kapanış tag'inden ÖNCE:

```html
<!-- CART TRACKING HELPER -->
<script src="<?php echo base_url('cart-tracking-helper.js'); ?>"></script>
```

---

## ✅ ADIM 4: SAYFA BAZINDA TRACKING EKLEYIN

### 📦 ÜRÜN DETAY SAYFASI

Ürün sayfanızda (örn: `tema/urun.php` veya benzeri), ürün bilgilerinin göründüğü yerde:

```html
<!-- Ürün bilgileri için data attribute'ları ekleyin -->
<div data-track-view-item
     data-product-id="<?php echo $urun->urun_id; ?>"
     data-product-name="<?php echo htmlspecialchars($urun->ad); ?>"
     data-product-price="<?php echo $urun->fiyat; ?>"
     data-product-category="<?php echo $urun_kategori->ad; ?>"
     data-product-brand="<?php echo $urun->marka ?? ''; ?>">

    <!-- Ürün içeriği -->
    <h1><?php echo $urun->ad; ?></h1>
    <p>Fiyat: <?php echo $urun->fiyat; ?> TL</p>

    <!-- Sepete ekle butonu -->
    <button data-track-add-to-cart
            data-product-id="<?php echo $urun->urun_id; ?>"
            data-product-name="<?php echo htmlspecialchars($urun->ad); ?>"
            data-product-price="<?php echo $urun->fiyat; ?>"
            data-product-category="<?php echo $urun_kategori->ad; ?>"
            data-product-quantity="1"
            onclick="sepeteEkle(this)">
        Sepete Ekle
    </button>
</div>

<script>
// Sepete ekleme fonksiyonunuzu güncelleyin
function sepeteEkle(button) {
    var productData = {
        id: $(button).data('product-id'),
        name: $(button).data('product-name'),
        price: $(button).data('product-price'),
        category: $(button).data('product-category'),
        quantity: $(button).data('product-quantity') || 1
    };

    // ÖNCE tracking event'i gönder
    trackAndAddToCart(productData, function() {
        // SONRA normal AJAX sepete ekleme işlemini yap
        $.ajax({
            url: '<?php echo base_url("sepet/sepete_ekle"); ?>',
            type: 'POST',
            data: {
                urun_id: productData.id,
                adet: productData.quantity
            },
            success: function(response) {
                // Başarılı mesajı göster
                alert('Ürün sepete eklendi!');
            }
        });
    });
}
</script>
```

---

### 🛒 SEPET SAYFASI

Sepet sayfanızda (örn: `tema/sepet.php`), sepet verilerini JavaScript'e aktarın:

```php
<!-- Sepet verileri -->
<script>
// Sepet verilerini JavaScript'e aktar
window.cartData = {
    items: [
        <?php foreach($sepet->urunler as $urun): ?>
        {
            id: '<?php echo $urun->urun_id; ?>',
            name: '<?php echo addslashes($urun->ad); ?>',
            price: <?php echo $urun->fiyat; ?>,
            quantity: <?php echo $urun->adet; ?>,
            category: '<?php echo $urun->kategori ?? ''; ?>'
        },
        <?php endforeach; ?>
    ],
    total: <?php echo $sepet->toplam_tutar; ?>
};
</script>
```

**Sepetten Çıkarma İşlemi:**

```javascript
function sepettenCikar(urunId, urunAd, urunFiyat, adet) {
    // ÖNCE tracking event'i gönder
    trackAndRemoveFromCart({
        id: urunId,
        name: urunAd,
        price: urunFiyat,
        quantity: adet
    }, function() {
        // SONRA AJAX ile sepetten çıkar
        $.ajax({
            url: '<?php echo base_url("sepet/sepet_urun_sil"); ?>',
            type: 'POST',
            data: { id: urunId },
            success: function(response) {
                location.reload();
            }
        });
    });
}
```

---

### 💳 ÖDEME SAYFASI

Ödeme sayfanızda (örn: `tema/odeme.php`), ödeme formunun başında:

```php
<script>
// Sayfa yüklendiğinde "begin_checkout" event'i gönder
document.addEventListener('DOMContentLoaded', function() {
    var cartItems = [
        <?php foreach($sepet->urunler as $urun): ?>
        {
            id: '<?php echo $urun->urun_id; ?>',
            name: '<?php echo addslashes($urun->ad); ?>',
            price: <?php echo $urun->fiyat; ?>,
            quantity: <?php echo $urun->adet; ?>,
            category: '<?php echo $urun->kategori ?? ''; ?>'
        },
        <?php endforeach; ?>
    ];

    var totalValue = <?php echo $sepet->toplam_tutar; ?>;
    var couponCode = '<?php echo isset($_SESSION["kupon_kodu"]) ? $_SESSION["kupon_kodu"] : ""; ?>';

    // Begin Checkout event gönder
    trackBeginCheckout(cartItems, totalValue, couponCode);
});

// Ödeme yöntemi seçildiğinde
function odemeYontemiSec(method) {
    var cartItems = [...]; // Yukarıdaki ile aynı
    var totalValue = <?php echo $sepet->toplam_tutar; ?>;

    // Add Payment Info event gönder
    trackPaymentMethod(cartItems, totalValue, method);
}
</script>

<!-- Ödeme form submit edildiğinde -->
<form onsubmit="odemeYontemiSec('<?php echo post('method'); ?>')">
    <!-- Form içeriği -->
</form>
```

---

### ✅ ÖDEME BAŞARILI SAYFASI (ÖNEMLİ!)

Ödeme başarılı sayfanızda (örn: `tema/odeme-sonuc.php` veya `tema/index.php`), **body'nin sonunda**:

```php
<!-- PURCHASE EVENT TRACKING -->
<?php if (isset($tracking_data) && $tip == 'success'): ?>
    <?php include('payment-success-tracking.php'); ?>
<?php endif; ?>
```

**NOT:** `$tracking_data` değişkeni `Odeme.php -> sonuc()` fonksiyonundan otomatik gelir (zaten güncelledik).

---

## ✅ ADIM 5: KATEGORİ/LİSTE SAYFALARINDA TRACKING

Kategori veya ürün listesi sayfalarında:

```php
<script>
document.addEventListener('DOMContentLoaded', function() {
    var items = [
        <?php foreach($urunler as $index => $urun): ?>
        {
            id: '<?php echo $urun->urun_id; ?>',
            name: '<?php echo addslashes($urun->ad); ?>',
            price: <?php echo $urun->fiyat; ?>,
            category: '<?php echo $kategori->ad; ?>'
        },
        <?php endforeach; ?>
    ];

    if (window.EcommerceTracking) {
        EcommerceTracking.trackViewItemList(items, '<?php echo $kategori->ad; ?>');
    }
});
</script>
```

---

## ✅ ADIM 6: ARAMA TRACKING

Arama sayfanızda veya arama sonuçlarında:

```php
<script>
<?php if (isset($arama_terimi) && isset($sonuc_sayisi)): ?>
    trackSearch('<?php echo addslashes($arama_terimi); ?>', <?php echo $sonuc_sayisi; ?>);
<?php endif; ?>
</script>
```

---

## 🧪 TEST ETME

### 1. Debug Modunu Aktif Edin

`tracking-init.php` dosyasında:

```php
define('TRACKING_DEBUG', true);
```

### 2. Tarayıcı Console'u Açın

- **Chrome/Firefox:** `F12` veya `Ctrl+Shift+I`
- **Console** sekmesine gidin

### 3. Event'leri İzleyin

Her event gönderildiğinde console'da göreceksiniz:

```
[E-commerce Tracking] GA4 Event: view_item
[E-commerce Tracking] FB Pixel Event: ViewContent
[DataLayer Push] {event: "view_item", ...}
```

### 4. Google Analytics Real-Time Raporlarını Kontrol Edin

- [Google Analytics](https://analytics.google.com/) > **Reports** > **Realtime**
- Event'lerin gerçek zamanlı geldiğini görün

### 5. Facebook Pixel Test Tool

- [Facebook Pixel Helper](https://chrome.google.com/webstore/detail/facebook-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc) Chrome eklentisini kurun
- Sayfalarınızda event'lerin gönderildiğini doğrulayın

---

## 📋 EVENT KONTROL LİSTESİ

Tüm event'lerin çalıştığından emin olmak için:

- [ ] `page_view` - Tüm sayfalarda
- [ ] `view_item` - Ürün detay sayfasında
- [ ] `add_to_cart` - Sepete ekleme butonuna tıklandığında
- [ ] `remove_from_cart` - Sepetten çıkarma işleminde
- [ ] `view_cart` - Sepet sayfası açıldığında
- [ ] `begin_checkout` - Ödeme sayfası açıldığında
- [ ] `add_payment_info` - Ödeme yöntemi seçildiğinde
- [ ] `purchase` - Ödeme başarılı sayfasında (**EN ÖNEMLİ!**)
- [ ] `search` - Arama yapıldığında
- [ ] `view_item_list` - Kategori sayfasında

---

## 🚀 CANLI ORTAMA ALMA

Testler tamamlandığında:

1. `tracking-init.php` dosyasında debug modunu kapatın:

```php
define('TRACKING_DEBUG', false);
```

2. Tüm dosyaların canlı sunucuya yüklendiğinden emin olun

3. Canlı ortamda da test edin

---

## 🆘 SORUN GİDERME

### Event'ler gönderilmiyor?

1. **Console'da hata var mı kontrol edin**
   - `tracking-init.php` doğru yüklendi mi?
   - GA4 ve FB Pixel ID'leri doğru mu?

2. **Script sıralaması doğru mu?**
   - `tracking-init.php` EN BAŞTA olmalı
   - `ecommerce-tracking.js` sonra yüklenmeli
   - Sayfa kodları en son çalışmalı

3. **jQuery yüklü mü?**
   - `cart-tracking-helper.js` jQuery'ye ihtiyaç duyuyor

### Purchase event çalışmıyor?

1. `Odeme.php -> sonuc()` fonksiyonu güncellenmiş mi?
2. `payment-success-tracking.php` ödeme başarılı sayfasına dahil edilmiş mi?
3. `$tracking_data` değişkeni view'a aktarılıyor mu?

---

## 📞 DESTEK

Sorularınız için:
- E-posta: [destek@ilayulker.com]
- GitHub Issues: [Repository link]

---

## 📝 NOTLAR

- **Tüm fiyatlar TRY (Türk Lirası) olarak gönderilir**
- **User ID tracking**: Üye girişi yapıldığında otomatik aktive olur
- **Kupon tracking**: Kupon kullanıldığında otomatik eklenir
- **Conversion değeri**: PayTR komisyonu DAHİL toplam tutar

---

**Son Güncelleme:** <?php echo date('Y-m-d H:i:s'); ?>

**Versiyon:** 1.0.0
