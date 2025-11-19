<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Bankahesaplari extends CI_Controller
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
		$bankahesaplari = $this->db->order_by('id', 'desc')->get('banka_hesaplari');

		$veri = [
			'bankahesaplari' => $bankahesaplari,
			'sayfa_adi' => 'bankahesaplari/list',
			'sayfa_title' => 'Banka Hesapları - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{

		$bankahesabi = $this->db->get_where("banka_hesaplari", array("id" => $id))->row();

		if (!$bankahesabi) {
			redirect(admin_url("bankahesaplari"));
		}

		$img_yol = 'public/uploads/bankalar/' . $bankahesabi->banka_logo;
		if (is_file($img_yol)) {
			unlink($img_yol);
		}

		$this->db->delete('banka_hesaplari', array('id' => $id));
		redirect(admin_url('bankahesaplari'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$banka_ad 			= post('banka_ad');
			$alici_ad 			= post('alici_ad');
			$sube 				= post('sube');
			$sube_kodu 			= post('sube_kodu');
			$hesap_numarasi 	= post('hesap_numarasi');
			$iban 				= post('iban');

			$this->form_validation->set_rules('banka_ad', 'Banka Ad', 'trim|required|min_length[3]');

			$validat = $this->form_validation->run();

			if ($validat) {

				$img_name = "";
				$uploadResponse = array();
				if (strlen($_FILES['img']['name']) > 2) {
					$img_name = rastgele_sifre(10);
					$uploadResponse =  resim_yukle("img", "public/uploads/bankalar/", $img_name, 0, 0);

					if ($uploadResponse['hata'] == "0") {
						$img_name = $uploadResponse["resim_name"];
					} else {
						$json["mesaj"] .= $uploadResponse["hata"];
					}
				}

				$veri = array(
					'banka_ad' 			=> $banka_ad,
					'alici_ad' 			=> $alici_ad,
					'sube' 				=> $sube,
					'sube_kodu' 		=> $sube_kodu,
					'hesap_numarasi' 	=> $hesap_numarasi,
					'iban' 				=> $iban,
					'banka_logo' 		=> $img_name
				);

				if ($this->db->insert('banka_hesaplari', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('bankahesaplari'));
				}
			} else {
				$json["mesaj"] = validation_errors('<i class="fa fa-times"></i> ', '<br>');
			}

			if ($json["durum"] == "error") {
				$json["mesaj"] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
			}

			echo json_encode($json);
			exit;
		}

		$veri = [
			'sayfa_adi' => 'bankahesaplari/ekle',
			'sayfa_title' => 'Banka Hesabı Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{
		$bankahesabi = $this->db->get_where("banka_hesaplari", array("id" => $id))->row();

		if (!$bankahesabi) {
			redirect(admin_url("bankahesaplari"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$banka_ad 			= post('banka_ad');
			$alici_ad 			= post('alici_ad');
			$sube 				= post('sube');
			$sube_kodu 			= post('sube_kodu');
			$hesap_numarasi 	= post('hesap_numarasi');
			$iban 				= post('iban');

			$this->form_validation->set_rules('banka_ad', 'Başlık', 'trim|required|min_length[3]');

			$validat = $this->form_validation->run();

			if ($validat) {


				$img_name = "";
				$uploadResponse = array();
				if (strlen($_FILES['img']['name']) > 2) {
					$img_name = rastgele_sifre(10);
					$uploadResponse =  resim_yukle("img", "public/uploads/bankalar/", $img_name, 0, 0);

					if ($uploadResponse['hata'] == "0") {

						$img_yol = 'public/uploads/bankalar/' . $bankahesabi->banka_logo;
						if (is_file($img_yol)) {
							unlink($img_yol);
						}


						$img_name = $uploadResponse["resim_name"];
					} else {
						$json["mesaj"] .= $uploadResponse["hata"];
					}
				} else {
					$img_name = $bankahesabi->banka_logo;
				}


				if ($json["mesaj"] == '') {

					$veri = array(
						'banka_ad' 			=> $banka_ad,
						'alici_ad' 			=> $alici_ad,
						'sube' 				=> $sube,
						'sube_kodu' 		=> $sube_kodu,
						'hesap_numarasi' 	=> $hesap_numarasi,
						'iban' 				=> $iban,
						'banka_logo' 		=> $img_name
					);

					$this->db->where('id', $id);
					if ($this->db->update('banka_hesaplari', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('bankahesaplari'));
					}
				}
			} else {
				$json["mesaj"] = validation_errors('<i class="fa fa-times"></i> ', '<br>');
			}

			if ($json["durum"] == "error") {
				$json["mesaj"] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
			}

			echo json_encode($json);
			exit;
		}


		$veri = [
			'row'	=> $bankahesabi,
			'sayfa_adi' => 'bankahesaplari/duzenle',
			'sayfa_title' => 'Banka Hesabı Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
}
