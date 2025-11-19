<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Anasayfa extends CI_Controller
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
		$anasayfa_sliderler = $this->db->order_by("sira", "asc")->get("slider");
		$anasayfa_bannerlar = $this->db->order_by('sira', 'asc')->get("banner");
		$anasayfa_vitrinler = $this->db->order_by('sira', 'asc')->get_where('u_vitrin');
		$anasayfa_haberler  = $this->db->order_by('id', 'desc')->where('durum', '1')->limit(8)->get('haberler');
		
		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, array(
			'anasayfa_sliderler' => $anasayfa_sliderler,
			'anasayfa_bannerlar' => $anasayfa_bannerlar,
			'anasayfa_vitrinler' => $anasayfa_vitrinler,
			'anasayfa_haberler' => $anasayfa_haberler,
			'sayfa_adi' 	=> 'anasayfa',
			'sayfa_title' 	=> $this->siteayar->site_baslik . ' - ' . $this->siteayar->site_slogan
		));
	}


	public function sitemapxml()
	{


		header('Content-type: application/xml; ', true);

		echo '
		<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
		';

		echo '
			<url>
				<loc>' . base_url() . '</loc>
				<changefreq>weekly</changefreq>
				<priority>0.7</priority>
			</url>
			<url>
				<loc>' . base_url('urunler') . '</loc>
				<changefreq>weekly</changefreq>
				<priority>0.7</priority>
			</url>
			<url>
				<loc>' . base_url('iletisim') . '</loc>
				<changefreq>weekly</changefreq>
				<priority>0.7</priority>
			</url>
			<url>
				<loc>' . base_url('siparis/sorgula') . '</loc>
				<changefreq>weekly</changefreq>
				<priority>0.7</priority>
			</url>
			<url>
				<loc>' . base_url('hesap-numaralarimiz') . '</loc>
				<changefreq>weekly</changefreq>
				<priority>0.7</priority>
			</url>
		';

		$sayfalar = $this->db->get_where('sayfalar', ['durum' => 1])->result();
		foreach ($sayfalar as $sayfa) {
			echo '
				<url>
					<loc>' . base_url('sayfa/' . $sayfa->slug) . '</loc>
					<changefreq>weekly</changefreq>
					<priority>0.7</priority>
				</url>
			';
		}

		$kategoriler = $this->db->get_where('u_kategoriler')->result();
		foreach ($kategoriler as $kategori) {
			echo '
				<url>
					<loc>' . base_url('kategori/' . $kategori->seo) . '</loc>
					<changefreq>weekly</changefreq>
					<priority>0.7</priority>
				</url>
			';
		}

		$urunler = $this->db->get_where('urunler')->result();
		foreach ($urunler as $urun) {
			$m_urun = $this->Urun_model;
			$urun = $m_urun->urun($urun->urun_id);

			echo '
				<url>
					<loc>' . base_url($m_urun->kategori->seo . '/' . $urun->seo) . '</loc>
					<changefreq>weekly</changefreq>
					<priority>0.7</priority>
				</url>
			';
		}

		$haber_kategoriler = $this->db->get_where('haber_kategoriler')->result();
		foreach ($haber_kategoriler as $haber_kategori) {

			echo '
				<url>
					<loc>' . base_url('haber/kategori/' . $haber_kategori->slug) . '</loc>
					<changefreq>weekly</changefreq>
					<priority>0.7</priority>
				</url>
			';
		}

		$haberler = $this->db->get_where('haberler')->result();
		foreach ($haberler as $haber) {

			echo '
				<url>
					<loc>' . base_url('haber/' . $haber->slug) . '</loc>
					<changefreq>weekly</changefreq>
					<priority>0.7</priority>
				</url>
			';
		}


		echo '
		</urlset>
		';
	}

	
}
