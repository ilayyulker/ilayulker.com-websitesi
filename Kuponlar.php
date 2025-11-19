<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Kuponlar extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');

		admin_oturum_kontrol();
	}

	public function index()
	{
		$kuponlar = $this->db->get('kupon');

		$veri = [
			'kuponlar' => $kuponlar,
			'sayfa_adi' => 'kuponlar/list',
			'sayfa_title' => 'Kuponlar - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{
		$this->db->delete('kupon', array('id' => $id));
		redirect(admin_url('kuponlar'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$kupon_adi      		 	= html_sil(post('kupon_adi'));
			$kupon_kodu      		 	= html_sil(post('kupon_kodu'));
			$gecerlilik_tarihi      	= html_sil(post('gecerlilik_tarihi'));
			$indirim_miktari      		= html_sil(post('indirim_miktari'));
			$kupon_aktif         		= post('kupon_aktif') == "on" ? 1 : 0;
			$her_tek_sinir         		= post('her_tek_sinir') == "on" ? 1 : 0;
			$tek_kullanim         		= post('tek_kullanim') == "on" ? 1 : 0;

	
			if (strlen($kupon_adi) < 2) {
				$json['mesaj'] .= '<i class="bx bx-error"></i> Kupon adı boş bırakılamaz<br>';
			}
			if (strlen($kupon_kodu) < 2) {
				$json['mesaj']  .= '<i class="bx bx-error"></i> Kupon kodu boş bırakılamaz<br>';
			}
			if ($indirim_miktari < 1) {
				$json['mesaj']  .= '<i class="bx bx-error"></i> İndirim miktarı 1\'den küçük olamaz<br>';
			}

			if ($json['mesaj'] == '') {

				$veri = array(
					'baslik'     			=> $kupon_adi,
					'indirim_miktari'       => $indirim_miktari,
					'kod'    				=> $kupon_kodu,
					'gecerlilik_tarihi'    	=> $gecerlilik_tarihi,
					'aktif'    				=> $kupon_aktif,
					'her_tek_sinir'    		=> $her_tek_sinir,
					'tek_kullanim'    		=> $tek_kullanim
				);

				if ($this->db->insert('kupon', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('kuponlar'));
				}
			}


			if ($json["durum"] == "error") {
				$json["mesaj"] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
			}

			echo json_encode($json);
			exit;
		}

		$veri = [
			'sayfa_adi' => 'kuponlar/ekle',
			'sayfa_title' => 'Kupon Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{
		$kupon = $this->db->get_where("kupon", array("id" => $id))->row();

		if (!$kupon) {
			redirect(admin_url("kuponlar"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$kupon_adi      		 	= html_sil(post('kupon_adi'));
			$kupon_kodu      		 	= html_sil(post('kupon_kodu'));
			$gecerlilik_tarihi      	= html_sil(post('gecerlilik_tarihi'));
			$indirim_miktari      		= html_sil(post('indirim_miktari'));
			$kupon_aktif         		= post('kupon_aktif') == "on" ? 1 : 0;
			$her_tek_sinir         		= post('her_tek_sinir') == "on" ? 1 : 0;
			$tek_kullanim         		= post('tek_kullanim') == "on" ? 1 : 0;

			if (strlen($kupon_adi) < 2) {
				$json['mesaj'] .= '<i class="bx bx-error"></i> Kupon adı boş bırakılamaz<br>';
			}
			if (strlen($kupon_kodu) < 2) {
				$json['mesaj']  .= '<i class="bx bx-error"></i> Kupon kodu boş bırakılamaz<br>';
			}
			if ($indirim_miktari < 1) {
				$json['mesaj']  .= '<i class="bx bx-error"></i> İndirim miktarı 1\'den küçük olamaz<br>';
			}
			if ($json['mesaj'] == '') {

				$veri = array(
					'baslik'     			=> $kupon_adi,
					'indirim_miktari'       => $indirim_miktari,
					'kod'    				=> $kupon_kodu,
					'gecerlilik_tarihi'    	=> $gecerlilik_tarihi,
					'aktif'    				=> $kupon_aktif,
					'her_tek_sinir'    		=> $her_tek_sinir,
					'tek_kullanim'    		=> $tek_kullanim
				);

				$this->db->where('id', $id);
				if ($this->db->update('kupon', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('kuponlar'));
				}
			}
			if ($json["durum"] == "error") {
				$json["mesaj"] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
			}

			echo json_encode($json);
			exit;
		}

		
		$veri = [
			'row'	=> $kupon,
			'sayfa_adi' => 'kuponlar/duzenle',
			'sayfa_title' => 'Kupon Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
}
