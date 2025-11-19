<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Haber extends CI_Controller
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
	
        $this->load->helper('file');

        $this->title = 'Haber - ' . siteayar()->site_baslik;
    }

    public function index()
    {
         $this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
            'kategori'          => "0",
            'sayfa_title'       => "Haberler - " . siteayar()->site_baslik,
            'sayfa_adi'         => 'haber/kategori'
        ));
    }

    public function haber($haber_slug)
    {
      
        $row_haber       = $this->db->get_where("haberler", array("slug" => $haber_slug));
        if ($row_haber->num_rows() == 0) {
            redirect(base_url());
        }
        $haber = $row_haber->row();


        $row_kategori       = $this->db->get_where("haber_kategoriler", array("id" => $haber->kategori_id));
        if ($row_kategori->num_rows() == 0) {
            redirect(base_url());
        }
        $kategori = $row_kategori->row();

        
        $this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
            'haber'             => $haber,
            'kategori'          => $kategori,
            'sayfa_title'       => $haber->baslik . ' - ' . siteayar()->site_baslik,
            'sayfa_adi'         => 'haber/haber',
            
        ));
    }

    public $mod = "hepsi";
    public $title = '';
    public $row_kategori = null;
    public $kategori_id = 0;


    public function haber_kategori($kategori_slug)
    {
        $row_kategori       = $this->db->get_where("haber_kategoriler", array("slug" => $kategori_slug));
        if ($row_kategori->num_rows() == 0) {
            redirect(base_url());
        }
        $kategori = $row_kategori->row();


        $this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
            'kategori'          => $kategori,
            'sayfa_title'       => $kategori->baslik,
            'sayfa_adi'         => 'haber/kategori'
        ));
    }

    /******************/
    public function haberler_json($page)
    {
        $filter_data = (object) [
            "arama"                 => html_sil($this->input->post('arama')),
            'kategori_id'           => $this->input->post('kategori_id')
        ];

        $limit = 15;

        $this->load->library('pagination');
        $config = array();
        $config['base_url'] = '#';
        $config['total_rows'] = $this->sorgu_satir_sayisi($filter_data);
        $config['per_page'] = $limit;
        $config['uri_segment'] = 3;
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



        $config['num_links'] = 3;


        $this->pagination->initialize($config);
        $page = $this->uri->segment(3);
        $start = ($page - 1) * $config['per_page'];
        $output = array(
            'pagination_link'  => $this->pagination->create_links(),
            'haber_count'  => $this->sorgu_satir_sayisi($filter_data),
            'haber_list'   => $this->filtreli_haber_getir($config["per_page"], $start, $filter_data)
        );

        echo json_encode($output);
    }

    private function filtre_step_1($filtre)
    {
        $query_txt = " SELECT * FROM haberler WHERE durum = '1' ";

        /* arama filtresi */
        if (isset($filtre->kategori_id)) {
            if ($filtre->kategori_id != "0")
                $query_txt .= 'AND kategori_id = ' . $filtre->kategori_id . ' ';
        }

        /* arama filtresi */
        if ($filtre->arama != "") {
            $query_txt .= " 
            AND  ( baslik LIKE '%" . $filtre->arama . "%'  
            or icerik like '%" . $filtre->arama . "%' )
            ";
        }

        return $query_txt;
    }

    private function sorgu_satir_sayisi($filtre)
    {
        $query = $this->filtre_step_1($filtre);
        $data = $this->db->query($query);
        return $data->num_rows();
    }

    private function filtreli_haber_getir($limit, $start, $filtre)
    {
        $query = $this->filtre_step_1($filtre);
        $query .= " ORDER BY id desc";

        $query .= ' LIMIT ' . $start . ', ' . $limit;
        $data = $this->db->query($query);

        $item_part_html = file_get_contents(APPTYPATH . 'temalar/' . siteayar()->tema . '/sayfalar/haber/part/haber-item.php');;

        $output = '';
        if ($data->num_rows() > 0) {
            foreach ($data->result() as $row) {
                $kategori       = $this->db->get_where("haber_kategoriler", array("id" => $row->kategori_id));

                $_item_part_html = $item_part_html;
                if ($kategori->num_rows() > 0) {
                    $kategori = $kategori->row();
                    $_item_part_html = str_replace("{{KATEGORI_BASLIK}}", isset($kategori->baslik) ? $kategori->baslik : '', $item_part_html);
                    $_item_part_html = str_replace("{{KATEGORI_URL}}", isset($kategori->baslik) ? base_url('haber/kategori/' . $kategori->slug) : '', $_item_part_html);
                }
                $_item_part_html = str_replace("{{HREF_URL}}", base_url("haber/" . $row->slug), $_item_part_html);
                $_item_part_html = str_replace("{{BASLIK}}", $row->baslik, $_item_part_html);
                $_item_part_html = str_replace("{{ICERIK}}", (mb_substr($row->kisa_aciklama, 0, 250)), $_item_part_html);
                $_item_part_html = str_replace("{{HABER_TARIH}}", (iconv('latin5', 'utf-8', strftime(' %d %B %Y %A', strtotime($row->tarih)))), $_item_part_html);



                $_item_part_html = str_replace("{{RESIM_URL}}", public_folder() . '/uploads/haberler/' . $row->img, $_item_part_html);
                $output .= $_item_part_html;
            }
        } else {
            $output = '
                <div class="col-md-3 col-sm-6 col-xs-12" style="padding-top:30px">
                    Haber bulunamadı.
                </div>
            ';
        }
        return $output;
    }
}
