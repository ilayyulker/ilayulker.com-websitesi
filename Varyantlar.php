<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Varyantlar extends CI_Controller
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
		$varyantlar = $this->db->order_by('id', 'desc')->get('u_varyantlar');

		$veri = [
			'varyantlar' => $varyantlar,
			'sayfa_adi' => 'varyantlar/list',
			'sayfa_title' => 'Varyantlar - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{
		$this->db->delete('u_varyantlar', array('id' => $id));
		$this->db->delete('u_varyant_ozellikler', array('varyant_id' => $id));
		redirect(admin_url('varyantlar'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$baslik 			= post('baslik');
			$tip 				= post('tip');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[2]');

			$validat = $this->form_validation->run();

			if ($validat) {

				$veri = [
					'baslik' => $baslik,
					'type' => $tip,
				];

				if ($this->db->insert('u_varyantlar', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('varyantlar'));
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
			'sayfa_adi' => 'varyantlar/ekle',
			'sayfa_title' => 'Varyant Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{
		$varyant = $this->db->get_where("u_varyantlar", array("id" => $id))->row();

		if (!$varyant) {
			redirect(admin_url("varyant"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];


			$baslik 			= post('baslik');
			$tip 			= post('tip');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');


			$validat = $this->form_validation->run();

			if ($validat) {

				if ($json["mesaj"] == '') {


					$veri = [
						'baslik' => $baslik,
						'type' => $tip,
					];

					$this->db->where('id', $id);
					if ($this->db->update('u_varyantlar', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('varyantlar'));
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
			'row'	=> $varyant,
			'sayfa_adi' => 'varyantlar/duzenle',
			'sayfa_title' => 'Varyant Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function varyant_ozellikler($varyant_id)
	{
		$varyant = $this->db->get_where('u_varyantlar', ['id' => $varyant_id]);

		if ($varyant->num_rows() == 0) {
			redirect(admin_url('varyantlar'));
		}

		$varyant_ozellikler = $this->db->order_by('id', 'desc')->get_where('u_varyant_ozellikler', ['varyant_id' => $varyant_id]);


		$veri = [
			'varyant' => $varyant->row(),
			'varyant_ozellikler' => $varyant_ozellikler,
			'sayfa_adi' => 'varyantlar/varyant_ozellikler_list',
			'sayfa_title' => 'Varyant Özellikleri - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function varyant_ozellik_sil($varyant_id, $id)
	{
		$this->db->delete('u_varyant_ozellikler', ['varyant_id' => $varyant_id, 'id' => $id]);
		redirect(admin_url('varyantlar/varyant_ozellikler/' . $varyant_id));
	}

	public function  varyant_ozellik_ekle($varyant_id)
	{
		$varyant = $this->db->get_where('u_varyantlar', ['id' => $varyant_id]);

		if ($varyant->num_rows() == 0) {
			redirect(admin_url('varyantlar'));
		}

		$vozellik_baslik = post('baslik');

		$this->db->insert('u_varyant_ozellikler', ['varyant_id' => $varyant_id, 'baslik' => $vozellik_baslik]);

		redirect(admin_url('varyantlar/varyant_ozellikler/'.$varyant_id.'?islem=ekle'));
	}

	
	public function  varyant_ozellik_duzenle($varyant_id, $ozellik_id)
	{
		$varyant = $this->db->get_where('u_varyant_ozellikler', ['id' => $ozellik_id]);

		if ($varyant->num_rows() == 0) {
			redirect(admin_url('varyantlar'));
		}

		$vozellik_baslik = post('baslik');

		$this->db->where(['id' => $ozellik_id]);
		$this->db->update('u_varyant_ozellikler', ['baslik' => $vozellik_baslik]);

		redirect(admin_url('varyantlar/varyant_ozellikler/'.$varyant_id));
	}
}
