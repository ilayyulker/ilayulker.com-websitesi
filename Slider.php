<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Slider extends CI_Controller
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
		$slider = $this->db->get('slider');

		$veri = [
			'slider' => $slider,
			'sayfa_adi' => 'slider/list',
			'sayfa_title' => 'Sliderlar - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{
		$slider = $this->db->get_where("slider", array("id" => $id))->row();

		if (!$slider) {
			redirect(admin_url("slider"));
		}

		$img_yol = 'public/uploads/slider/' . $slider->img;
		if (is_file($img_yol)) {
			unlink($img_yol);
		}

		$this->db->delete('slider', array('id' => $id));
		redirect(admin_url('slider'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$baslik = post('baslik', 1);

			$slider_yazi 	= post('slider_yazi') == "on" ? 1 : 0;
			$slider_button 	= post('slider_button') == "on" ? 1 : 0;

			$slider_yazi_baslik 			= post('slider_yazi_baslik');
			$slider_yazi_aciklama 			= post('slider_yazi_aciklama');
			$slider_button_yazi 			= post('slider_button_yazi');
			$slider_button_link 			= post('slider_button_link');

			$sira 	= post('sira') < 1 ? 0 : post('sira');



			$img_name = "";
			$uploadResponse = array();
			if (strlen($_FILES['img']['name']) > 2) {
				$img_name = rastgele_sifre(10);
				$uploadResponse =  resim_yukle("img", "public/uploads/slider/", $img_name, 0, 0);

				if ($uploadResponse['hata'] == "0") {
					$img_name = $uploadResponse["resim_name"];
				} else {
					$json["mesaj"] .= $uploadResponse["hata"];
				}
			}



			if ($json["mesaj"] == '') {

				$veri = [
					'img' 					=> $img_name,
					'baslik' 				=> $baslik,
					'slider_yazi' 			=> $slider_yazi,
					'slider_button' 		=> $slider_button,
					'slider_yazi_baslik' 	=> $slider_yazi_baslik,
					'slider_yazi_aciklama' 	=> $slider_yazi_aciklama,
					'slider_button_yazi' 	=> $slider_button_yazi,
					'slider_button_link' 	=> $slider_button_link,
					'sira' 					=> $sira
				];

				if ($this->db->insert('slider', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('slider'));
				}
			}

			if ($json["durum"] == "error") {
				$json["mesaj"] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
			}

			echo json_encode($json);
			exit;
		}

		$veri = [
			'sayfa_adi' => 'slider/ekle',
			'sayfa_title' => 'Slider Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{

		$slider = $this->db->get_where("slider", array("id" => $id))->row();

		if (!$slider) {
			redirect(admin_url("slider"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$baslik = post('baslik', 1);
			$sira 	= post('sira') < 1 ? 0 : post('sira');

			$slider_yazi 	= post('slider_yazi') == "on" ? 1 : 0;
			$slider_button 	= post('slider_button') == "on" ? 1 : 0;

			$slider_yazi_baslik 			= post('slider_yazi_baslik');
			$slider_yazi_aciklama 			= post('slider_yazi_aciklama');
			$slider_button_yazi 			= post('slider_button_yazi');
			$slider_button_link 			= post('slider_button_link');

			$img_name = "";
			$uploadResponse = array();
			if (strlen($_FILES['img']['name']) > 2) {
				$img_name = rastgele_sifre(10);
				$uploadResponse =  resim_yukle("img", "public/uploads/slider/", $img_name, 0, 0);

				if ($uploadResponse['hata'] == "0") {

					$img_yol = 'public/uploads/slider/' . $slider->img;
					if (is_file($img_yol)) {
						unlink($img_yol);
					}

					$img_name = $uploadResponse["resim_name"];
				} else {
					$json["mesaj"] .= $uploadResponse["hata"];
				}
			} else {
				$img_name = $slider->img;
			}

			if ($json["mesaj"] == '') {

				$veri = [
					'img' 					=> $img_name,
					'baslik' 				=> $baslik,
					'slider_yazi' 			=> $slider_yazi,
					'slider_button' 		=> $slider_button,
					'slider_yazi_baslik' 	=> $slider_yazi_baslik,
					'slider_yazi_aciklama' 	=> $slider_yazi_aciklama,
					'slider_button_yazi' 	=> $slider_button_yazi,
					'slider_button_link' 	=> $slider_button_link,
					'sira' 					=> $sira
				];

				$this->db->where('id', $id);
				if ($this->db->update('slider', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi!</div>' . meta_refr(admin_url('slider'));
				}
			}


			if ($json["durum"] == "error") {
				$json["mesaj"] = '<div class="alert alert-danger">' . $json['mesaj'] . '</div> ';
			}

			echo json_encode($json);
			exit;
		}


		$veri = [
			'row'	=> $slider,
			'sayfa_adi' => 'slider/duzenle',
			'sayfa_title' => 'Slider Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
}
