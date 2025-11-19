<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Favoriler extends CI_Controller
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


		if (!uye_oturum_kontrol(0)) {
			redirect();
		}
	}

	public function index()
	{
		$veri = [
			'sayfa_adi' => 'uye/hesabim/favoriler',
			'sayfa_title' => 'Favoriler - ' . siteayar()->site_baslik,

			'siteayar' => siteayar()
		];

		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function listeyi_cek()
	{

		$istek_listesi = $this->db->get_where('istek_listesi', array("uye_id" => uye()->id));

		$html = '';

		$say = 0;
		foreach ($istek_listesi->result() as $istek) {
			$urun = $this->Market_model->urun($istek->urun_id);
			if ($urun) {
				$urun = $urun->row();

				$say++;
				$html_indirim_miktari = '';
				//if ($urun->indirim == true) {
				//	$html_indirim_miktari = '<span class="old-price">' . number_format($urun->indirimsizfiyat, 0, '.', ',') . '₺</span>';
				//}


				$m_urun = $this->Urun_model;
				$urun = $m_urun->urun($urun->urun_id);

				$urun_resimleri = json_decode($urun->resimler);

				$urun_fiyat = $urun->fiyat;
				if ($urun_fiyat == 0) {
					$urun_fiyat = "STOK YOK";
				} else {
					$urun_fiyat = number_format($urun->fiyat, 2, '.', ',') . ' ₺';
				}


				$html .= '
					<tr>
						<td class="text-center pl-4">
							<a href="' . $m_urun->kategori->seo . '/'  . $urun->seo . '"><img width="47" src="/public/uploads/urunler/' . $urun_resimleri[0] . '" alt="#" /></a>
						</td>
						<td class="text-left"> <a href="' . $m_urun->kategori->seo . '/' . $urun->seo . '">' . $urun->ad . '</a> </td>
					
						<td class="text-center">' . ($urun->stok > 0 ? "Stokta Var" : "Stokta Yok") . '</td>
						<td class="text-center">
							<div class="price-box">
								<span class="price">' . $urun_fiyat . '</span> ' . $m_urun->html_indirim_miktari . '
								' . $html_indirim_miktari . '
							</div>
							' . $m_urun->html_indirim_miktari . '
						</td>
						<td class="text-center">
							<a class="button" href="' . base_url($m_urun->kategori->seo . '/' . $urun->seo) . ' ">
								<i class="fa fa-shopping-cart"></i>
							</a>
							<button class="btn btn-istek-sil" id="' . $urun->urun_id . '" data-toggle="tooltip" title="Sil">
								<i class="fa fa-times"></i>
							</button>
						</td>
					</tr>
				';
			}
		}

		if ($say == 0) {
			$html = '
				<tr>
					<td class="text-center" colspan="5">
						İstek listeniz boş.
					</td>
				</tr>
			';
		}



		$result = [
			'html' => $html,
			'count' => $this->Uye_model->istek_listesi_sayisi(uye()->id)
		];

		echo json_encode($result);
	}

	public function sil()
	{
		$urun_id = html_sil(post("id"));

		$kontrol = $this->db->get_where('istek_listesi', array("urun_id" => $urun_id, 'uye_id' => uye()->id));

		if ($kontrol->num_rows() > 0) {
			$this->db->delete("istek_listesi", array("urun_id" => $urun_id, 'uye_id' => uye()->id));
		}
	}
}
