<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Adreslerim extends CI_Controller
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
			'sayfa_adi' => 'uye/hesabim/adreslerim',
			'sayfa_title' => 'Adreslerim - ' . siteayar()->site_baslik,
            
            'siteayar' => siteayar()
		];

        $this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
    }

    public function islem($islem='ekle')
	{
        $adres_adi      = html_sil(post('adres-adi'));
        $isim           = html_sil(post('isim'));
        $sirket_adi     = html_sil(post('sirket-adi'));
        $adres         = html_sil(post('adres'));
        $il             = html_sil(post('il'));
        $ilce           = html_sil(post('ilce'));
        $soyisim        = html_sil(post('soyisim'));
        $telefon        = html_sil(post('telefon'));
        $postakodu      = html_sil(post('postakodu'));
        $ulke           = html_sil(post('ulke'));
        
        $hata_str = '';
        $html_hata = '';
        $sonuc= 'no';

        if(strlen($adres_adi) < 2)  { $hata_str .= '<i class="fa fa-times"></i> Adres adı boş bırakılamaz<br>'; }
        if(strlen($isim) < 2)       { $hata_str .= '<i class="fa fa-times"></i> İsim boş bırakılamaz<br>'; }
        if(strlen($sirket_adi) < 2) { $sirket_adi .= ""; }
        if(strlen($adres) < 2)     { $hata_str .= '<i class="fa fa-times"></i> Adres 1 boş bırakılamaz<br>'; }
        if(strlen($il) < 2)         { $hata_str .= '<i class="fa fa-times"></i> İl boş bırakılamaz<br>'; }
        if(strlen($ilce) < 2)       { $hata_str .= '<i class="fa fa-times"></i> İlçe boş bırakılamaz<br>'; }
        if(strlen($soyisim) < 2)    { $hata_str .= '<i class="fa fa-times"></i> Soyisim boş bırakılamaz<br>'; }
        if(strlen($telefon) != 11)   { $hata_str .= '<i class="fa fa-times"></i> Telefon numaranız başında 0 ile birlikte 11 karakter olmalıdır<br>'; }
        if(strlen($postakodu) < 2)  { $hata_str .= '<i class="fa fa-times"></i> Posta kodu boş bırakılamaz<br>'; }
        if(strlen($ulke) < 1)       { $hata_str .= '<i class="fa fa-times"></i> Ülke boş bırakılamaz<br>'; }
        
        if($islem == "ekle"){
            if($this->db->get_where("adreslerim", array('adres_adi' =>  $adres_adi, 'uye_id' => uye()->id))->num_rows() != 0 )
            { 
                $hata_str .= '<i class="fa fa-times"></i> Adres adı zaten kayıtlı.<br>';  
            }
        } 

        if($hata_str == ''){
            $kayit_verisi = [
                'uye_id'        => uye()->id,
                'adres_adi'     => $adres_adi,
                'isim'          => $isim,
                'sirket_adi'    => $sirket_adi,
                'adres'        => $adres,
                'il'            => $il,
                'ilce'          => $ilce,
                'soyisim'       => $soyisim,
                'telefon'       => $telefon,
                'postakodu'     => $postakodu,
                'ulke'          => $ulke
            ];    
            
            if($islem == "ekle"){

                if($this->db->insert('adreslerim', $kayit_verisi)){
                    $sonuc= 'yes';
                    $html_hata =  ' <div class="alert alert-success"> Adres başarıyla eklendi!...</div>';    
                }
            } else if($islem == "duzenle"){
                $post_adres_id  = html_sil(post('adres-id'));

                $duzenlenen_adres_row   = $this->db->get_where("adreslerim", array('id' =>  $post_adres_id, 'uye_id' => uye()->id));
                $adres_row = $this->db->get_where("adreslerim", array('adres_adi' =>  $adres_adi, 'uye_id' => uye()->id))->row();
                
                if($adres_row){
                    if($duzenlenen_adres_row->row()->id == $adres_row->id)
                    {
                        if($duzenlenen_adres_row->num_rows() > 0){

                            $this->db->where('id', $post_adres_id);    
                            if($this->db->update('adreslerim', $kayit_verisi)){
        
                                $sonuc= 'yes';
                                $html_hata =  ' <div class="alert alert-success"> Adres başarıyla güncellendi!...</div>';    
                            } 
                        }else{
                            $html_hata = '<div class="alert alert-danger"> Erişim hatası. Sayfayı yenileyin! </div> ';
                        }
    
                    }else{
                        $html_hata =  ' <div class="alert alert-danger"> Adres adı zaten kayıtlı. </div>';   
                    }
                }else{

                    if($duzenlenen_adres_row->num_rows() > 0){

                        $this->db->where('id', $post_adres_id);    
                        if($this->db->update('adreslerim', $kayit_verisi)){
    
                            $sonuc= 'yes';
                            $html_hata =  ' <div class="alert alert-success"> Adres başarıyla güncellendi!...</div>';    
                        } 
                    }else{
                        $html_hata = '<div class="alert alert-danger"> Erişim hatası. Sayfayı yenileyin! </div> ';
                    }

                }
                
            }

        } else {
            $sonuc= 'no';
            $html_hata = '<div class="alert alert-danger">'.$hata_str.'</div> ';
        }

        $json = [
            'sonuc' => $sonuc,
            'msj'   => $html_hata
        ];
        
        echo json_encode($json);
    }

    public function sil()
	{
        $adres_id = html_sil(post("id"));

		$kontrol = $this->db->get_where('adreslerim', array("id" => $adres_id, 'uye_id' => uye()->id));

		if($kontrol->num_rows() > 0){
            $this->db->delete("adreslerim", array("id" => $adres_id, 'uye_id' => uye()->id));
		}
    }

    public function adresleri_cek()
	{
        if(!uye(0)){
			echo "no";
			exit;
        }
        
        $adres_id = 0;
        $type = post('type');
        $json["html"] = '';
        $json["selected_option"] = '<option>Adres Seçiniz...</option>';
        if($type == 'hepsi'){
            $adresler_tablo = $this->db->get_where("adreslerim", array('uye_id' => uye()->id));
            foreach($adresler_tablo->result() as $adresler){
                $json["html"] .= '
                    <tr>
                        <td class="text-center ozel">'.$adresler->adres_adi.'</td>
                        <td class="text-center">'.$adresler->isim.' '.$adresler->soyisim.'</td>
                        <td class="text-center">'.substr($adresler->adres, 0,25).'....</td>
                        <td class="text-center">
                            <a href="" class="button adresi-guncelle" id="'.$adresler->id.'"  data-toggle="modal" data-target="#adres-guncelle" data-toggle="tooltip" title="" data-original-title="Adresi Güncelle">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="" class="adresi-sil" style="color:#F44336" id="'.$adresler->id.'" data-toggle="tooltip" title="" data-original-title="Sil">
                                <i class="fa fa-times"></i>
                            </a>
                        </td>
                    </tr>
                
                ';

                $json["selected_option"] .= '<option value="'.$adresler->id.'">'.$adresler->adres_adi.'</option>"';
            }
            if($adresler_tablo->num_rows() < 1){
                $json["html"] = '
                    <tr>
                        <td class="text-center" colspan="5">
                            Adres listeniz boş.
                        </td>
                    </tr>
                ';
                $json["selected_option"] = '<option value="0">Lütfen adres ekleyin.</option>"';
            }
        } else if($type == 'tek') {
            $adres_id = isset($_POST["id"]) ? $_POST["id"] : 0;
            $adres_row = $this->db->get_where("adreslerim", array('id' => $adres_id, 'uye_id' => uye()->id))->row();

            if($adres_row){
                $json = [
                    'id'            => $adres_id,
                    'adres_adi'     => $adres_row->adres_adi,
                    'isim'          => $adres_row->isim,
                    'sirket_adi'    => $adres_row->sirket_adi,
                    'adres'        => $adres_row->adres,
                    'il'            => $adres_row->il,
                    'ilce'          => $adres_row->ilce,
                    'soyisim'       => $adres_row->soyisim,
                    'telefon'       => $adres_row->telefon,
                    'postakodu'     => $adres_row->postakodu,
                    'ulke'          => $adres_row->ulke,
                ];
            }
        }

        echo json_encode($json);
        
    }

  
    
    
     




}
