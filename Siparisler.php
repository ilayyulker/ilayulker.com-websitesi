<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Siparisler extends CI_Controller
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
			'sayfa_adi' => 'siparisler/list',
			'sayfa_title' => 'Siparişler - Admin Paneli',
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
			(siparisler.teslimat_adresi like '%" . $searchValue . "%' 
			or siparisler.sipariskey like '%" . $searchValue . "%'
			or siparisler.sepet like '%" . $searchValue . "%'
			)";
		}


		if ($durum_type == "odeme_bekliyor") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='odeme_bekliyor' ";
		} else if ($durum_type == "odeme_basarili") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='odeme_basarili' ";
		} else if ($durum_type == "iptal_edildi") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='iptal_edildi' ";
		} else if ($durum_type == "hazirlaniyor") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='hazirlaniyor' ";
		} else if ($durum_type == "teslim_edildi") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='teslim_edildi' ";
		} else if ($durum_type == "kargolandi") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='kargolandi' ";
		} else if ($durum_type == "kontrol_ediliyor") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='kontrol_ediliyor' ";
		} else if ($durum_type == "stok_yok") {
			if ($searchQuery != "") {
				$searchQuery .= ' and ';
			}
			$searchQuery .= " siparisler.siparis_durum='stok_yok' ";
		}


		$this->db->select('count(*) as allcount');
		$records = $this->db->get('siparisler')->result();
		$totalRecords = $records[0]->allcount;

		$this->db->select('count(*) as allcount');
		if ($searchQuery != '')
			$this->db->where($searchQuery);
		$records = $this->db->get('siparisler')->result();
		$totalRecordwithFilter = $records[0]->allcount;


		$this->db->select('*');
		if ($searchQuery != '')
			$this->db->where($searchQuery);
		$this->db->order_by($columnName, $columnSortOrder);
		$this->db->limit($rowperpage, $start);
		$records = $this->db->get('siparisler')->result();

		$data = array();

		foreach ($records as $row) {

			$isimsoyisim = "";

			$_teslimat_adresi = json_decode($row->teslimat_adresi);
			$isimsoyisim = $_teslimat_adresi->isim . ' ' . $_teslimat_adresi->soyisim;


			$method_html = '';
			if ($row->method == "kredi_karti") {
				$method_html =  '<span class="badge badge-dark">Kredi Kartı</span>';
			} else if ($row->method == "havale_eft") {
				$method_html =  '<span class="badge badge-dark">Havale & Eft</span>';
			} else if ($row->method == "mobil_odeme") {
				$method_html =  '<span class="badge badge-dark">Mobil Ödeme</span>';
			} else if ($row->method == "kapida_odeme") {
				$method_html =  '<span class="badge badge-dark">Kapıda Ödeme</span>';
			}

			$siparis_durum_html = '';
			if ($row->siparis_durum == "odeme_bekliyor") {
				$siparis_durum_html = '<span class="badge badge-primary">Ödeme Bekliyor</span>';
			} else if ($row->siparis_durum == "odeme_basarili") {
				$siparis_durum_html = '<span class="badge badge-success">Ödeme Başarılı</span>';
			} else if ($row->siparis_durum == "iptal_edildi") {
				$siparis_durum_html = '<span class="badge badge-danger">Sipariş İptal</span>';
			} else if ($row->siparis_durum == "hazirlaniyor") {
				$siparis_durum_html = '<span class="badge badge-info">Hazırlanıyor</span>';
			} else if ($row->siparis_durum == "teslim_edildi") {
				$siparis_durum_html = '<span class="badge badge-dark"><i class="fa fa-check text-success"></i> Teslim Edildi</span>';
			} else if ($row->siparis_durum == "kargolandi") {
				$siparis_durum_html = '<span class="badge badge-info">Kargolandı</span>';
			} else if ($row->siparis_durum == 'kontrol_ediliyor') {
				$siparis_durum_html = '<span class="badge badge-info">Kontrol Ediliyor</span>';
			} else if ($row->siparis_durum == 'stok_yok') {
				$siparis_durum_html = '<span class="badge badge-dark">Stokta Yok</span>';
			}

			$data[] = array(
				"id" 			=> $row->id,
				"siparis_no" 	=> $row->sipariskey,
				"musteri_bilgi"	=> '<strong>' . $isimsoyisim . '</strong>',
				"toplam_tutar"	=> '<span class="h5 text-success">' . number_format($row->tutar, 2, '.', ',') . '₺</span>',
				"siparis_tarihi" => dmyhi($row->tarih),
				"method"	=> $method_html,
				"siparis_durumu" => $siparis_durum_html,
				"islem" => '
				<a href="' . admin_url('siparisler/goruntule/' . $row->id) . '" class="btn btn-primary btn-sm">
						<i class="fa fa-eye"></i>
				</a>
				<a href="' . admin_url('siparisler/sil/' . $row->id) . '" id="' . $row->id . '"  class="sil-btn btn btn-danger btn-sm sor-sil">
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


	public function goruntule($id)
	{
		$siparis = $this->db->get_where("siparisler", array("id" => $id));

		if (!$siparis) {
			redirect(admin_url("siparisler"));
		}
		$siparis = $siparis->row();

		if ($_POST) {
			$kargo_bilgi = (array)json_decode($siparis->kargo_bilgi);

			$siparis_durum 		= html_sil(post("siparis_durum"));
			$magaza_mesaji 		= html_sil(post("magaza_mesaji"));

			$kargo_ad 				= html_sil(post("kargo_ad"));
			$kargo_takip_kodu 		= html_sil(post("kargo_takip_kodu"));
			$kargo_takip_linki 		= html_sil(post("kargo_takip_linki"));

			$kargo_bilgi["kargo_ad"]			 = $kargo_ad;
			$kargo_bilgi["kargo_takip_kodu"]	 = $kargo_takip_kodu;
			$kargo_bilgi["kargo_takip_linki"] 	 = $kargo_takip_linki;

			$dizi = array(
				"kargo_bilgi" => json_encode($kargo_bilgi),
				"siparis_durum" => $siparis_durum,
				"magaza_mesaji" => $magaza_mesaji
			);
			$this->db->where(array("id" => $id));
			$this->db->update("siparisler", $dizi);

			$this->load->model('Bildir_model');
			$digital_urun_teslim   	= post('digital_urun_teslim') == "on" ? 1 : 0;
			$mail_bildirim   		= post('mail_bildirim') == "on" ? 1 : 0;
			$sms_bildirim   		= post('sms_bildirim') == "on" ? 1 : 0;
			if ($sms_bildirim) {
				$this->Bildir_model->sms_siparis_durumu_bildir($siparis->id);
			}
			if ($mail_bildirim) {
				$this->Bildir_model->mail_siparis_durumu_bildir($siparis->id);
			}

			if ($digital_urun_teslim) {

				$_sepet                     = json_decode($siparis->sepet);
				$_urunler                   = (array)json_decode($siparis->urun_verileri);
				$_urun_digital				= (array)json_decode($siparis->urun_digital);


				$verilen_veriler = array();
				foreach ($_urun_digital as $f_urun_id => $f_digirows) {
					$verilen_veriler[$f_urun_id][] = "_-_-_taysiweb_-_-__";
					foreach ($f_digirows as $f_index => $f_digirow) {
						if ($f_digirow->veri != "stok_yok") {
							$verilen_veriler[$f_urun_id][] = $f_digirow->veri;
						}
					}
				}

				foreach ($_urun_digital as $f_urun_id => $f_digirows) {
					foreach ($f_digirows as $f_index => $f_digirow) {
						if ($f_digirow->veri == "stok_yok") {
							echo $f_index;

							$db_veri = $this->db
								->order_by('RAND()')
								->limit(1)
								->where_not_in('veri', $verilen_veriler[$f_urun_id])
								->where(['urun_id' => $f_urun_id, 'durum' => 0, 'siparis_id' => 0])
								->get('urun_digital')
								->row();

							if (isset($db_veri)) {

								$_urun_digital[$f_urun_id][$f_index] = (object)array(
									'veri_id' =>  $db_veri->id,
									'veri' => $db_veri->veri
								);

								$this->db->where('id', $db_veri->id);
								$this->db->update('urun_digital', ['siparis_id' => $siparis->id, 'durum' => 1]);
							} else {
								$urun_veri[$f_urun_id][] = array(
									'veri_id' 	=> 0,
									'veri' 		=> "stok_yok",
								);
							}
						}
					}
				}

				$update_siparis_array = array(
					'siparis_durum' => 'odeme_basarili',
					'urun_digital' => json_encode($_urun_digital)
				);

				$this->db->where(array('id' => $siparis->id));
				$this->db->update('siparisler', $update_siparis_array);
			}

			redirect(admin_url("siparisler/goruntule/" . $siparis->id));
		}

		$veri = [
			'siparis' => $siparis,
			'sayfa_adi' => 'siparisler/goruntule',
			'sayfa_title' => $siparis->sipariskey . ' takip numaralı sipariş  - Admin Paneli',
			'siteayar' => siteayar()
		];
		$this->load->view('yonetim/index', $veri);
	}

	public function sil($id)
	{
		$this->db->delete("siparisler", array("id" => $id));
	}
}
