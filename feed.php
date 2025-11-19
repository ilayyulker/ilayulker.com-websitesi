<?php
// XML çıktısı için header
header("Content-Type: application/xml; charset=utf-8");

// --- Veritabanı bilgileri ---
$host = 'localhost';
$db   = 'ilayulker_ilay';
$user = 'ilayulker_ilay';
$pass = 'gQwXsfVoVH$l';
$charset = 'utf8mb4';

// PDO bağlantısı
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header("Content-Type: text/plain; charset=utf-8");
    die("DB Connection failed: " . $e->getMessage());
}

// Ürünleri çek
$sql = "SELECT urun_id, ad, aciklama, kisa_aciklama, seo, resimler, fiyat, aktif, ucretbaz 
        FROM urunler 
        WHERE aktif = 1";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll();

// XML root
$xml = new SimpleXMLElement('<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');
$channel = $xml->addChild('channel');
$channel->addChild('title', 'İlay Ulker Ürün Feed');
$channel->addChild('link', 'https://ilayulker.com');
$channel->addChild('description', 'İlay Ulker ürün feed');

// Her ürün için XML item oluştur
foreach ($products as $p) {
    $item = $channel->addChild('item');
    $item->addChild('g:id', $p['urun_id'], 'http://base.google.com/ns/1.0');
    $item->addChild('g:title', htmlspecialchars($p['ad']), 'http://base.google.com/ns/1.0');

    // description
    $desc = !empty($p['kisa_aciklama']) ? $p['kisa_aciklama'] : $p['aciklama'];
    $item->addChild('g:description', htmlspecialchars($desc), 'http://base.google.com/ns/1.0');

    // link
    $item->addChild('g:link', 'https://ilayulker.com/urun/' . $p['seo'], 'http://base.google.com/ns/1.0');

    // image
    $images = json_decode($p['resimler'], true); // JSON array'i PHP array'e çevir
    if(is_array($images) && count($images) > 0){
        $firstImage = $images[0];
    } else {
        $firstImage = ''; // fallback
    }

if(!empty($firstImage)){
    $item->addChild(
        'g:image_link',
        'https://ilayulker.com/public/uploads/urunler/' . trim($firstImage),
        'http://base.google.com/ns/1.0'
    );
}
    // availability
    $availability = $p['aktif'] == 1 ? 'in_stock' : 'out_of_stock';
    $item->addChild('g:availability', $availability, 'http://base.google.com/ns/1.0');

    // price
    $item->addChild('g:price', number_format($p['fiyat'], 2, '.', '') . ' TRY', 'http://base.google.com/ns/1.0');

    // brand
    $brand = !empty($p['ucretbaz']) ? $p['ucretbaz'] : 'İlay Ulker';
    $item->addChild('g:brand', $brand, 'http://base.google.com/ns/1.0');

    // condition
    $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
}

// XML çıktısını ver
echo $xml->asXML();
