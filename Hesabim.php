<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');


class Hesabim extends CI_Controller
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
	//	$siparisler = $this->Site_model->firma_siparisler(['firma_uye_id' => uye()->id], 'id desc', 3);


		//$firma_iletisimler = $this->Site_model->firma_iletisimler(['firma_uye_id' => uye()->id], 'id desc', 3);

		//$firmalarim = $this->Site_model->firmalar(['uye_id' => uye()->id], 'id desc', 3);


		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
			'sayfa_adi' => 'hesabim/index',
			'sayfa_title' => 'Profilim - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar,


			//'siparisler' => $siparisler,
			//'firma_iletisimler' => $firma_iletisimler,
			//'firmalarim' => $firmalarim
		));
	}
	public function bilgilerimi_duzenle()
	{
		uye_oturum_kontrol();

		if ($_POST) {
			$json = ['durum' => 'error', 'mesaj'   => ''];

			$adsoyad	= post('adsoyad');
			$telefon	= post('telefon');

			if (strlen($adsoyad) < 3 || strlen($adsoyad) > 50) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Lütfen geçerli bir ad soyad girin<br>';
			}

			if (strlen($telefon) != 10) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Telefon başın 0 olmadan 10 hane olmalıdır<br>';
			}

			if ($json['mesaj'] == '') {
				$veri = [
					'adsoyad' => $adsoyad,
					'telefon' => $telefon
				];
				$this->db->where('id', uye()->id);
				if ($this->db->update('uyeler', $veri)) {
					$json['durum'] = 'success';
					$json['mesaj'] = '<div class="alert alert-success"> Bilgileriniz güncellendi </div>';
				}
			} else {
				$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
			}

			print_r(json_encode($json));
			exit;
		}
		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/bilgilerimi_duzenle',
			'sayfa_title' => 'Bilgilerimi Düzenle - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar
		));
	}
	public function sifre_degistir()
	{
		uye_oturum_kontrol();

		if ($_POST) {
			$json = ['durum' => 'error', 'mesaj'   => ''];

			$eski_sifre		= post('eski_sifre');
			$yeni_sifre1	= post('yeni_sifre1');
			$yeni_sifre2	= post('yeni_sifre2');


			if ($yeni_sifre1 != $yeni_sifre2) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Şifreler uyuşmuyor<br>';
			}

			if (strlen($yeni_sifre1) < 5) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Yeni şifre minimum 6 karakter olmalıdır<br>';
			}
			if (uye()->sifre != sha1($eski_sifre)) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Eski şifrenizi hatalı girdiniz<br>';
			}

			if ($json['mesaj'] == '') {

				$veri = [
					'sifre' => sha1($yeni_sifre1)
				];

				$this->db->where('id', uye()->id);
				if ($this->db->update('uyeler', $veri)) {

					$_SESSION["uye"]["sifre"] = sha1($yeni_sifre1);


					$json['durum'] = 'success';
					$json['mesaj'] = '<div class="alert alert-success"> Bilgileriniz güncellendi </div>';
				}
			} else {
				$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
			}

			print_r(json_encode($json));
			exit;
		}
		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/bilgilerimi_duzenle',
			'sayfa_title' => 'Bilgilerimi Düzenle - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar
		));
	}

	public function firmalarim()
	{
		uye_oturum_kontrol();

		$firmalarim = $this->Site_model->firmalar(['uye_id' => uye()->id], 'id desc');

		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/firmalarim',
			'sayfa_title' => 'Firmalarım - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar,

			'firmalarim' => $firmalarim
		));
	}

	public function siparisler()
	{
		uye_oturum_kontrol();

		$siparisler = $this->Site_model->firma_siparisler(['firma_uye_id' => uye()->id], 'id desc');

		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/siparisler',
			'sayfa_title' => 'Siparişler - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar,

			'siparisler' => $siparisler
		));
	}

	public function siparis_json()
	{
		uye_oturum_kontrol();

		$siparis_id = post('siparis');

		$siparis = $this->Site_model->firma_siparisler(['id' => $siparis_id, 'firma_uye_id' => uye()->id]);

		if ($siparis->num_rows() == 0) {
			echo json_encode(array(
				'id' 				=> $siparis_id,
				'firma_slug' 		=> "",
				'firma_baslik' 		=> "",
				'adsoyad' 			=> "",
				'telefon' 			=> "",
				'email' 			=> "",
				'adres' 			=> "",
				'siparis' 			=> "Sipariş Bilgileri Bulunamadı!",
			));
			exit;
		}
		$siparis = $siparis->row();

		$firma_baslik 	= "";
		$firma_slug 	= "";

		$firma = $this->Site_model->firmalar(['id' => $siparis->firma_id])->row();
		if (isset($firma->baslik)) {
			$firma_baslik = $firma->baslik;
			$firma_slug = $firma->slug;
		}

		$this->db->where('id', $siparis_id);
		$this->db->update('firma_siparisler', ['yeni' => 0]);

		echo json_encode(array(
			'id' 				=> $siparis->id,
			'firma_slug' 		=> $firma_slug,
			'firma_baslik' 		=> $firma_baslik,
			'adsoyad' 			=> $siparis->adsoyad,
			'telefon' 			=> $siparis->telefon,
			'email' 			=> $siparis->email,
			'adres' 			=> $siparis->adres,
			'siparis' 			=> $siparis->siparis,
		));
	}

	public function iletisim()
	{
		uye_oturum_kontrol();

		$firma_iletisimler = $this->Site_model->firma_iletisimler(['firma_uye_id' => uye()->id], 'id desc');

		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/iletisim_mesajlari',
			'sayfa_title' => 'Firmalarım - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar,

			'firma_iletisimler' => $firma_iletisimler
		));
	}

	public function iletisim_json()
	{
		uye_oturum_kontrol();

		$iletisim_id = post('iletisim');

		$iletisim = $this->Site_model->firma_iletisimler(['id' => $iletisim_id, 'firma_uye_id' => uye()->id]);

		if ($iletisim->num_rows() == 0) {
			echo json_encode(array(
				'id' 				=> "",
				'firma_slug' 		=> "",
				'firma_baslik' 		=> "",
				'adsoyad' 			=> "",
				'telefon' 			=> "",
				'email' 			=> "",
				'mesaj' 			=> "İletişim Bilgileri Bulunamadı!",
			));
			exit;
		}
		$iletisim = $iletisim->row();

		$firma_baslik 	= "";
		$firma_slug 	= "";

		$firma = $this->Site_model->firmalar(['id' => $iletisim->firma_id])->row();
		if (isset($firma->baslik)) {
			$firma_baslik = $firma->baslik;
			$firma_slug = $firma->slug;
		}

		$this->db->where('id', $iletisim_id);
		$this->db->update('firma_iletisim', ['yeni' => 0]);

		echo json_encode(array(
			'id' 				=> $iletisim->id,
			'firma_slug' 		=> $firma_slug,
			'firma_baslik' 		=> $firma_baslik,
			'adsoyad' 			=> $iletisim->adsoyad,
			'telefon' 			=> $iletisim->telefon,
			'email' 			=> $iletisim->email,
			'mesaj' 			=> $iletisim->mesaj,
		));
	}


	public function giris()
	{
		if ($_POST) {
			$this->giris_post();
		}
		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/auth/giris',
			'sayfa_title' => 'Giriş Yap - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar
		));
	}

	public function kayit()
	{
		if ($_POST) {
			$this->kayit_post();
		}
		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'hesabim/auth/kayit',
			'sayfa_title' => 'Kayıt Ol - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar
		));
	}

	public function sifremi_unuttum()
	{
	    $hata = "";
		if ($_POST) {
			

			$email	= post('email');

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$hata  .= '<i class="fa fa-times"></i> Geçersiz E-Mail.<br>';
			}

			$emailsor = $this->db->get_where('uyeler', ['email' => $email]);
			if ($emailsor->num_rows() == 0) {
				$hata  .= '<i class="fa fa-times"></i> E-Mail bulunamadı! <br>';
			}

			if ($hata == '') {
				$uye_row = $emailsor->row();

				$this->load->model('Mail_model');
				$mailresponse = $this->Mail_model->sifre_sifirlama_maili($uye_row);

				if ($mailresponse) {
					$hata  = ' Şifre sıfırlama maili gönderildi! <br>';
				} else {
					$hata = '<div class="alert alert-danger"><i class="fa fa-times"></i> Gönderilemedi! <br></div> ';
				}
			} else {
				$hata = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
			}

		
		}
		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'uye/sifremi_unuttum',
			'sayfa_title' => 'Şifremi Unuttum - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar
		));
	}

	public function sifre_sifirla($mailkod)
	{
	
		$uye = $this->db->get_where('uyeler', ['mailkod' => $mailkod]);
		if ($uye->num_rows() == 0 || strlen($mailkod) < 3) {
			redirect(base_url());
			exit;
		}
		$uye = $uye->row();

		if ($_POST) {
			$json = ['durum' => 'error', 'mesaj'   => ''];

			$sifre      	= post('sifre');
			$sifre2      	= post('sifre2');

			

			if ($sifre != $sifre2) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Şifreler uyuşmuyor<br>';
			}

			if (strlen($sifre) < 5) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Şifre minimum 6 karakter olmalıdır<br>';
			}

			if ($json['mesaj'] == '') {

				$this->db->where('id', $uye->id);
				if ($this->db->update('uyeler', ['sifre' => sha1($sifre), 'mailkod' => '0'])) {
					$json['durum'] = 'success';
					$json['mesaj'] = '<div class="alert alert-success"> Şifreniz başarıyla değiştirildi</div>
						<meta http-equiv="refresh" content="2;URL=' . base_url('giris') . '">
					';
				}
			} else {
				$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
			}

			print_r(json_encode($json));
			exit;
		}

		$this->load->view($this->siteayar->tema . '/index', array(
			'sayfa_adi' => 'uye/sifre_sifirla',
			'sayfa_title' => 'Şifre Sıfırla - ' . $this->siteayar->site_baslik,
			'siteayar' => $this->siteayar,
			'uye' => $uye
		));
	}

	public function cikis()
	{
		unset($_SESSION['uye']);
		redirect(base_url());
	}

	public function giris_post()
	{

		$json = ['durum' => 'error', 'mesaj'   => ''];


		$email	= post('email');
		$sifre	= sha1(post('sifre'));


		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$json['mesaj']  .= '<i class="fa fa-times"></i> Geçersiz E-Mail.<br>';
		}

		if ($json['mesaj'] == '') {

			$veri = [
				'email' => $email,
				'sifre' => $sifre
			];

			$sorgu = $this->db->get_where('uyeler', $veri);
			if ($sorgu->num_rows() > 0) {
				$_SESSION["uye"]["email"] = $sorgu->row()->email;
				$_SESSION["uye"]["sifre"] = $sorgu->row()->sifre;

				$json['durum'] = 'success';
				$json['mesaj'] =
					'<div class="alert alert-success"> Giriş yapıldı </div>
					<meta http-equiv="refresh" content="1;URL=' . base_url('hesabim') . '">
				';
			} else {
				$json['mesaj'] = 'Email veya şifre yanlış!';
			}
		} else {
			$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
		}

		print_r(json_encode($json));
		exit;
	}

	public function kayit_post()
	{

		$json = ['durum' => 'error', 'mesaj'   => ''];


		$adsoyad  		= post('adsoyad');
		$telefon        = post('telefon');
		$email        	= post('email');
		$sifre      	= post('sifre');
		$sifre2      	= post('sifre2');


		if (strlen($adsoyad) < 3) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> Lütfen geçerli bir ad soyad girin<br>';
		}

		if (strlen($telefon) != 10) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> Telefon başın 0 olmadan 10 hane olmalıdır<br>';
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$json['mesaj']  .= '<i class="fa fa-times"></i> Geçersiz E-Mail.<br>';
		}

		$emailsor = $this->db->get_where('uyeler', ['email' => $email]);
		if ($emailsor->num_rows() > 0) {
			$json['mesaj']  .= '<i class="fa fa-times"></i> E-Mail zaten kullanılmaktadır<br>';
		}
		$telefonsor = $this->db->get_where('uyeler', ['telefon' => $telefon]);
		if ($telefonsor->num_rows() > 0) {
			$json['mesaj']  .= '<i class="fa fa-times"></i> Telefon numarası zaten kullanılmaktadır<br>';
		}

		if ($sifre != $sifre2) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> Şifreler uyuşmuyor<br>';
		}

		if (strlen($sifre) < 5) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> Şifre minimum 6 karakter olmalıdır<br>';
		}

		$sifre = sha1($sifre);

		if ($json['mesaj'] == '') {

			$veri = [
				'adsoyad' => $adsoyad,
				'email' => $email,
				'telefon' => $telefon,
				'sifre' => $sifre,
				'ip'	=> get_ip(),
				'kayit_tarihi' => date('Y-m-d H:i:s'),
				'durum' => '1'
			];

			if ($this->db->insert('uyeler', $veri)) {

				$_SESSION["uye"]["email"] = $email;
				$_SESSION["uye"]["sifre"] = $sifre;

				$json['durum'] = 'success';
				$json['mesaj'] =
					'<div class="alert alert-success"> Kayıt başarılı! Yönlendiriliyor... </div>
					<meta http-equiv="refresh" content="2;URL=' . base_url('hesabim') . '">
					';

				$this->load->model('Bildir_model');
				//$this->Bildir_model->yonetici_bildir('yeni_kayit', $veri);

			}
		} else {
			$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
		}

		print_r(json_encode($json));
		exit;
	}
}
