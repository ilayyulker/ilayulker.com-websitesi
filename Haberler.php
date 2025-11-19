<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Haberler extends CI_Controller
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
		$haberler = $this->db->order_by('id', 'desc')->get('haberler');

		$veri = [
			'haberler' => $haberler,
			'sayfa_adi' => 'haberler/list',
			'sayfa_title' => 'Haberler - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
	public function sil($id){
		$haber = $this->db->get_where("haberler", array("id" => $id))->row();

		if (!$haber) {
			redirect(admin_url("haberler"));
		}

		$img_yol = 'public/uploads/haberler/'.$haber->img;
		if (is_file($img_yol)) {
			unlink($img_yol);
		}

		$this->db->delete('haberler', array('id' => $id));
		redirect(admin_url('haberler'));
	}
	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];
			$kategori_id 		= post('kategori');
			$baslik 			= post('baslik');
			$icerik 			= post('icerik', true);
			$kisa_aciklama 		= post('kisa_aciklama', true);
			$durum 				= post('durum');
			$meta_title 		= post('meta_title');
			$meta_description 	= post('meta_description');


			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('icerik', 'İçerik', 'trim|required|min_length[10]');
			$this->form_validation->set_rules('durum', 'Durum', 'required|trim');

			$validat = $this->form_validation->run();

			if ($validat) {

				$random_name = rastgele_sifre(10);
			
				$resim_name = '';
				$upload_return = array();
			

				if (strlen($_FILES['img']['name']) > 3) {
					$random_name = rastgele_sifre(10);
					$upload_return 			= resim_yukle("img", "public/uploads/haberler/", $random_name);
					if ($upload_return['hata'] == "0") {
						$resim_name = $upload_return['resim_name'];
					

					} else {
						$json["mesaj"] = $upload_return['hata'];
					}
				} else {
					$upload_return['hata'] == "Resim yüklemeniz gerekmektedir.";
				}



				if ($upload_return['hata'] == "0") {

					$slug = url_title(convert_accented_characters($baslik), '-', TRUE);

						
					$veri = [
						'kategori_id' => $kategori_id,
						'slug' => $slug,
						'baslik' => $baslik,
						'icerik' => $icerik,
						'kisa_aciklama' => $kisa_aciklama,
						'img'	=> $resim_name,
						'durum' => $durum,
						'meta_title' => $meta_title,
						'meta_description' => $meta_description,
						'tarih' => date('Y-m-d H:i:s')
					];

					if ($this->db->insert('haberler', $veri)) {

						$insert_id = $this->db->insert_id();
						$this->db->where('id', $insert_id);
						$this->db->update('haberler', ['slug' => $slug.'-'.($insert_id * 999)]);

						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>'. meta_refr(admin_url('haberler'));
					}
				}else {
					$json["mesaj"] = $upload_return['hata'];
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
			'sayfa_adi' => 'haberler/ekle',
			'sayfa_title' => 'Haber Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
	public function duzenle($id)
	{

		$haber = $this->db->get_where("haberler", array("id" => $id))->row();

		if (!$haber) {
			redirect(admin_url("haberler"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$kategori_id 		= post('kategori');
			$baslik 			= post('baslik');
			$icerik 			= post('icerik', true);
			$kisa_aciklama 		= post('kisa_aciklama', true);
			$durum 				= post('durum');
			$meta_title 		= post('meta_title');
			$meta_description 	= post('meta_description');

			$tarih = date('Y-m-d H:i:s', strtotime(post('tarih')));
		
			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('icerik', 'İçerik', 'trim|required|min_length[10]');
			$this->form_validation->set_rules('durum', 'Durum', 'required|trim');

			$validat = $this->form_validation->run();

			if ($validat) {
				$resim_name = '';
			

				if (strlen($_FILES['img']['name']) > 3) {
					$random_name = rastgele_sifre(10);
					$upload_return 			= resim_yukle("img", "public/uploads/haberler", $random_name);
					if ($upload_return['hata'] == "0") {

						$img_yol = 'public/uploads/haberler/'.$haber->img;
						if (is_file($img_yol)) {
							unlink($img_yol);
						}

						$resim_name = $upload_return['resim_name'];
				
					} else {
						$json["mesaj"] = $upload_return['hata'];
					}
				} else {
					$resim_name = $haber->img;
				}

				
				if ($json["mesaj"] == '') {

					$slug = url_title(convert_accented_characters($baslik), '-', TRUE).'-'.($haber->id * 999);

					$veri = [
						'kategori_id' => $kategori_id,
						'slug' => $slug,
						'baslik' => $baslik,
						'icerik' => $icerik,
						'kisa_aciklama' => $kisa_aciklama,
						'img'	=> $resim_name,
						'durum' => $durum,
						'meta_title' => $meta_title,
						'meta_description' => $meta_description,
						'tarih' => $tarih
					
					];

					$this->db->where('id', $id);
					if ($this->db->update('haberler', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>'.meta_refr(admin_url('haberler')) ;
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
			'row'	=> $haber,
			'sayfa_adi' => 'haberler/duzenle',
			'sayfa_title' => 'Haber Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

    public function kategoriler()
	{
		$kategoriler = $this->db->get('haber_kategoriler');

		$veri = [
			'kategoriler' => $kategoriler,
			'sayfa_adi' => 'haberler/haber_kategoriler/list',
			'sayfa_title' => 'Haber Kategorileri - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function kategoriler_sil($id)
	{
		$this->db->delete('haber_kategoriler', array('id' => $id));
		redirect(admin_url('haberler/kategoriler'));
	}

	public function kategoriler_ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];


			$baslik 			= post('baslik');
			$aciklama 			= post('aciklama', true);
			$meta_title 		= post('meta_title');
			$meta_description 	= post('meta_description');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[1]');

			$validat = $this->form_validation->run();


			if ($validat) {

				if ($json['mesaj'] == '') {
					$slug = url_title(convert_accented_characters($baslik), '-', TRUE);
					$veri = [
						'slug' => $slug,
						'baslik' => $baslik,
						'aciklama' => $aciklama,
						'sira' => $sira,
						'meta_title' => $meta_title,
						'meta_description' => $meta_description,
					];
					if ($this->db->insert('haber_kategoriler', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('haberler/kategoriler'));
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
			'sayfa_adi' => 'haberler/haber_kategoriler/ekle',
			'sayfa_title' => 'Haber Kategorisi Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function kategoriler_duzenle($id)
	{

		$kategori = $this->db->get_where("haber_kategoriler", array("id" => $id))->row();

		if (!$kategori) {
			redirect(admin_url("haberler/kategoriler"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$baslik 			= post('baslik');
			$aciklama 			= post('aciklama', true);
			$sira 				= post('sira');
			$meta_title 		= post('meta_title');
			$meta_description 	= post('meta_description');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[3]');


			$validat = $this->form_validation->run();

			if ($validat) {
				if ($json["mesaj"] == '') {
					if ($json['mesaj'] == '') {
						$slug = url_title(convert_accented_characters($baslik), '-', TRUE);

						$veri = [
							'slug' => $slug,
							'baslik' => $baslik,
							'aciklama' => $aciklama,
							'sira' => $sira,
							'meta_title' => $meta_title,
							'meta_description' => $meta_description,
						];
						$this->db->where('id', $id);
						if ($this->db->update('haber_kategoriler', $veri)) {
							$json["durum"] = 'success';
							$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('haberler/kategoriler'));
						}
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
			'sayfa_adi' => 'haberler/haber_kategoriler/duzenle',
			'sayfa_title' => 'Haber Kategorisi Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}



}
