<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sayfalar extends CI_Controller {

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

	public function index($slug)
	{
        $sayfa = $this->db->get_where("sayfalar", array("slug" => $slug));

        if($sayfa->num_rows() < 1)
            redirect("/404");

		$veri = [
            'sayfa' => $sayfa->row(),
			'sayfa_adi' => 'sayfa',
			'sayfa_title' => $sayfa->row()->baslik . ' - ' . siteayar()->site_baslik
		];

		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

}
