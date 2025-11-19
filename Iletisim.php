<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');


class Iletisim extends CI_Controller
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
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
			'sayfa_adi' => 'iletisim',
			'sayfa_title' => 'İletişim - ' . siteayar()->site_baslik
		));

		
	}


	public function gonder()
	{

		$json = ['durum' => 'error', 'mesaj'   => ''];


		$isimsoyisim  	= html_sil(post('isimsoyisim'));
		$email        	= html_sil(post('email'));
		$telefon        = html_sil(post('telefon'));
		$mesaj      	= html_sil(post('mesaj'));



		if (strlen($isimsoyisim) < 3 || strlen($isimsoyisim) > 50) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> İsim Soyisim 2 ile 50 karakter arası olmalıdır.<br>';
		}

		if ($telefon == "") {
			$telefon = " ";
		}
		if (strlen($telefon) > 12) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> Telefon max 12 karakter olmalıdır<br>';
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$json['mesaj']  .= '<i class="fa fa-times"></i> Geçersiz E-Mail.<br>';
		}

		if (strlen($mesaj) < 3 || strlen($mesaj) > 500) {
			$json['mesaj'] =  '<i class="fa fa-times"></i> Mesaj 2 ile 500 karakter arası olmalıdır.<br>';
		}


		if ($json['mesaj'] == '') {

			$veri = [
				'isimsoyisim' => $isimsoyisim,
				'email' => $email,
				'telefon' => $telefon,
				'mesaj' => $mesaj,
				'ip'	=> get_ip(),
				'tarih' => date('Y-m-d H:i:s'),
				'durum' => 'aktif'
			];

			if ($this->db->insert('iletisim', $veri)) {
				$json['durum'] = 'success';

				$json['mesaj'] =
					'<div class="alert alert-success"> İletişim mesajınız gönderildi!</div>
					<meta http-equiv="refresh" content="2;URL=' . base_url('iletisim') . '">
					';
			}
		} else {
			$json['mesaj'] = '<div class="alert alert-danger">' . $json['mesaj']  . '</div> ';
		}

		print_r(json_encode($json));
	}
}
