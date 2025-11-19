<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Banner extends CI_Controller
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
		$banner = $this->db->get('banner');

		$veri = [
			'banner' => $banner,
			'sayfa_adi' => 'banner/list',
			'sayfa_title' => 'Bannerlar - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{
		$banner = $this->db->get_where("banner", array("id" => $id))->row();

		if (!$banner) {
			redirect(admin_url("banner"));
		}

		
		$img_yol = 'public/uploads/banner/' . $banner->img;
		if (is_file($img_yol)) {
			unlink($img_yol);
		}


		$this->db->delete('banner', array('id' => $id));
		redirect(admin_url('banner'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];


			$baslik 			= post('baslik');
			$sira 				= post('sira');
			$link 				= post('link');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[1]');

			$validat = $this->form_validation->run();

			if ($validat) {

				$img_name = "";
				$uploadResponse = array();
				if (strlen($_FILES['img']['name']) > 2) {
					$img_name = rastgele_sifre(10);
					$uploadResponse =  resim_yukle("img", "public/uploads/banner/", $img_name, 0, 0);

					if ($uploadResponse['hata'] == "0") {
						$img_name = $uploadResponse["resim_name"];
					} else {
						$json["mesaj"] .= $uploadResponse["hata"];
					}
				}

				$veri = [
					'baslik' => $baslik,
					'link' => $link,
					'img'	=> $img_name,
					'sira' => $sira
				];

				if ($this->db->insert('banner', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('banner'));
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
			'sayfa_adi' => 'banner/ekle',
			'sayfa_title' => 'Banner Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{

		$banner = $this->db->get_where("banner", array("id" => $id))->row();

		if (!$banner) {
			redirect(admin_url("banner"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$baslik 			= post('baslik');
			$sira 				= post('sira');
			$link 				= post('link');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[3]');


			$validat = $this->form_validation->run();

			if ($validat) {


				$img_name = "";
				$uploadResponse = array();
				if (strlen($_FILES['img']['name']) > 2) {
					$img_name = rastgele_sifre(10);
					$uploadResponse =  resim_yukle("img", "public/uploads/banner/", $img_name, 0, 0);

					if ($uploadResponse['hata'] == "0") {

						$img_yol = 'public/uploads/banner/' . $banner->img;
						if (is_file($img_yol)) {
							unlink($img_yol);
						}


						$img_name = $uploadResponse["resim_name"];
					} else {
						$json["mesaj"] .= $uploadResponse["hata"];
					}
				} else {
					$img_name = $banner->img;
				}


				if ($json["mesaj"] == '') {

					$veri = [
						'baslik' => $baslik,
						'link' => $link,
						'img'	=> $img_name,
						'sira' => $sira
					];

					$this->db->where('id', $id);
					if ($this->db->update('banner', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('banner'));
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
			'row'	=> $banner,
			'sayfa_adi' => 'banner/duzenle',
			'sayfa_title' => 'Banner Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
}
