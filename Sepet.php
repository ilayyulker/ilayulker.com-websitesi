<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Sepet extends CI_Controller
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

	public function index()
	{
		$veri = [
			'sayfa_adi' => 'sepet/sepet',
			'sayfa_title' => 'Sepetim - ' . siteayar()->site_baslik,
			'siteayar' => siteayar()
		];
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function sepet_body()
	{
		$sepet = (object)$this->Sepet_model;

		$data = array(
			'sepet' => $sepet,
		);

		$this->load->view(siteayar()->tema . '/sayfalar/sepet/_part_sepet_body', $data);
	}

	public function sepete_ekle()
	{
		if ($_POST) {
			echo json_encode($this->Sepet_model->ekle());
		}
	}

	public function sepet_adet_guncelle()
	{

		$this->Sepet_model->adet_guncelle("H4tUVHEyE91DPaKHEWFl", "2");


		if ($_POST) {
			$id = $_POST["id"];
			$adet = $_POST["adet"];
			$this->Sepet_model->adet_guncelle($id, $adet);
		}
	}

	public function sepet_urun_sil()
	{
		if ($_POST) {
			$this->Sepet_model->sil();
		}
	}

	public function sepet_head()
	{
		$sepet = (object)$this->Sepet_model;

		$data = array(
			'sepet' => $sepet,
		);

		$this->load->view(siteayar()->tema . '/sayfalar/sepet/_header_part_sepet', $data);
	}

	public function sepet_bilgi()
	{
		$sepet = (object)$this->Sepet_model;

		$result = [
			'sepet_toplam' 	=> number_format($sepet->urunler_tutar, 2, '.', ','),
			'sepet_miktar' 	=> $sepet->urun_sayisi
		];

		echo json_encode($result);
	}
}
