<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Urun extends CI_Controller
{
	private $_extraData = NULL;
	private $siteayar = NULL;
	private $tasarim_ayar = NULL;
	private $odeme_ayar = NULL;
	private $_Market = NULL;
	private $_Sepet = NULL;


	public function __construct()
	{
		parent::__construct();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');

		$this->siteayar = siteayar();
		$this->tasarim_ayar = tasarim_ayar();
		$this->odeme_ayar = odeme_ayar();
		$this->_Market = $this->Market_model;
		$this->_Sepet = $this->Sepet_model;

		$this->_extraData = array(
			'siteayar'			=> $this->siteayar,
			'tasarim_ayar'		=> $this->tasarim_ayar,
			'odeme_ayar'		=> $this->odeme_ayar,
			'_Market'			=> $this->_Market,
			'_Sepet'			=> $this->_Sepet,
		);
		$this->_extraData = (object)$this->_extraData;
	}

	public function index($kategori_slug, $urun_slug)
	{
		$urun_slug = $urun_slug . '9';

		$urun = (object) $this->Market_model->urun_getir(0, $urun_slug);

		$m_urun = $this->Urun_model;
		$urun = $m_urun->urun($urun->urun_id);


		if (!isset($urun->ad)) redirect("/");

		$veri = [
			'sayfa_adi' => 'urun/urun_goster',
			'sayfa_title' => $urun->ad . ' - ' . siteayar()->site_baslik,

			'siteayar' => siteayar()
		];


		$urun_kategori = $this->db->get_where("u_kategoriler", array('id' => $urun->kategori_id))->row();
		$urun_resimler = ((array)json_decode($urun->resimler));


		$veri["m_urun"] = $m_urun;
		$veri["urun"] = $urun;
		$veri["urun_kategori"] = $urun_kategori;
		$veri["urun_resimler"] = $urun_resimler;
		
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function yorum_ekle($slug)
	{
		$slug = $slug . '9';
		if ($_POST) {
			$json = ['durum' => 'error', 'mesaj'   => ''];

			$urun = $this->db->get_where('urunler', ['seo' => $slug]);
			if ($urun->num_rows() == 0) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Ürün bulunamadı!<br>';
			}

			$yorum = post('yorum');
			$puan = post('puan');

			if (strlen($yorum) < 3 || strlen($yorum) > 500) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Yorum 10 ile 500 karakter arası olmalıdır.<br>';
			}

			if ($puan > 5 || $puan < 0) {
				$json['mesaj'] =  '<i class="fa fa-times"></i> Puan geçersiz!<br>';
			}


			if ($json['mesaj'] == '') {

				$veri = [
					'urun_id' => $urun->row()->urun_id,
					'uye_id' => uye()->id,
					'yorum' => $yorum,
					'puan' => $puan,
					'ip'	=> get_ip(),
					'tarih' => date('Y-m-d H:i:s'),
					'durum' => $this->siteayar->otomatik_yorum_onayi
				];


				if ($this->db->insert('urun_yorumlar', $veri)) {
					$json['durum'] = 'success';

					$json['mesaj'] =
						'<div class="alert alert-success"> Yorumunuz başarıyla eklendi!</div>
						<meta http-equiv="refresh" content="2;URL=' . base_url('urun/' . $slug) . '">
					';

					//$this->load->model('Bildir_model');
					//$this->Bildir_model->yonetici_bildir('yeni_yorum', $veri);
				}
			} else {
				$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
			}

			print_r(json_encode($json));
		}
	}

}
