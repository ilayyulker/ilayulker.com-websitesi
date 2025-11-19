<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Ayarlar extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');

        admin_oturum_kontrol();
    }

    public function genel_ayarlar()
    {
        if ($_POST) {
            echo $this->genel_ayarlar_post();
            exit;
        }

        $siteayar = $this->db->get_where("ayarlar", array("id" => 1))->row();
        $veri = [
            'siteayar' => $siteayar,
            'sayfa_adi' => 'ayarlar/genel_ayarlar',
            'sayfa_title' => 'Site Ayarları - Admin Paneli',
            'siteayar' => siteayar()
        ];
        $this->load->view('yonetim/index', $veri);
    }

    public function genel_ayarlar_post()
    {
        $json = ['durum' => 'error', 'mesaj'   => ''];

        $resim_name = "logo";
        $favicon_name = 'favicon';

        $uploadResponse = array();

        if (strlen($_FILES['logo']['name']) > 2) {
            $uploadResponse =  resim_yukle("logo", "public/front/images/", $resim_name, 0, 0);

            if ($uploadResponse['hata'] == "0") {

                $resim_name = $uploadResponse["resim_name"] . '?v=' . rand(1000, 9999);
            }

            if ($uploadResponse["hata"] != "0") {
                $json["mesaj"] .= $uploadResponse["hata"];
            }
        } else {
            $resim_name = siteayar()->site_logo;
        }
        if (strlen($_FILES['favicon']['name']) > 2) {
            $uploadResponse =  resim_yukle("favicon", "public/front/images/", $favicon_name, 36, 36);

            if ($uploadResponse['hata'] == "0") {
                $favicon_name = $uploadResponse["resim_name"] . '?v=' . rand(1000, 9999);
            }

            if ($uploadResponse["hata"] != "0") {
                $json["mesaj"] .= $uploadResponse["hata"];
            }
        } else {
            $favicon_name = siteayar()->site_favicon;
        }

        if ($json['mesaj'] == '') {
            $post_array = $_POST;
            $post_array['site_logo'] = $resim_name;
            $post_array['site_favicon'] = $favicon_name;

            foreach ($post_array as $post_key => $post_val) {

                $mtn_db = $this->db->get_where('ayarlar', array('mkey' => $post_key));
                if ($mtn_db->num_rows() > 0) {

                    $this->db->where(array('mkey' => $post_key));
                    $this->db->update('ayarlar', array('mval' => $post_val));
                } else {

                    $this->db->insert('ayarlar', array('mkey' => $post_key, 'mval' => $post_val));
                }
            }

            $json['durum'] = 'success';
        }



        return json_encode($json);
    }

    public function odeme_ayarlari()
    {
        if ($_POST) {
            echo $this->odeme_ayarlari_post();
            exit;
        }

        $siteayar = $this->db->get_where("odeme_ayarlari", array("id" => 1))->row();
        $veri = [
            'siteayar' => $siteayar,
            'sayfa_adi' => 'ayarlar/odeme_ayarlari',
            'sayfa_title' => 'Ödeme Ayarları - Admin Paneli',
            'siteayar' => siteayar()
        ];
        $this->load->view('yonetim/index', $veri);
    }

    public function odeme_ayarlari_post()
    {
        $json = ['durum' => 'error', 'mesaj'   => ''];


        foreach ($_POST as $post_key => $post_val) {


            $mtn_db = $this->db->get_where('odeme_ayarlari', array('mkey' => $post_key));
            if ($mtn_db->num_rows() > 0) {

                $this->db->where(array('mkey' => $post_key));
                $this->db->update('odeme_ayarlari', array('mval' => $post_val));
            } else {

                $this->db->insert('odeme_ayarlari', array('mkey' => $post_key, 'mval' => $post_val));
            }
        }

        $json['durum'] = 'success';

        return json_encode($json);
    }

    public function mail_sms()
    {
        if ($_POST) {
            echo $this->mail_sms_post();
            exit;
        }

        $veri = [
            'sayfa_adi' => 'ayarlar/mail_sms',
            'sayfa_title' => 'Mail & SMS Ayarları - Admin Paneli',
            'siteayar' => siteayar()
        ];
        $this->load->view('yonetim/index', $veri);
    }

    public function mail_sms_post()
    {
        $json = ['durum' => 'error', 'mesaj'   => ''];

        $post_array = $_POST;

        foreach ($post_array as $post_key => $post_val) {

            $mtn_db = $this->db->get_where('ayarlar_mailsms', array('mkey' => $post_key));
            if ($mtn_db->num_rows() > 0) {

                $this->db->where(array('mkey' => $post_key));
                $this->db->update('ayarlar_mailsms', array('mval' => $post_val));
            } else {

                $this->db->insert('ayarlar_mailsms', array('mkey' => $post_key, 'mval' => $post_val));
            }
        }

        $json['durum'] = 'success';

        return json_encode($json);
    }



    public function tasarim_ayarlari()
    {
        if ($_POST) {
            echo $this->tasarim_ayarlari_post();
            exit;
        }

        $siteayar = $this->db->get_where("ayarlar_tasarim", array("id" => 1))->row();
        $veri = [
            'siteayar' => $siteayar,
            'sayfa_adi' => 'ayarlar/tasarim_ayarlari',
            'sayfa_title' => 'Tasarım Ayarları - Admin Paneli',
            'siteayar' => siteayar()
        ];
        $this->load->view('yonetim/index', $veri);
    }

    public function tasarim_ayarlari_post()
    {
        $json = ['durum' => 'error', 'mesaj'   => ''];

        $post = $_POST;

        $upload_response = array('hata' => "0");

      
        foreach ($_FILES as $form_name => $up_file) {
            if (strlen($up_file['name'])  > 3) {
                $img_name           = rastgele_sifre(10);
                $upload_response    =  resim_yukle($form_name, "public/uploads/images/", $img_name, 0, 0);

                if ($upload_response['hata'] == "0") {
                    $img_name = $upload_response["resim_name"];
                    $post[$form_name] = $img_name;
                } else {
                    $json["mesaj"] .= $upload_response["hata"] . '<br>';
                }
            }
        }

        if ($upload_response['hata'] == "0") {

            foreach ($post as $post_key => $post_val) {
                $mtn_db = $this->db->get_where('ayarlar_tasarim', array('mkey' => $post_key));
                if ($mtn_db->num_rows() > 0) {

                    $this->db->where(array('mkey' => $post_key));
                    $this->db->update('ayarlar_tasarim', array('mval' => $post_val));
                } else {

                    $this->db->insert('ayarlar_tasarim', array('mkey' => $post_key, 'mval' => $post_val));
                }
            }
            $json['durum'] = 'success';
        } else {
            $json["mesaj"] .= $upload_response["hata"];
            $json['durum'] = 'error';
        }

        return json_encode($json);
    }
}
