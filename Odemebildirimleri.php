<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Odemebildirimleri extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		admin_oturum_kontrol();
	}


	public function index($durum_type)
	{
		$veri = [
			'durum_type' => $durum_type,
			'sayfa_adi' => 'odeme_bildirimleri/list',
			'sayfa_title' => 'Ödeme Bildirimleri - Admin Paneli',
			'siteayar' => siteayar()
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function datatable($durum_type)
	{
		$postData = $this->input->post();
		$data = $this->getEmployees($postData, $durum_type);

		echo json_encode($data);
	}

	function getEmployees($postData = null, $durum_type)
	{

		$response = array();


		$draw = $postData['draw'];
		$start = $postData['start'];
		$rowperpage = $postData['length']; // Rows display per page
		$columnIndex = $postData['order'][0]['column']; // Column index
		$columnName = $postData['columns'][$columnIndex]['data']; // Column name
		$columnSortOrder = $postData['order'][0]['dir']; // asc or desc
		$searchValue = $postData['search']['value']; // Search value




		$searchQuery = "";
		if ($searchValue != '') {
			$searchQuery = " 
			(odeme_bildirimleri.isim_soyisim like '%" . $searchValue . "%' 
			or odeme_bildirimleri.eposta like '%" . $searchValue . "%'
			or odeme_bildirimleri.telefon like '%" . $searchValue . "%'
			or odeme_bildirimleri.ip like '%" . $searchValue . "%'
			)";
		}



		if ($durum_type == "inceleniyor") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " odeme_bildirimleri.durum='inceleniyor' ";
		} else if ($durum_type == "odeme_basarili") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " odeme_bildirimleri.durum='odeme_basarili' ";
		} else if ($durum_type == "iptal_edildi") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " odeme_bildirimleri.durum='iptal_edildi' ";
		}


		$this->db->select('count(*) as allcount');
		$records = $this->db->get('odeme_bildirimleri')->result();
		$totalRecords = $records[0]->allcount;

		$this->db->select('count(*) as allcount');
		if ($searchQuery != '')
			$this->db->where($searchQuery);
		$records = $this->db->get('odeme_bildirimleri')->result();
		$totalRecordwithFilter = $records[0]->allcount;


		$this->db->select('*');
		if ($searchQuery != '')
			$this->db->where($searchQuery);
		$this->db->order_by($columnName, $columnSortOrder);
		$this->db->limit($rowperpage, $start);
		$records = $this->db->get('odeme_bildirimleri')->result();

		$data = array();

		foreach ($records as $row) {


			$banka_row = $this->db->get_where("banka_hesaplari", array("id" => $row->banka_id))->row();
			$siparis_row = $this->db->get_where("siparisler", array("id" => $row->siparis_no))->row();

		

			$durum_html = '';
			if ($row->durum == "inceleniyor") {
				$durum_html = '<span class="badge badge-primary">İnceleniyor</span>';
			} else if ($row->durum == "odeme_basarili") {
				$durum_html = '<span class="badge badge-success">Ödeme Başarılı</span>';
			} else if ($row->durum == "iptal_edildi") {
				$durum_html = '<span class="badge badge-danger">Sipariş İptal</span>';
			}

			$data[] = array(
				"id" 			=> $row->id,
				"isim_soyisim" 	=> $row->isim_soyisim,
				"siparis_tutar"	=> '<span class="h5 text-success">' . (isset($siparis_row->tutar) ? $siparis_row->tutar : '[SİLİNMİŞ]') . '₺</span>',
				"odenen_ucret"	=> '<span class="h5 text-success">' . $row->gonderilen_toplam_tutar . '₺</span>',
				"banka"			=> isset($banka_row->banka_ad) ? $banka_row->banka_ad : '',
				"durum" 		=> $durum_html,
				"tarih" 		=> dmyhi($row->tarih),
				"islem" 		=> '
					<a href="' . admin_url('odemebildirimleri/no/' . $row->id) . '" class="btn btn-primary btn-sm">
						<i class="fa fa-eye"></i>
					</a>
					<a href="' . admin_url('odemebildirimleri/sil/' . $row->id) . '" id="' . $row->id . '"  class="sil-btn btn btn-danger btn-sm sor-sil">
                        <i class="fa fa-trash"></i>
                    </a>
                    
				'
			);

		
		}

		## Response
		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordwithFilter,
			"aaData" => $data
		);

		return $response;
	}



	public function sil($id)
	{
		$this->db->delete("odeme_bildirimleri", array("id" => $id));

	}


	public function no($id)
	{


		$odeme = $this->db->get_where("odeme_bildirimleri", array("id" => $id))->row();
		if (!$odeme) {
			redirect(admin_url("odemebildirimleri"));
		}

		if ($_POST) {

			$odeme_durum 			= html_sil(post("odeme_durum"));

			$dizi = array(
				"durum" => $odeme_durum,
			);

			$this->db->where(array("id" => $id));
			$this->db->update("odeme_bildirimleri", $dizi);

			
			if (isset($_POST['kaydetvesiparisegit'])) {
				redirect(admin_url("siparisler/goruntule/" . $odeme->siparis_no));
			} else {
				redirect(admin_url("odemebildirimleri/no/" . $odeme->id));
			}
		}


		$siparis = $this->db->get_where('siparisler', array('id' => $odeme->siparis_no))->row();

		$veri = [
			'odeme' => $odeme,
			'siparis' => $siparis,
			'sayfa_adi' => 'odeme_bildirimleri/goruntule',
			'sayfa_title' => '#' . $id . ' numaralı ödeme bildirimi  - Admin Paneli',
			'siteayar' => siteayar()
		];
		$this->load->view('yonetim/index', $veri);
	}
}
