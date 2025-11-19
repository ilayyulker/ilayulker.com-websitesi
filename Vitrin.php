<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Vitrin extends CI_Controller
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
		$vitrinler = $this->db->get('u_vitrin');

		$veri = [
			'vitrinler' => $vitrinler,
			'sayfa_adi' => 'vitrin/list',
			'sayfa_title' => 'Vitrin - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{
		$this->db->delete('u_vitrin', array('id' => $id));
		redirect(admin_url('vitrin'));
	}

	public function ekle()
	{

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];

			$baslik 			= post('baslik');
			$tip 				= post('tip');
			$urun_limiti 		= post('urun_limiti');
			$sira 				= post('sira');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('tip', 'Tip', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('urun_limiti', 'Ürün Sınırı', 'trim|required|min[1]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[1]');

			$validat = $this->form_validation->run();

			if ($validat) {

				$veri = [
					'baslik' => $baslik,
					'tip' => $tip,
					'urun_limiti' => $urun_limiti,
					'sira' => $sira
				];

				if ($this->db->insert('u_vitrin', $veri)) {
					$json["durum"] = 'success';
					$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Eklendi! </div>' . meta_refr(admin_url('vitrin'));
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
			'sayfa_adi' => 'vitrin/ekle',
			'sayfa_title' => 'Vitrin Ekle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function duzenle($id)
	{
		$vitrin = $this->db->get_where("u_vitrin", array("id" => $id))->row();

		if (!$vitrin) {
			redirect(admin_url("vitrin"));
		}

		if ($_POST) {

			$json = ['durum' => 'error', 'mesaj'   => ''];


			$baslik 			= post('baslik');
			$tip 				= post('tip');
			$urun_limiti 		= post('urun_limiti');
			$sira 				= post('sira');

			$this->form_validation->set_rules('baslik', 'Başlık', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('tip', 'Tip', 'trim|required|min_length[3]');
			$this->form_validation->set_rules('urun_limiti', 'Ürün Sınırı', 'trim|required|min[1]');
			$this->form_validation->set_rules('sira', 'Sıra', 'trim|required|min[1]');


			$validat = $this->form_validation->run();

			if ($validat) {

				if ($json["mesaj"] == '') {


					$veri = [
						'baslik' => $baslik,
						'tip' => $tip,
						'urun_limiti' => $urun_limiti,
						'sira' => $sira,
					];

					$this->db->where('id', $id);
					if ($this->db->update('u_vitrin', $veri)) {
						$json["durum"] = 'success';
						$json["mesaj"] =  '<div class="alert alert-success"> Başarıyla Güncellendi! </div>' . meta_refr(admin_url('vitrin'));
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
			'row'	=> $vitrin,
			'sayfa_adi' => 'vitrin/duzenle',
			'sayfa_title' => 'Vitrin Düzenle - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}



	public function vitrin_urunler($vitrin_id)
	{
		$vitrin = $this->db->get_where('u_vitrin', ['id' => $vitrin_id]);

		if ($vitrin->num_rows() == 0) {
			redirect(admin_url('vitrin'));
		}

		$vitrin_urunler = $this->db->order_by('sira', 'asc')->get_where('u_vitrin_urunler', ['vitrin_id' => $vitrin_id]);

		$veri = [
			'vitrin' => $vitrin->row(),
			'vitrin_urunler' => $vitrin_urunler,
			'sayfa_adi' => 'vitrin/vitrin_urunler_list',
			'sayfa_title' => 'Vitrin Ürünleri - Admin Paneli'
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function vitrin_urunler_sira_duzenle($vitrin_id)
	{

		$vitrin = $this->db->get_where('u_vitrin', ['id' => $vitrin_id]);

		if ($vitrin->num_rows() == 0) {
			redirect(admin_url('vitrin'));
		}

		$vitrin_urunler = $this->db->get_where('u_vitrin_urunler', ['vitrin_id' => $vitrin_id]);

		$sira_array = $_POST['sira'];
		foreach ($sira_array as $post_urun_id => $post_sira) {

			$urun = $this->db->get_where('urunler', ['urun_id' => $post_urun_id])->row();
			if (!isset($urun->ad))
				continue;

			$this->db->where(['vitrin_id' => $vitrin_id, 'urun_id' => $post_urun_id]);
			$this->db->update('u_vitrin_urunler', ['sira' => $post_sira]);
		}


		redirect(admin_url('vitrin/vitrin_urunler/' . $vitrin_id));
	}

	public function vitrin_urun_sil($vitrin_id, $urun_id)
	{
		$this->db->delete('u_vitrin_urunler', ['vitrin_id' => $vitrin_id, 'urun_id' => $urun_id]);
		redirect(admin_url('vitrin/vitrin_urunler/' . $vitrin_id));
	}



	public function vitrin_urunler_datatable($vitrin_id)
	{
		// POST data
		$postData = $this->input->post();


		$draw = $postData['draw'];
		$start = $postData['start'];
		$rowperpage = $postData['length']; // Rows display per page
		$columnIndex = $postData['order'][0]['column']; // Column index
		$columnName = $postData['columns'][$columnIndex]['data']; // Column name
		$columnSortOrder = $postData['order'][0]['dir']; // asc or desc
		$searchValue = $postData['search']['value']; // Search value


		$searchQuery = "";
		if ($searchValue != '') {
			$searchQuery = " (urunler.urun_kodu like '%" . $searchValue . "%' or urunler.ad like '%" . $searchValue . "%' or urunler.fiyat like '%" . $searchValue . "%' ) ";
		}


		## Total number of records without filtering
		$this->db->select('count(*) as allcount');
		$records = $this->db->get('urunler')->result();
		$totalRecords = $records[0]->allcount;

		## Total number of record with filtering
		$this->db->select('count(*) as allcount');
		if ($searchQuery != '')
			$this->db->where($searchQuery);
		$records = $this->db->get('urunler')->result();
		$totalRecordwithFilter = $records[0]->allcount;

		## Fetch records
		$this->db->select('*');
		if ($searchQuery != '')
			$this->db->where($searchQuery);
		$this->db->order_by($columnName, $columnSortOrder);
		$this->db->limit($rowperpage, $start);
		$records = $this->db->get('urunler')->result();

		$data = array();

		foreach ($records as $urun) {

			$is_vitrin = 0;
			$vitrin_ekle_html = '';

			$vitrin_sor = $this->db->get_where('u_vitrin_urunler', ['vitrin_id' => $vitrin_id, 'urun_id' => $urun->urun_id]);
			if ($vitrin_sor->num_rows() > 0) {
				$is_vitrin = 1;

				$vitrin_ekle_html = '
					
					<a href="#" data-vitrinid="' . $vitrin_id . '" data-urunid="' . $urun->urun_id . '" class="btn btn-success btn-xs ">
						<i class="fa fa-check"></i> Sıra: ' . $vitrin_sor->row()->sira . ' Ürün Vitrinde
					</a>

				';
			} else {
				$vitrin_ekle_html = '
					<a href="#" data-vitrinid="' . $vitrin_id . '" data-urunid="' . $urun->urun_id . '" class="vitrine_urun_ekle btn btn-primary btn-xs ">
						<i class="fa fa-plus"></i> Vitrine Ekle 
					</a>
				';
			}

			$urun_resimleri = (array)json_decode($urun->resimler);
			$urun_resmi = isset($urun_resimleri[0]) ? $urun_resimleri[0] : "";
			$kategori = (object) $this->Market_model->kategori_getir($urun->kategori_id);




			$data[] = array(
				"urun_id" => $urun->urun_id,
				"ad" => '<img  width="25px" class="b-r-8" src="' . uploads_folder('urunler/' . $urun_resmi) . '" alt="" class="avatar-sm"> ' . $urun->ad,
				"urun_kodu" => $urun->urun_kodu,
				"kategori" => (isset($kategori->baslik) ?  $kategori->baslik : "-"),
				"fiyat" => number_format($urun->fiyat, 2, '.', ',')  . '₺',
				"aktif" =>  $urun->aktif == "1" ? '
					<span class="badge badge-success font-size-12">Aktif</span>' :
					'<span class="badge badge-danger font-size-12">Kapalı</span>',
				"islem" => $vitrin_ekle_html


			);
		}

		## Response
		$data = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordwithFilter,
			"aaData" => $data
		);

		echo json_encode($data);
	}

	public function vitrine_urun_ekle($vitrin_id, $yeni_urun_id)
	{

		$vitrin = $this->db->get_where('u_vitrin', ['id' => $vitrin_id]);

		if ($vitrin->num_rows() == 0) {
			redirect(admin_url('vitrin'));
		}

		/* Vitrinde daha önce silinen ürün varsa sil */
		$__kontrol_vitrin_urunler = $this->db->get_where('u_vitrin_urunler', ['vitrin_id' => $vitrin_id]);
		foreach ($__kontrol_vitrin_urunler->result() as $v_urun) {
			$urun = $this->db->get_where('urunler', ['urun_id' => $v_urun->urun_id])->row();
			if (!isset($urun->ad)) {
				$this->db->delete('u_vitrin_urunler', ['id' => $v_urun->id]);
			}
		}

		/* Yeni eklenecek ürün var mı kontrol et */
		$urun = $this->db->get_where('urunler', ['urun_id' => $yeni_urun_id])->row();
		if (!isset($urun->ad)) {
			return 0;
		}

		$vitrin_urunler = $this->db->order_by('sira', 'asc')->get_where('u_vitrin_urunler', ['vitrin_id' => $vitrin_id]);
		$sira_dizi 		= array();
		$sira_dizi[1] 	= $yeni_urun_id;

		foreach ($vitrin_urunler->result() as $__sira => $__urun) {
			$sira_dizi[($__sira + 2)] = $__urun->urun_id;
		}

		foreach ($sira_dizi as $yeni_sira => $__urun_id) {

			$for_urun = $this->db->get_where('u_vitrin_urunler', ['vitrin_id' => $vitrin_id, 'urun_id' => $__urun_id]);
			if ($for_urun->num_rows() == 0) {
				$this->db->insert('u_vitrin_urunler', ['vitrin_id' => $vitrin_id, 'urun_id' => $__urun_id, 'sira' => $yeni_sira]);
			} else {
				$this->db->where(['vitrin_id' => $vitrin_id, 'urun_id' => $__urun_id]);
				$this->db->update('u_vitrin_urunler', ['sira' => $yeni_sira]);
			}
		}
	}
}
