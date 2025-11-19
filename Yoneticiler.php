<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Yoneticiler extends CI_Controller
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
		$yoneticiler = $this->db->get('yoneticiler');
		$veri = [
			'yoneticiler' => $yoneticiler,
			'sayfa_adi' => 'yoneticiler/list',
			'sayfa_title' => 'Yöneticiler - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}


	public function sil($id)
	{
		$this->db->delete('yoneticiler', array('id' => $id));
		redirect(admin_url('yoneticiler'));
	}

	public function ekle()
	{
		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$adsoyad 			= post('adsoyad');
			$email 				= post('email');
			$durum 				= post('durum');
			$songiris			= date('Y-m-d H:i:s');


			$sifre 				= post('sifre', true);
			$sifre_tekrar 		= post('sifre_tekrar', true);

			if ($sifre != $sifre_tekrar) {
				$json['mesaj'] = 'Şifreler uyuşmuyor!';
				echo json_encode($json);
				exit;
			}

			$sifre = sha1($sifre);

			$this->form_validation->set_rules('adsoyad', 'Ad Soyad', 'trim|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('sifre', 'Şifre', 'trim|required|min_length[6]');
			$this->form_validation->set_rules('durum', 'Durum', 'required|trim');

			$validat = $this->form_validation->run();

			$email_kontrol = $this->db->get_where('yoneticiler', ['email' => $email]);
			if ($email_kontrol->num_rows() > 0) {
				$json['mesaj'] = 'Email zaten kayıtlı!';
				echo json_encode($json);
				exit;
			}

			if ($validat) {

				$veri = [
					'adsoyad' => $adsoyad,
					'email' => $email,
					'sifre' => $sifre,
					'durum' => $durum,
					'songiris' => $songiris
				];

				if ($this->db->insert('yoneticiler', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('yoneticiler'));
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
			'sayfa_adi' => 'yoneticiler/ekle',
			'sayfa_title' => 'Yönetici Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{
		$yonetici = $this->db->get_where("yoneticiler", array("id" => $id))->row();

		if (!$yonetici) {
			redirect(admin_url("yoneticiler"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			

			$adsoyad 			= post('adsoyad');
			$durum 				= post('durum');
			$songiris			= date('Y-m-d H:i:s');
			$email 				= post('email');
			$sifre_degistir 	= post('sifre_degistir_cb') == "on" ? true : false;

			$sifre 				= $yonetici->sifre;
			if ($sifre_degistir) {
				$yeni_sifre = post('yeni_sifre', true);
				$yeni_sifre_tekrar = post('yeni_sifre_tekrar', true);
				if ($yeni_sifre != $yeni_sifre_tekrar) {
					$json['mesaj'] = 'Yeni şifreler uyuşmuyor!';
					echo json_encode($json);
					exit;
				}
				$sifre = sha1($yeni_sifre);
			}


			$this->form_validation->set_rules('adsoyad', 'Ad Soyad', 'trim|required');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('durum', 'Durum', 'required|trim');

			$validat = $this->form_validation->run();

			$email_kontrol = $this->db->query('select * from yoneticiler where email="' . $email . '" and id !=' . admin()->id);
			if ($email_kontrol->num_rows() > 0) {
				$json['mesaj'] = 'Email zaten kayıtlı!';
				echo json_encode($json);
				exit;
			}

			if ($validat) {

				if ($json["mesaj"] == '') {
					$veri = [
						'adsoyad' => $adsoyad,
						'email' => $email,
						'sifre' => $sifre,
						'durum' => $durum,
						'songiris' => $songiris
					];

					$this->db->where('id', $id);
					if ($this->db->update('yoneticiler', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('yoneticiler'));
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
			'row'	=> $yonetici,
			'sayfa_adi' => 'yoneticiler/duzenle',
			'sayfa_title' => 'Yönetici Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}
}
