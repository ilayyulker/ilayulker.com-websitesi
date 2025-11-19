<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');


class Urunler extends CI_Controller
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
			'sayfa_adi' => 'urun/urun_list',
			'sayfa_title' => 'Ürünler - ' . siteayar()->site_baslik,
			'kategori_tipi'		=> "bos"
		];

		$ARAMA_STR = html_sil(post("ara"));
		if (strlen($ARAMA_STR) < 1) {
			$ARAMA_STR = html_sil($this->input->get("ara"));
		}
		$veri["arama_str"] = $ARAMA_STR;

		$sayfa_link = '/urunler/?ara=' . $ARAMA_STR;
		$filtreden = $this->UrunleriGetir("",  "", $sayfa_link);
		$veri["filtreli_urunler"] = $filtreden["urun_listesi"];
		$veri["pagination_link"]  = $filtreden["pagination_link"];
		$veri["toplam_bulunan_urun"]  = $filtreden["toplam_bulunan_urun"];
		$veri["limit_start"]  = $filtreden["limit_start"];
		$veri["limit_stop"]   = $filtreden["limit_stop"];

		$veri["kategori_baslik"] 	= "KATEGORİLER";
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}


	public function kategori($kategori_seo)
	{
		$kategori_tipi = "bos";
		$kategori = $this->db->get_where("u_kategoriler", array("seo" => $kategori_seo));

		if ($kategori->num_rows() > 0) {
			$kategori_tipi = "kategori";
		} else {
			redirect("/");
		}

		$veri = [
			'sayfa_adi' => 'urun/urun_list',
			'sayfa_title' => 'Ürünler - ' . siteayar()->site_baslik,
		];

		$veri["kategori"] 			= $kategori->row();
		$veri["secilen_kategori"] 	= $kategori->row();
		$veri["kategori_tipi"] 		= $kategori_tipi;
		$veri["siteayar"] 			= siteayar();
		$veri["arama_str"] 			= "";

		$sayfa_link = '/kategori/' . $kategori->row()->seo;
		$filtreden = $this->UrunleriGetir("kategori", $kategori->row()->id, $sayfa_link);
		$veri["filtreli_urunler"] = $filtreden["urun_listesi"];
		$veri["pagination_link"]  = $filtreden["pagination_link"];
		$veri["toplam_bulunan_urun"]  = $filtreden["toplam_bulunan_urun"];
		$veri["limit_start"]  = $filtreden["limit_start"];
		$veri["limit_stop"]   = $filtreden["limit_stop"];


		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function altkategori($kategori_seo, $altkategori_seo)
	{
		$kategori_tipi = "";

		$kategori 	 = $this->db->get_where("u_kategoriler", array("seo" => $kategori_seo));
		$altkategori = $this->db->get_where("u_alt_kategoriler", array("seo" => $altkategori_seo));

		if ($altkategori->num_rows() > 0) {
			$kategori_tipi = "altkategori";
		} else {
			redirect("/");
		}

		if ($kategori->num_rows() < 1) {
			redirect("/");
		}


		$veri = [
			'sayfa_adi' => 'urun/urun_list',
			'sayfa_title' => 'Ürünler - ' . siteayar()->site_baslik,
		];



		$veri["arama_str"] 			= "";
		$veri["kategori"] 				= $kategori->row();
		$veri["altkategori"] 			= $altkategori->row();
		$veri["secilen_altkategori"] 	= $altkategori->row();
		$veri["kategori_tipi"] 			= $kategori_tipi;
		$veri["siteayar"] 				= siteayar();

		$sayfa_link = '/kategori/' . $kategori->row()->seo . '/' . $altkategori->row()->seo;
		$filtreden = $this->UrunleriGetir("altkategori",  $altkategori->row()->id, $sayfa_link);
		$veri["filtreli_urunler"] = $filtreden["urun_listesi"];
		$veri["pagination_link"]  = $filtreden["pagination_link"];

		$veri["toplam_bulunan_urun"]  = $filtreden["toplam_bulunan_urun"];
		$veri["limit_start"]  = $filtreden["limit_start"];
		$veri["limit_stop"]   = $filtreden["limit_stop"];


		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	//ajax ürün filtrele
	public function UrunleriGetir($kategori_tipi, $kategori_id, $sayfa_link)
	{
		$ARAMA_STR = html_sil(post("ara") == null ? "" : post("ara"));
		if (strlen($ARAMA_STR) < 1) {
			$ARAMA_STR = html_sil($this->input->get("ara"));
		}

		$query = " SELECT * FROM urunler WHERE aktif = '1' ";

		$filtre_minimum_fiyat 	= get('filtre_minimum_fiyat');
		$filtre_maximum_fiyat 	= get('filtre_maximum_fiyat');
		$filtre_siralama 		= get('filtre_siralama');
		$filtre_limit 			= get('filtre_limit');
		if ($filtre_limit > 100) $filtre_limit = 100;
		if ($filtre_limit < 1) $filtre_limit = 12;

		if (isset($ARAMA_STR)) {
			if ($ARAMA_STR != "") {
				$query .= " AND ad LIKE '%" . $ARAMA_STR . "%' ";
			}
		}

		if ($filtre_minimum_fiyat != 0 && $filtre_minimum_fiyat > 0) {
			$query .= " AND fiyat >= " . $filtre_minimum_fiyat;
		}
		if ($filtre_maximum_fiyat != 0 && $filtre_maximum_fiyat > 0) {
			$query .= " AND fiyat <= " . $filtre_maximum_fiyat;
		}



		if ($kategori_tipi == "kategori") {
			if ($kategori_id != "") {
				$query .= ' AND FIND_IN_SET("' . $kategori_id . '", kategori_id) ';
			}
		}

		if ($kategori_tipi == "altkategori") {
			if ($kategori_id != "") {
				$query .= ' AND FIND_IN_SET("' . $kategori_id . '", alt_kategori_id) ';
			}
		}

		$filtre_urun_sayisi = $this->db->query($query)->num_rows();


		$sayfada_gosterilecek_urun_limiti = $filtre_limit;
		$this->load->library('pagination');
		$config = array();

		$config['page_query_string'] = TRUE;
		$config['query_string_segment'] = "sayfa";
		$config['base_url'] = $sayfa_link;
		$config['per_page'] = $sayfada_gosterilecek_urun_limiti;
		$config['uri_segment'] = 3;
		$config['total_rows'] = $filtre_urun_sayisi;

		$config['num_links'] = 2;
		$config['use_page_numbers'] = TRUE;

		$config['full_tag_open'] = '<ul class="pagination justify-content-center">';
		$config['full_tag_close'] = '</ul>';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';

		$config['next_link'] = '<i class="fa fa-caret-right"></i> <span aria-hidden="true"><i class="icon-long-arrow-right"></i></span>';

		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['prev_link'] = '<span aria-hidden="true"><i class="icon-long-arrow-left"></i></span> <i class="fa fa-caret-left"></i>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] =  "<li class=\"page-item\">";
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);

		$page = isset($_GET["sayfa"]) ? $_GET["sayfa"] : 1;
		$start = ($page - 1) * $config['per_page'];

		//ÜSTTEKİ FİLTRELERE LİMİT EKLE
		if ($filtre_siralama == "baslik_asc") {
			$query .= ' ORDER BY ad ASC';
		} else if ($filtre_siralama == "baslik_desc") {
			$query .= ' ORDER BY ad DESC';
		} else if ($filtre_siralama == "fiyat_asc") {
			$query .= ' ORDER BY fiyat ASC';
		} else if ($filtre_siralama == "fiyat_desc") {
			$query .= ' ORDER BY fiyat DESC';
		} else {
			$query .= ' ORDER BY urun_id DESC';
		}

		$query .= ' LIMIT ' . $start . ', ' . $config["per_page"];
		$FILTRELI_URUNLER = $this->db->query($query);


		$limit_start = $start;
		$limit_stop  = $start + $sayfada_gosterilecek_urun_limiti;

		if ($limit_start == 0)
			$limit_start = 1;
		if ($limit_stop > $config['total_rows'])
			$limit_stop = $config['total_rows'];

		$output = array(
			'toplam_bulunan_urun' => $config['total_rows'],
			'limit_start' => $limit_start,
			'limit_stop'  => $limit_stop,
			'pagination_link'  	=> $this->pagination->create_links(),
			'urun_listesi'   	=> $FILTRELI_URUNLER
		);

		return $output;
	}
}
