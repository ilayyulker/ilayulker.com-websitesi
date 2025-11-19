# 🎯 E-COMMERCE EVENT TRACKING SİSTEMİ

## ✨ Kurulum Tamamlandı!

Bu sistem, Google Analytics 4 ve Facebook Pixel ile kapsamlı e-ticaret event tracking sağlar.

---

## 📦 EKLENEN DOSYALAR

### 📚 Kütüphane Dosyaları
- **`ecommerce-tracking.js`** - Ana tracking kütüphanesi (GA4 + FB Pixel + Data Layer)
- **`cart-tracking-helper.js`** - Sepet işlemleri için yardımcı fonksiyonlar
- **`tracking-init.php`** - GA4 ve FB Pixel başlatıcı (header'a ekleyin)
- **`payment-success-tracking.php`** - Purchase event snippet (ödeme başarılı sayfası için)

### 📖 Dokümantasyon
- **`TRACKING-IMPLEMENTATION-GUIDE.md`** - **📖 DETAYLI UYGULAMA REHBERİ (BURADAN BAŞLAYIN!)**
- **`EVENT-TRACKING-README.md`** - Bu dosya (genel bakış)

### 🧪 Test Araçları
- **`event-tracking-test.html`** - Event'leri test etmek için interaktif test sayfası

### ✏️ Güncellenmiş Dosyalar
- **`Odeme.php`** - Purchase event için tracking verisi hazırlama eklendi (satır 245-275)

---

## 🚀 HIZLI BAŞLANGIÇ (5 DAKİKA)

### 1️⃣ GA4 ve Facebook Pixel ID'lerinizi alın

- **GA4:** [Google Analytics](https://analytics.google.com/) > Admin > Data Streams > **Measurement ID**
- **FB Pixel:** [Meta Business](https://business.facebook.com/) > Events Manager > **Pixel ID**

### 2️⃣ Tracking Init dosyasını yapılandırın

`tracking-init.php` dosyasını açın ve ID'lerinizi girin:

```php
define('GA4_MEASUREMENT_ID', 'G-XXXXXXXXXX'); // 👈 Buraya GA4 ID'nizi
define('FB_PIXEL_ID', '1234567890123456');    // 👈 Buraya FB Pixel ID'nizi
define('TRACKING_DEBUG', true);                // Test için true, canlıda false
```

### 3️⃣ Header dosyanıza ekleyin

Tema header dosyanızın `<head>` bölümüne, **diğer script'lerden ÖNCE**:

```php
<?php include('tracking-init.php'); ?>
```

### 4️⃣ Cart helper'ı sepet ve ürün sayfalarına ekleyin

`</body>` kapanış tag'inden önce:

```html
<script src="<?php echo base_url('cart-tracking-helper.js'); ?>"></script>
```

### 5️⃣ Ödeme başarılı sayfasına purchase tracking ekleyin

Ödeme başarılı sayfanızda (tema dosyasında), `</body>` önce:

```php
<?php if (isset($tracking_data) && $tip == 'success'): ?>
    <?php include('payment-success-tracking.php'); ?>
<?php endif; ?>
```

### 6️⃣ Test edin!

1. **Test sayfasını açın:** `event-tracking-test.html`
2. **Tarayıcı console'unu açın:** F12
3. **Event'leri test edin** ve console'da göründüğünü kontrol edin
4. **Google Analytics Real-Time** raporlarında event'leri izleyin

---

## 📋 TRACK EDİLEN EVENT'LER

✅ **Kurulumla otomatik çalışanlar:**
- `page_view` - Tüm sayfalarda otomatik
- `purchase` - Ödeme başarılı sayfasında otomatik (Odeme.php güncellendi)

⚙️ **Manuel entegrasyon gerektirenler:**
- `view_item` - Ürün detay sayfası (data attribute ile)
- `add_to_cart` - Sepete ekle butonu (trackAndAddToCart fonksiyonu ile)
- `remove_from_cart` - Sepetten çıkar (trackAndRemoveFromCart ile)
- `begin_checkout` - Ödeme sayfası (trackBeginCheckout ile)
- `add_payment_info` - Ödeme yöntemi seçimi (trackPaymentMethod ile)
- `search` - Arama (trackSearch ile)
- `view_item_list` - Kategori sayfası (trackViewItemList ile)

**Detaylı kullanım örnekleri için:** `TRACKING-IMPLEMENTATION-GUIDE.md` dosyasına bakın!

---

## 🎓 ÖRNEK KULLANIM

### Ürün Sayfasında

```html
<div data-track-view-item
     data-product-id="<?php echo $urun->urun_id; ?>"
     data-product-name="<?php echo $urun->ad; ?>"
     data-product-price="<?php echo $urun->fiyat; ?>"
     data-product-category="<?php echo $kategori->ad; ?>">

    <button data-track-add-to-cart
            data-product-id="<?php echo $urun->urun_id; ?>"
            data-product-name="<?php echo $urun->ad; ?>"
            data-product-price="<?php echo $urun->fiyat; ?>"
            onclick="sepeteEkle(this)">
        Sepete Ekle
    </button>
</div>
```

### Sepete Ekleme Fonksiyonu

```javascript
function sepeteEkle(button) {
    var productData = {
        id: $(button).data('product-id'),
        name: $(button).data('product-name'),
        price: $(button).data('product-price'),
        category: 'Elektronik',
        quantity: 1
    };

    // ÖNCE tracking gönder
    trackAndAddToCart(productData, function() {
        // SONRA AJAX ile sepete ekle
        $.ajax({
            url: 'sepet/sepete_ekle',
            type: 'POST',
            data: productData,
            success: function() {
                alert('Sepete eklendi!');
            }
        });
    });
}
```

---

## 🧪 TEST KONTROLÜ

### Console'da görmek istediğiniz:

```
[E-commerce Tracking] GA4 Event: page_view
[E-commerce Tracking] FB Pixel Event: PageView
[DataLayer Push] {event: "page_view", ...}
```

### Google Analytics'te kontrol:

1. [Google Analytics](https://analytics.google.com/) > **Reports** > **Realtime**
2. Event'lerin gerçek zamanlı geldiğini görün
3. **E-commerce purchases** bölümünde satışları izleyin

### Facebook Pixel'de kontrol:

1. [Facebook Pixel Helper](https://chrome.google.com/webstore/detail/facebook-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc) Chrome eklentisini kurun
2. Sayfalarınızı ziyaret edin
3. Eklentide event'lerin gönderildiğini görün

---

## ⚠️ ÖNEMLİ NOTLAR

### Purchase Event (EN ÖNEMLİ!)

- ✅ **Otomatik çalışır** - `Odeme.php` zaten güncellendi
- ✅ **PayTR success callback** sonrasında tetiklenir
- ⚠️ **Mutlaka test edin** - Canlıya almadan önce test siparişi verin
- 💰 **Conversion tracking** için kritik - Reklam kampanyalarının ROI'sini ölçer

### Debug Modu

- ✅ **Test sırasında:** `TRACKING_DEBUG = true`
- ⚠️ **Canlıya alırken:** `TRACKING_DEBUG = false`

### Veri Güvenliği

- 🔒 Hassas müşteri bilgileri tracking'e gönderilmez
- 🔒 Sadece ürün ID, ad, fiyat, kategori gibi genel bilgiler
- 🔒 User ID (varsa) hash'lenmiş şekilde gönderilebilir

---

## 📊 BEKLENEN SONUÇLAR

### İlk Hafta

- Event'lerin düzgün gönderildiğini görün
- GA4 Real-time raporlarında aktivite izleyin
- Facebook Pixel event'lerini test edin

### İlk Ay

- Conversion funnel analizi yapın
- Hangi ürünlerin daha çok ilgi gördüğünü görün
- Abandoned cart (terk edilen sepet) oranını ölçün

### Uzun Vadede

- ROI (yatırım getirisi) optimizasyonu
- Remarketing kampanyaları için audience oluşturma
- A/B test sonuçlarını ölçme
- Attribution modeling

---

## 🆘 SORUN GİDERME

### "Event gönderilmiyor"

1. ✅ `tracking-init.php` header'a dahil edilmiş mi?
2. ✅ GA4 ve FB Pixel ID'leri doğru mu?
3. ✅ Console'da hata var mı?
4. ✅ jQuery yüklü mü?

### "Purchase event çalışmıyor"

1. ✅ `Odeme.php` güncellenmiş mi? (satır 245-275 kontrol edin)
2. ✅ `payment-success-tracking.php` ödeme başarılı sayfasına dahil edilmiş mi?
3. ✅ `$tracking_data` değişkeni view'da mevcut mu?

**Daha fazla yardım için:** `TRACKING-IMPLEMENTATION-GUIDE.md` dosyasının "Sorun Giderme" bölümüne bakın.

---

## 📞 DESTEK

Sorularınız veya sorunlarınız için:

- 📧 E-posta: destek@ilayulker.com
- 📖 Detaylı rehber: `TRACKING-IMPLEMENTATION-GUIDE.md`
- 🧪 Test sayfası: `event-tracking-test.html`

---

## ✅ YAPILACAKLAR LİSTESİ

Kurulum için:

- [ ] `tracking-init.php` dosyasında GA4 ve FB Pixel ID'lerini güncelle
- [ ] Header dosyasına `tracking-init.php` dahil et
- [ ] Sepet ve ürün sayfalarına `cart-tracking-helper.js` ekle
- [ ] Ödeme başarılı sayfasına `payment-success-tracking.php` ekle
- [ ] Ürün sayfasında `data-track-view-item` attribute'larını ekle
- [ ] Sepete ekle butonunda `data-track-add-to-cart` ekle
- [ ] Test siparişi ver ve purchase event'i kontrol et
- [ ] Google Analytics Real-Time raporlarını kontrol et
- [ ] Facebook Pixel Helper ile event'leri doğrula
- [ ] Debug modunu kapat (`TRACKING_DEBUG = false`)
- [ ] Canlıya al! 🚀

---

**Başarılar dileriz! 🎉**

**Versiyon:** 1.0.0
**Son Güncelleme:** 2025-11-19
