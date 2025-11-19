<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Sifre_Degistir extends CI_Controller
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


        if(!uye_oturum_kontrol(0)){ redirect();}
	}
    

	public function index()
	{
		$veri = [
			'sayfa_adi' => 'uye/hesabim/sifre-degistir',
			'sayfa_title' => 'Hesabım - ' . siteayar()->site_baslik ,
			
            'siteayar' => siteayar()			
		];
	
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function sifre_degistir(){
		if(!uye_oturum_kontrol(0)){
			echo "no";
			exit;
		}

		$hata_str = '';
        $html_hata = '';
        $sonuc= 'no';


		$yeni_sifre      		= (post('sifre1'));
        $sifre_tekrar      		= (post('sifre2'));
		$eski_sifre     		= (post('eski_sifre'));
		
        if(strlen($yeni_sifre) <= 8)  		{ $hata_str .= '<i class="fa fa-times"></i> Yeni şifreniz en az 8 karakter olmalıdır<br>'; }
		if($yeni_sifre != $sifre_tekrar) 	{ $hata_str .= '<i class="fa fa-times"></i> Şifreler uyuşmuyor<br>'; }

		$yeni_sifre      		= sha1($yeni_sifre);
        $sifre_tekrar      		= sha1($sifre_tekrar);
		$eski_sifre     		= sha1($eski_sifre);

		if($hata_str == ''){

			if($eski_sifre == uye()->sifre){
				$this->db->where('id', uye()->id);    
				if($this->db->update('uyeler', array('sifre' => $yeni_sifre))){
					$sonuc = 'yes';
					$html_hata =  ' <div class="alert alert-success"> Şifreniz güncellendi!...</div>'; 
				}
			} else { 
				$html_hata =  ' <div class="alert alert-danger"> Eski şifrenizi yanlış girdiniz. </div>';   
			}

		} else {
            $html_hata = '<div class="alert alert-danger">'.$hata_str.'</div> ';
		}

		$json = [
			'sonuc' => $sonuc,
			'html'   => $html_hata
		];
		
		echo json_encode($json);

	}

	
	

}
