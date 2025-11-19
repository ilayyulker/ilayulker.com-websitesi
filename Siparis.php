<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Siparis extends CI_Controller
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

	public function sorgula()
	{
		$hata = '';

		if ($_POST) {
			$siparis_no = post('siparis_no');
			$siparis = $this->db->get_where("siparisler", array('sipariskey' => $siparis_no));

			if ($siparis->num_rows() < 1) {
				$hata = "Sipariş Bulunamadı";
			} else {
				redirect("siparis/takip/" . $siparis_no);
				exit;
			}
		}


		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
			'hata' => $hata,
			'sayfa_adi' 	=> 'siparis/siparis_sorgula',
			'sayfa_title' 	=> 'Sipariş Sorgula - ' . $this->siteayar->site_baslik
		));

	}

	public function takip($siparis_key)
	{

		$siparis = $this->db->get_where("siparisler", array('sipariskey' => $siparis_key));

		$siparis_no = $siparis->row()->sipariskey;


		if ($siparis->num_rows() < 1) {
			redirect("siparis/sorgula");
		}

		if ($_POST) {
			$this->odeme_bildirimi_olustur($siparis->row()->id);
			exit;
		}

		$veri = [
			'siparis' => $siparis->row(),
			'sayfa_adi' => 'siparis/siparis_takip',
			'sayfa_title' => '#' . $siparis_no . ' Siparişiniz -' . siteayar()->site_baslik,

			'siteayar' => siteayar()
		];
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function odeme_bildirimi_olustur($siparis_no)
	{
		$isim_soyisim           		= html_sil(post('isim_soyisim'));
		$gonderilen_toplam_tutar        = html_sil(post('gonderilen_toplam_tutar'));
		$banka_id           			= html_sil(post('banka'));
		$eposta           				= html_sil(post('eposta'));
		$telefon           				= html_sil(post('telefon'));
		$not           					= html_sil(post('not'));
		$dekont 						= "";
		// file: dekont

		$hata_str = '';
		$html_hata = '';
		$sonuc = 'no';

		if (strlen($isim_soyisim) < 2) {
			$hata_str .= '<i class="fa fa-times"></i> İsim boş bırakılamaz<br>';
		}
		if (strlen($gonderilen_toplam_tutar) < 1) {
			$hata_str .= '<i class="fa fa-times"></i> Gönderilen Toplam Tutar 1\'den küçük olamaz<br>';
		}
		if (strlen($banka_id) < 0) {
			$hata_str .= '<i class="fa fa-times"></i> Banka Seçimi Yapın!<br>';
		}
		if (strlen($eposta) < 2) {
			$hata_str .= '<i class="fa fa-times"></i> E-Posta Boş Bırakılamaz<br>';
		}
		if (strlen($telefon) != 11) {
			$hata_str .= '<i class="fa fa-times"></i> Telefon numaranız başında 0 ile birlikte 11 karakter olmalıdır<br>';
		}


		$banka_hesaplari = $this->db->get_where("banka_hesaplari", array("id" => $banka_id));
		if ($banka_hesaplari->num_rows() < 1) {
			$hata_str .= '<i class="fa fa-times"></i> Geçersiz banka seçimi!<br>';
		}

		if ($hata_str == '') {
			if (isset($_FILES['dekont']['name'])) {
				$dekont_array = dekont_yukle("dekont", "public/uploads/dekontlar/");
				if ($dekont_array["durum"] == "ok") {
					$dekont = $dekont_array["dosya_adi"];
				} else {
					$hata_str .= $dekont_array["mesaj"] . ' <br>';
				}
			} else {
				$hata_str .= '<i class="fa fa-times"></i> Dekont boş bırakılamaz!<br>';
			}
		}

		if ($hata_str == '') {

			$veri = [
				'siparis_no'        		=> $siparis_no,
				'isim_soyisim'     			=> $isim_soyisim,
				'gonderilen_toplam_tutar'   => $gonderilen_toplam_tutar,
				'dekont'   					=> $dekont,
				'banka_id'    				=> $banka_id,
				'eposta'        			=> $eposta,
				'telefon'            		=> $telefon,
				'notu'          			=> $not,
				'durum'          			=> "inceleniyor",
				'tarih'       				=> date("Y-m-d H:i:s"),
				'ip'       					=> get_ip()
			];


			if ($this->db->insert('odeme_bildirimleri', $veri)) {
				$sonuc = 'yes';
				$html_hata =  ' <div class="alert alert-success"> Ödeme bildirimi oluşturuldu! </div>';
			}
		} else {
			$sonuc = 'no';
			$html_hata = '<div class="alert alert-danger">' . $hata_str . '</div> ';
		}

		$json = [
			'sonuc' => $sonuc,
			'msj'   => $html_hata
		];

		echo json_encode($json);
	}

	public function indir($siparis_key, $urun_id)
	{
		$siparis = $this->db->get_where("siparisler", array('sipariskey' => $siparis_key));

		if ($siparis->num_rows() > 0) {
			$s_urun_verileri = (array)json_decode($siparis->row()->urun_verileri);
			if ($s_urun_verileri[$urun_id]) {
				$_urun = json_decode($s_urun_verileri[$urun_id]);
				$dosya = $_urun->urun_veri;
				if ((isset($dosya)) && (file_exists("public/uploads/dosyalar/" . $dosya))) {
					header("Content-length: " . filesize("public/uploads/dosyalar/" . $dosya));
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . $dosya . '"');
					readfile("public/uploads/dosyalar/" . "$dosya");
				} else {
					echo "Dosya Bulunamadı veya Kaldırılmış";
				}
			}
		}
	}
}
