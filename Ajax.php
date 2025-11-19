<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Ajax extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }


    public function cerez_kabul()
    {
        $cookie = array(
            'name'   => 'site_cerez',
            'value'  => true,
            'expire' => time() + (60 * 60 * 24),
            'path'   => '/',
            'prefix' => ''
        );
        set_cookie($cookie);
    }


    public function istek_listesine_ekle()
    {
        if (!uye_oturum_kontrol(0)) {
            echo "no";
            exit;
        }
        $sonuc = "no";
        $uye_id = uye()->id;
        
        if ($_POST) {
            $urun_id = $_POST['id'];
            $kontrol = $this->db->get_where('istek_listesi', array("uye_id" => $uye_id, 'urun_id' => $urun_id));

            if ($kontrol->num_rows() > 0) {
                $this->db->delete("istek_listesi", array("uye_id" => $uye_id, 'urun_id' => $urun_id));
                $sonuc =  "no";
            } else {
                $ekle_data = [
                    'uye_id' => $uye_id,
                    'urun_id' => $_POST["id"]
                ];
                $sorgu = $this->db->insert('istek_listesi', $ekle_data);

                if ($sorgu) {
                    $sonuc =  "yes";
                };
            }
        }

        $result = [
            'sonuc' => $sonuc,
            'count' => $this->Uye_model->istek_listesi_sayisi(uye()->id)
        ];
        echo json_encode($result);
    }



    public function sepete_ekle()
    {
        if ($_POST) {
            echo json_encode($this->Sepet_model->ekle());
        }
    }

    public function sepet_adet_guncelle()
    {
        if ($_POST) {
            $id = $_POST["id"];
            $adet = $_POST["adet"];

            $this->Sepet_model->adet_guncelle($id, $adet);
        }
    }

    public function sepet_urun_sil()
    {
        if ($_POST) {
            $this->Sepet_model->sil();
        }
    }


    public function h_sepet_json()
    {
        $HTML = '';
        $sepet = (object)$this->Sepet_model;

        if ($sepet->urun_sayisi > 0) {
            $HTML .= ' <div class="dropdown-cart-products">';
            foreach ($sepet->array_sepet as $sepet_id => $_sepet) {

                $m_urun = $this->Urun_model;
                $urun_row = $m_urun->urun($_sepet["urun_id"]);

                if (!$urun_row)
                    continue;

                $HTML .= '
                   

                    <div class="product">
                        <div class="product-cart-details">
                        <h4 class="product-title">
                            <a href="/' . $m_urun->kategori->seo . '/' . $urun_row->seo . '">' . $urun_row->ad . '</a>
                        </h4>
        
                        <span class="cart-product-info">
                            <span class="cart-product-qty">' . $_sepet["adet"] . '</span>
                            x ' . number_format($urun_row->fiyat, 2, '.', ',') . '₺
                        </span>
                        </div>
        
                        <figure class="product-image-container">
                        <a href="/' . $m_urun->kategori->seo . '/' . $urun_row->seo . '" class="product-image">
                            <img src="images/urunler/' . $m_urun->urun_resimleri[0] . '" alt="' . $urun_row->ad . '">
                        </a>
                        </figure>
                        <a href="#0" class="btn-remove sepet-urun-sil" id="' . $sepet_id . '" title="Ürünü Sil"><i class="icon-close"></i></a>
                    </div>


                ';
            }
            $HTML .= ' </div>';
        }



        if ($sepet->urun_sayisi == 0) {
            $HTML = '
                <ul>
                    <li class="text-center">
                        <strong><span>SEPETİNİZ BOŞ</span></strong>
                    </li>
                </ul>
            ';
        } else {
            //SEPET FOOTER
            $HTML = $HTML .  '
                    <div class="dropdown-cart-total">
                        <span>Toplam</span>

                        <span class="cart-total-price">' . number_format($sepet->urunler_tutar, 2, '.', ',') . '₺</span>
                    </div>

                    <div class="dropdown-cart-action">
                        <a href="sepet" class="btn btn-primary">Sepete Git</a>
                        <a href="odeme" class="btn btn-outline-primary-2"><span>Ödeme</span><i class="icon-long-arrow-right"></i></a>
                    </div>
            ';
        }

        $result = [
            'sepet_html' => $HTML,
            'sepet_toplami' => number_format($sepet->urunler_tutar, 2, '.', ','),
            'sepet_urun_sayisi' => $sepet->urun_sayisi
        ];

        echo json_encode($result);
    }

    public function kayitli_adres_cek()
    {
        if (!uye_oturum_kontrol(0)) {
            exit;
        }

        $json_return = array(
            'isim' => '',
            'soyisim' => '',
            'sirket_adi' => '',
            'telefon' => '',
            'il' => '',
            'ilce' => '',
            'ulke' => '',
        );

        $adres_id = post('val');

        $adresim = $this->db->get_where('adreslerim', array('uye_id' => uye()->id, 'id' => $adres_id));

        if ($adresim->num_rows() > 0) {
            $adres = $adresim->row();

            $json_return = array(
                'isim' => $adres->isim,
                'soyisim' => $adres->soyisim,
                'sirket_adi' => $adres->sirket_adi,
                'telefon' => $adres->telefon,
                'email' =>  uye()->email,
                'adres' => $adres->adres,
                'il' => $adres->il,
                'ilce' => $adres->ilce,
                'ulke' => $adres->ulke,
                'posta_kodu' => $adres->postakodu,
            );
        }

        echo json_encode($json_return);
    }
}
