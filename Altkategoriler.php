<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Altkategoriler extends CI_Controller
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
		$altkategoriler = $this->db->get('u_alt_kategoriler');

		$veri = [
			'altkategoriler' => $altkategoriler,
			'sayfa_adi' => 'altkategoriler/list',
			'sayfa_title' => 'Alt Kategoriler - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id){
		$this->db->delete('u_alt_kategoriler', array('id' => $id));
		redirect(admin_url('altkategoriler'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$kategori_id			= post('kategori_id');
	
			$baslik 			= post('baslik');
			$aciklama 			= post('aciklama', true);
			$sira 				= post('sira');
			$seo_title 			= post('seo_title');
			$seo_description 	= post('seo_description');

			$this->form_validation->set_rules('kategori_id', 'Kategori', 'trim|required|callback_kategori_exists',);
			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[1]');

            $validat = $this->form_validation->run();

			if ($validat) {
					$slug = url_title(convert_accented_characters($baslik), '-', TRUE);
					$veri = [
						'kategori_id' => $kategori_id,
						'seo' => $slug,
						'baslik' => $baslik,
						'aciklama' => $aciklama,
						'sira' => $sira,
						'seo_title' => $seo_title,
						'seo_description' => $seo_description,
					];
					if ($this->db->insert('u_alt_kategoriler', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('altkategoriler'));
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
			'sayfa_adi' => 'altkategoriler/ekle',
			'sayfa_title' => 'Alt Kategori Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	function kategori_exists($id) {
		$query = $this->db->get_where('u_kategoriler', array('id' => $id));
		if($query->num_rows() > 0){
			return true;
		}
		$this->form_validation->set_message('kategori_exists', 'Kategori bulunamadı!');
		return false;
	}

	public function duzenle($id)
	{

		$kategori = $this->db->get_where("u_alt_kategoriler", array("id" => $id))->row();

		if (!$kategori) {
			redirect(admin_url("kategoriler"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$kategori_id			= post('kategori_id');
			$baslik 			= post('baslik');
			$aciklama 			= post('aciklama', true);
			$sira 				= post('sira');
			$seo_title 			= post('seo_title');
			$seo_description 	= post('seo_description');
			

			$this->form_validation->set_rules('kategori_id', 'Kategori', 'trim|required|callback_kategori_exists',);
			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[3]');

			$validat = $this->form_validation->run();

			if ($validat) {
				
				if ($json["mesaj"] == '') {

					$slug = url_title(convert_accented_characters($baslik), '-', TRUE);

					$veri = [
						'kategori_id' => $kategori_id,
						'seo' => $slug,
						'baslik' => $baslik,
						'aciklama' => $aciklama,
						'sira' => $sira,
						'seo_title' => $seo_title,
						'seo_description' => $seo_description,
					];

					$this->db->where('id', $id);
					if ($this->db->update('u_alt_kategoriler', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('altkategoriler'));
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
			'row'	=> $kategori,
			'sayfa_adi' => 'altkategoriler/duzenle',
			'sayfa_title' => 'Alt Kategori Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
}
