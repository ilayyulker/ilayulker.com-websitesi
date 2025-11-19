<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Odeme extends CI_Controller
{
	private $_extraData = NULL;
	private $siteayar = NULL;
	private $tasarim_ayar = NULL;
	private $odeme_ayar = NULL;
	private $_Market = NULL;
	private $_Sepet = NULL;


	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');

		$this->siteayar = siteayar();
		$this->tasarim_ayar = tasarim_ayar();
		$this->odeme_ayar = odeme_ayar();
		$this->_Market = $this->Market_model;
		$this->_Sepet = $this->Sepet_model;

		$this->_extraData = array(
			'siteayar'			=> $this->siteayar,
			'tasarim_ayar'		=> $this->tasarim_ayar,
			'odeme_ayar'		=> $this->odeme_ayar,
			'_Market'			=> $this->_Market,
			'_Sepet'			=> $this->_Sepet,
		);
		$this->_extraData = (object)$this->_extraData;
	}

	public function index()
	{
		$sepet = (object)$this->Sepet_model;
		if ($sepet->urun_sayisi == 0) {
			redirect(base_url());
		}

		$_SESSION["odeme_mod"] = true;

		$hata = "";
		if ($_POST) {

			$hata = $this->hizli_odeme();
		}

		$veri = [
			'hata' => $hata,
			'sayfa_adi' => 'odeme/odeme',
			'sayfa_title' => 'Ödeme Yap - ' . siteayar()->site_baslik,

			'siteayar' => siteayar()
		];
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function hizli_odeme()
	{

		$method         = html_sil(post('method'));

		$eposta         = html_sil(post('email'));
		$isim           = html_sil(post('isim'));
		$soyisim        = html_sil(post('soyisim'));
		$tcno        	= html_sil(post('tcno'));
		$sirket_adi     = html_sil(post('sirket-adi'));
		$adres          = html_sil(post('adres'));
		$il             = html_sil(post('il'));
		$ilce           = html_sil(post('ilce'));

		$telefon        = html_sil(post('telefon'));
		$postakodu      = html_sil(post('postakodu'));



		$json = array('durum' => 'error', 'mesaj' => '');

	

		/*$kosul1 	= post('kosul1') == "on" ? 1 : 0;
		$kosul2 	= post('kosul2') == "on" ? 1 : 0;

		if ($kosul1 == 0 || $kosul2 == 0) {
			$json['mesaj'] .= ' - Koşulları onaylamanız gerekmektedir.' . post('kosul1');
		}*/

		if (strlen($eposta) < 2) {
			$json['mesaj'] .= ' - E-Mail boş bırakılamaz<br>';
		}
		if (strlen($isim) < 2) {
			$json['mesaj'] .= ' - İsim boş bırakılamaz<br>';
		}
		if (strlen($sirket_adi) < 2) {
			$sirket_adi .= "";
		}
		if (strlen($adres) < 2) {
			$json['mesaj'] .= ' - Adres 1 boş bırakılamaz<br>';
		}
		if (strlen($il) < 2) {
			$json['mesaj'] .= ' - İl boş bırakılamaz<br>';
		}
		if (strlen($ilce) < 2) {
			$json['mesaj'] .= ' - İlçe boş bırakılamaz<br>';
		}
		if (strlen($soyisim) < 2) {
			$json['mesaj'] .= ' - Soyisim boş bırakılamaz<br>';
		}
		if (strlen($telefon) != 11) {
			$json['mesaj'] .= ' - Telefon numaranız başında 0 ile birlikte 11 karakter olmalıdır<br>';
		}
		if (strlen($postakodu) < 2) {
			$json['mesaj'] .= ' - Posta kodu boş bırakılamaz<br>';
		}

	/*
		if (odeme_ayar()->siparis_formu_tc) {

			if (strlen($tcno) != 11) {
				$json['mesaj'] .= ' - Lütfen 11 haneli TC Kimlik Numaranızı girin<br>';
			}
		}*/

	

		if ($json['mesaj'] == '') {

			$adres_verisi = [
				'eposta'        => $eposta,
				'isim'          => $isim,
				'soyisim'       => $soyisim,
				'tcno'			=> "",
				'sirket_adi'    => $sirket_adi,
				'adres'        => $adres,
				'il'            => $il,
				'ilce'          => $ilce,
				'telefon'       => $telefon,
				'postakodu'     => $postakodu
			];

			if (odeme_ayar()->siparis_formu_tc) {
				$adres_verisi['tcno'] = $tcno;
			}

			$siparis_notu = html_sil(post("siparis_notu"));

			if (strlen($siparis_notu) < 1) {
				$siparis_notu = "";
			}

			$_SESSION["siparis_notu"] = $siparis_notu;

			$stok_kontrol = $this->Sepet_model->sepet_stok_kontrol();

	
	
			if ($stok_kontrol) {

		
				if ($method == "kredi_karti") {
					$method 	= "kredi_karti";

					$siparis_bilgi = array(
						'method' => "kredi_karti",
						'icerik' => ''
					);

					$sepet_toplam_tutar = $this->Sepet_model->toplam_tutar;
					
	
					$sipariskey = rand(1000000, 9999999);
					$siparis_key = $this->Siparis_model->siparis_olustur($sipariskey, $method, (object)$adres_verisi, $siparis_bilgi, $siparis_notu, $sepet_toplam_tutar, "odeme_bekliyor");

				
					$json['durum'] = 'success';
					$json['mesaj'] =  'odeme/yap/' . $_SESSION['odeme_siparis_id'];
				
				} else if ($method == "havale_eft") {
					$method 	= "havale_eft";

					$secilen_banka_id = post("banka");

					$siparis = array(
						'secilen_banka_id' => $secilen_banka_id
					);
					$siparis_bilgi = array(
						'method' => "havale_eft",
						'icerik' => json_encode($siparis)
					);

					$sepet_toplam_tutar = $this->Sepet_model->toplam_tutar;
					$sipariskey = rand(1000000, 9999999);
					$siparis_key = $this->Siparis_model->siparis_olustur($sipariskey, $method, (object)$adres_verisi, $siparis_bilgi, $siparis_notu, $sepet_toplam_tutar, "odeme_bekliyor");

					$json['durum'] = 'success';
					$json['mesaj'] =  'siparis/takip/' . $siparis_key;
				} else if ($method == "kapida_odeme") {

					$siparis_bilgi = array(
						'method' => "kapida_odeme",
						'icerik' => ""
					);

					$sepet_toplam_tutar = $this->Sepet_model->toplam_tutar;
					$sipariskey = rand(1000000, 9999999);
					$siparis_key = $this->Siparis_model->siparis_olustur($sipariskey, $method, (object)$adres_verisi, $siparis_bilgi, $siparis_notu, $sepet_toplam_tutar, "odeme_bekliyor");

					$json['durum'] = 'success';
					$json['mesaj'] =  'siparis/takip/' . $siparis_key;
				}
			}
		} else {
			$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
		}

		echo json_encode($json);
		exit;
	}

	public function sonuc($tip, $siparis_no)
	{
	    unset($_SESSION["sepet"]);

		$veri = [
			'sayfa_adi' => '/odeme/odeme-sonuc',
			'siteayar' => siteayar()
		];

		$siparis = $this->db->get_where("siparisler", array('sipariskey' => $siparis_no));

		if ($siparis->num_rows() == 0) {
			redirect(base_url());
		}

		$siparis_data = $siparis->row();

		if ($tip == "success") {
			$veri["siparis_key"] = $siparis_data->sipariskey;
			$veri["tip"] = $tip;
			$veri["sayfa_title"] = 'Ödeme Başarılı - ' . siteayar()->site_baslik;

			// PURCHASE EVENT TRACKING İÇİN VERİ HAZIRLAMA
			$sepet_items = json_decode($siparis_data->sepet);
			$tracking_items = array();

			if ($sepet_items) {
				foreach ($sepet_items as $item) {
					$tracking_items[] = array(
						'id' => isset($item->urun_id) ? $item->urun_id : '',
						'name' => isset($item->urun_ad) ? $item->urun_ad : '',
						'price' => isset($item->urun_fiyat) ? floatval($item->urun_fiyat) : 0,
						'quantity' => isset($item->adet) ? intval($item->adet) : 1,
						'category' => isset($item->kategori) ? $item->kategori : ''
					);
				}
			}

			// Kupon bilgisi varsa ekle
			$kupon_bilgi = json_decode($siparis_data->kupon_bilgi);
			$kupon_kodu = '';
			if (isset($kupon_bilgi->kupon_kodu) && $kupon_bilgi->kupon_kodu != "0") {
				$kupon_kodu = $kupon_bilgi->kupon_kodu;
			}

			$veri["tracking_data"] = array(
				'order_id' => $siparis_data->sipariskey,
				'total' => floatval($siparis_data->tutar),
				'tax' => 0, // Vergi bilgisi varsa buraya ekleyin
				'shipping' => 0, // Kargo bilgisi varsa buraya ekleyin
				'coupon' => $kupon_kodu,
				'items' => $tracking_items
			);
		} else {
			$veri["siparis_key"] = $siparis_data->sipariskey;
			$veri["tip"] = $tip;
			$veri["sayfa_title"] = 'Ödeme Başarısız - ' . siteayar()->site_baslik;
		}

		$this->load->view($this->siteayar->tema . '/index', $veri, $this->_extraData);
	}

	//kupon
	public function kupon()
	{
		if ($_POST) {
			$json = array();
			$kupon_kodu = html_sil($_POST["kupon_kodu"]);
			$sess_kupon = $this->Kupon_model->sess_kupon();
			if ($kupon_kodu != $sess_kupon) {
				if ($this->Kupon_model->kupon_kontrol($kupon_kodu)) {
					$kupon_row = $this->Kupon_model->get_kupon($kupon_kodu);

					if (date("Y-m-d H:i:s", strtotime($kupon_row->gecerlilik_tarihi)) < date("Y-m-d H:i:s")) {
						$json['sonuc'] 			= 'no';
						$json['msj'] = ' <div class="alert alert-danger mt-3">Bu indirim kuponunun süresi dolmuş.</div>';
						echo json_encode($json);
						exit;
					}

					if ($kupon_row->aktif == "0") {
						$json['sonuc'] 			= 'no';
						$json['msj'] = ' <div class="alert alert-danger mt-3">Kupon Aktif Değil</div>';
						echo json_encode($json);
						exit;
					}

					if ($kupon_row->tek_kullanim == "1") {
						$kupon_blg = $this->Kupon_model->kuponu_kullanan_siparisler($kupon_row->id);

						if ($kupon_blg["sayisi"] > 0) {
							$json['sonuc'] 			= 'no';
							$json['msj'] = ' <div class="alert alert-danger mt-3">Kupon Kullanılmış!</div>';
							echo json_encode($json);
							exit;
						}
					}

					if ($kupon_row->her_tek_sinir == "1") {

						//Her ip 1 kez kullanabilirr
						$izin = true;
						$siparisler = $this->db->get("siparisler");


						foreach ($siparisler->result() as $siparis) {
							$kupon_bilgi = json_decode($siparis->kupon_bilgi);

							if ($kupon_bilgi->kupon_kodu != "0") {
								if ($kupon_bilgi->kupon_kodu == $kupon_row->kod) {

									// aynı ipde kullanılmışsa kupon kullandırma
									if ($siparis->ip == get_ip()) {
										$izin = false;
									}
								}
							}
						}


						if (!$izin) {
							$json['sonuc'] 			= 'no';
							$json['msj'] = ' <div class="alert alert-danger mt-3">Bu kuponu daha önce kullanmışsınız!</div>';
							echo json_encode($json);
							exit;
						}
					}

					if (date("Y-m-d H:i:s", strtotime($kupon_row->gecerlilik_tarihi)) < date("Y-m-d H:i:s")) {
						$json['sonuc'] 			= 'no';
						$json['msj'] = ' <div class="alert alert-danger mt-3">Bu indirim kuponunun süresi dolmuş.</div>';
						echo json_encode($json);
						exit;
					}


					$_SESSION["kupon_kodu"] = $kupon_row->kod;
					$_SESSION["kupon_turu"] = $kupon_row->indirim_turu;
					$json['sonuc'] 			= 'yes';
					$json['kupon_kodu'] 	= $kupon_row->kod;
					$json['kupon_indirim']  = $kupon_row->indirim_miktari;
					if($kupon_row->indirim_turu == "yuzde"){
						$json['msj'] = ' <div class="alert alert-success mt-3">Tebrikler, %' . $kupon_row->indirim_miktari . ' indirim sepetinize uygulandı.</div>';
					}else{
						$json['msj'] = ' <div class="alert alert-success mt-3">Tebrikler, ' . $kupon_row->indirim_miktari . 'TL değerindeki indirim sepetinize uygulandı.</div>';
					}
					
				} else {
					$json['sonuc'] 			= 'no';
					$json['msj'] = ' <div class="alert alert-danger mt-3">Kupon bulunamadı! Süresi dolmuş olabilir.</div>';
				}
			} else {
				$json['sonuc'] 			= 'no';
				$json['msj'] = ' <div class="alert alert-danger mt-3">Bu kodu şuan kullanıyorsunuz.</div>';
			}

			echo json_encode($json);
		}
	}

	public function kupon_sil()
	{
		$json['sonuc'] = 'no';

		if (isset($_SESSION["kupon_kodu"])) {
			unset($_SESSION["kupon_kodu"]);

			$json['sonuc'] 			= 'yes';
			$json['msj'] = ' <div class="alert alert-success mt-3">Kupon indirimi kaldırıldı.</div>';
		}

		echo json_encode($json);
	}
}
