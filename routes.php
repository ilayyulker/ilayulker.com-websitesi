<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

$route['default_controller']    = 'Anasayfa';
$route['404_override'] = '';
$route['translate_uri_dashes']  = FALSE;
$route['sitemap.xml']           = '/Anasayfa/sitemapxml';



$route['admin']                                         = 'Anasayfa/hata404';
$route['admin/giris']                                   = 'Anasayfa/hata404';
$route[ADMIN_PANEL_URL]                                 = 'admin/Anasayfa';
$route[ADMIN_PANEL_URL . '/giris']                      = 'admin/Giris';
$route[ADMIN_PANEL_URL . '/(:any)']                     = 'admin/$1';
$route[ADMIN_PANEL_URL .'/siparisler/(:any)']           = 'admin/Siparisler/index/$1';
$route[ADMIN_PANEL_URL .'/odemebildirimleri/(:any)']    = 'admin/Odemebildirimleri/index/$1';


$route['giris']                                 = 'Hesabim/giris';
$route['kayit']                                 = 'Hesabim/kayit';
$route['sifre-sifirla']                         = 'Hesabim/sifremi_unuttum';
$route['sifre-sifirla/(:any)']                  = 'Hesabim/sifre_sifirla/$1';
$route['cikis']                                 = 'Hesabim/cikis';


$route['hesabim']                              = '/Uye/Hesabim/Hesabim';
$route['hesabim/duzenle']                      = '/Uye/Hesabim/Hesabim/duzenle';

$route['hesabim/adreslerim']                   = '/Uye/Hesabim/Adreslerim';
$route['hesabim/adreslerim/adresleri_cek']     = '/Uye/Hesabim/Adreslerim/adresleri_cek';
$route['hesabim/adreslerim/ekle']              = '/Uye/Hesabim/Adreslerim/islem/ekle';
$route['hesabim/adreslerim/duzenle']           = '/Uye/Hesabim/Adreslerim/islem/duzenle';
$route['hesabim/adreslerim/sil']               = '/Uye/Hesabim/Adreslerim/sil';


$route['hesabim/favoriler']                 = '/Uye/Hesabim/Favoriler';
$route['hesabim/favoriler/listeyi_cek']          = '/Uye/Hesabim/Favoriler/listeyi_cek';
$route['hesabim/favoriler/(:any)']          = '/Uye/Hesabim/Favoriler/$1';

$route['hesabim/sifre-degistir']               = '/Uye/Hesabim/Sifre_Degistir';
$route['hesabim/sifre-degistir/post']          = '/Uye/Hesabim/Sifre_Degistir/sifre_degistir';

$route['hesabim/siparislerim']                 = '/Uye/Hesabim/Siparislerim';




$route['sepet']                                 = '/Sepet';
$route['sayfa/(:any)']                       = '/Sayfalar/index/$1';
$route['hesap-numaralarimiz']                   = '/Hesap_numaralarimiz';


$route['haber/(:any)']                          = 'Haber/haber/$1'; 
$route['haber/kategori/(:any)']                 = 'Haber/haber_kategori/$1'; 
$route['haber/haberler_json/(:any)']      = 'Haber/haberler_json/$1'; 

$route['haberler']                              = 'Haber/index'; 
$route['haber-ara']                             = 'Haber/haber_ara'; 

$route['kategori/(:any)']                       = '/Urunler/kategori/$1';
$route['kategori/(:any)/(:any)']                = '/Urunler/altkategori/$1/$2';
$route['(:any)/(:any)9']                        = '/Urun/index/$1/$2';
$route['urun/(:any)9']                          = 'Urun/index/0/$2';
$route['(:any)/(:any)9/yorum-ekle']             = 'Urun/yorum_ekle/$2';

$route['siparis/takip/(:any)/indir/(:any)']     = '/Siparis/indir/$1/$2';

$route['odeme/odeme-basarili/(:any)']           = '/Odeme/sonuc/success/$1';
$route['odeme/odeme-basarisiz/(:any)']          = '/Odeme/sonuc/error/$1';

$route['odeme/yap/(:any)']                      = 'Pos/odeme/$1'; 
$route['odeme/kontrol/(:any)']                  = 'Pos/kontrol/$1'; 




