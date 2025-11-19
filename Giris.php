<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Giris extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        
    }

    public function index()
    {
        $hata = '';

        if ($_POST) {
            $email = post('email');
            $sifre = sha1(post('sifre'));


            if (strlen($email) < 1 || strlen($sifre) < 1) {
                $hata = '<div class="alert alert-danger">Lütfen boş alan bırakmayın.</div> ';
            }


            $array = ['email' => $email, 'sifre' => $sifre];
            $roww = $this->db->get_where('yoneticiler', $array);

            if ($roww->num_rows() > 0) {

                if ($roww->row()->durum) {
                    $_SESSION["admin"]["email"] = $roww->row()->email;
                    $_SESSION["admin"]["sifre"] = $roww->row()->sifre;
                    $hata = '<div class="alert alert-success">Giriş Yapıldı, Yönlendiriliyor <meta http-equiv="refresh" content="1;URL=/admin"></div> ';

                    $this->db->where("id", $roww->row()->id);
                    $this->db->update("yoneticiler", array("ip" => get_ip(), "songiris" => date("Y-m-d H:i:s")));
                }else{
                    $hata = '<div class="alert alert-danger">Yönetim hesabınız aktif değil.</div> ';
                }
            } else {
                $hata = '<div class="alert alert-danger">E-Mail veya Şifre Yanlış</div> ';
            }
        }


        $veri = [
            'hata' => $hata,
            'sayfa_title' => 'Giriş Yap - ' . siteayar()->site_baslik,
            'siteayar' => siteayar()
        ];

        $this->load->view('yonetim/giris', $veri);
    }

   
 

    public function cikis()
    {
        unset($_SESSION["admin"]);

        redirect("/admin");
    }
}
