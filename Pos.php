<?php
defined('BASEPATH') or exit('Doğrudan erişime izin verilmiyor');

class Pos extends CI_Controller
{
	private $_posslar = array(
		'paytr',
		'shopier',
		'weepay',
		'iyzico'
	);

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

	public function odeme($siparis_id)
	{
		$siparis = $this->db->get_where('siparisler', array('id' => $siparis_id));

		if ($siparis) {
			$siparis = $siparis->row();

			$this->pos_start(odeme_ayar()->aktif_pos, $siparis);
		}
	}

	public function kontrol($POS)
	{
		$this->pos_kontrol($POS);
	}

	/* Sipariş oluşturup siteden POS'a yönlendirme */
	public function pos_start($POS, $siparis)
	{
		foreach ($this->_posslar as $_pos) {
			if ($POS == $_pos) {
				$func_name = $_pos . '_start';
				$this->$func_name($siparis);
			}
		}
	}

	/* POS dönüşüdür. */
	public function pos_kontrol($POS)
	{
		foreach ($this->_posslar as $_pos) {
			if ($POS == $_pos) {
				$func_name = $_pos . '_kontrol';
				$this->$func_name();
			}
		}
	}

	/* Ödeme Sonucu Göster */
	public function odeme_basarili($siparis_key)
	{
		$_siparis = $this->db->get_where('siparisler', array('siparis_key' => $siparis_key));

		if ($_siparis->num_rows() > 0) {
			$_siparis = $_siparis->row();

			$this->load->view(siteayar()->tema . '/index', array(
				'sayfa_adi' => 'pos/odeme-basarili',
				'sayfa_title' => "Ödemeniz Başarıyla Tamamlandı"
			));
		} else {
			echo 'siparis bulunamadı';
		}
	}

	public function odeme_basarisiz()
	{
		$mesaj = isset($_SESSION['odeme_basarisiz_mesaj']) ? $_SESSION['odeme_basarisiz_mesaj'] : '';
		$this->load->view(siteayar()->tema . '/index', array(
			'mesaj' => $mesaj,
			'sayfa_adi' => 'pos/odeme-basarisiz',
			'sayfa_title' => "Ödemeniz Alınamadı"
		));
	}

	/* PAYTR POS SİSTEMİ */
	public function paytr_start($siparis)
	{
		$adres		  = (object)json_decode($siparis->teslimat_adresi);

		$tutar_pos_komisyon = $siparis->tutar * odeme_ayar()->paytr_komisyon / 100;
		$sepet_toplam_tutar = $tutar_pos_komisyon + $siparis->tutar;


		$sipariskey = rand(1000000000, 9999999999);
		$this->db->where("id", $siparis->id);
		$this->db->update("siparisler", array('sipariskey' => $sipariskey));

		$merchant_id 	= odeme_ayar()->paytr_merchant_id;
		$merchant_key 	= odeme_ayar()->paytr_merchant_key;
		$merchant_salt	= odeme_ayar()->paytr_merchant_salt;

		$email 				= $adres->eposta;
		$payment_amount		= $sepet_toplam_tutar * 100;
		$merchant_oid 		= $sipariskey;
		$user_name 			= $adres->isim . ' ' . $adres->soyisim;
		$user_address 		=  $adres->adres . ' ' . $adres->il . ' ' . $adres->ilce;
		$user_phone 		= $adres->telefon;

		$merchant_ok_url 		= base_url("odeme/odeme-basarili/" . $sipariskey);
		$merchant_fail_url 		= base_url("odeme/odeme-basarisiz/" . $sipariskey);

		$user_basket = array();

		$_sepet = json_decode($siparis->sepet);
		foreach ($_sepet as $items) {
			$items = (array)$items;
			$user_basket[] = array($items["urun_ad"], number_format($items["urun_fiyat"], 2, '.', ','), $items["adet"]);
		}

		$user_basket = base64_encode(json_encode($user_basket));

		############################################################################################



		## !!! Eğer bu örnek kodu sunucuda değil local makinanızda çalıştırıyorsanız
		## buraya dış ip adresinizi (https://www.whatismyip.com/) yazmalısınız. Aksi halde geçersiz paytr_token hatası alırsınız.
		$user_ip = get_ip();
		##

		## İşlem zaman aşımı süresi - dakika cinsinden
		$timeout_limit = "30";

		## Hata mesajlarının ekrana basılması için entegrasyon ve test sürecinde 1 olarak bırakın. Daha sonra 0 yapabilirsiniz.
		$debug_on =  odeme_ayar()->paytr_test_mode;

		## Mağaza canlı modda iken test işlem yapmak için 1 olarak gönderilebilir.
		$test_mode =  odeme_ayar()->paytr_test_mode;

		$no_installment	= 0; // Taksit yapılmasını istemiyorsanız, sadece tek çekim sunacaksanız 1 yapın

		## Sayfada görüntülenecek taksit adedini sınırlamak istiyorsanız uygun şekilde değiştirin.
		## Sıfır (0) gönderilmesi durumunda yürürlükteki en fazla izin verilen taksit geçerli olur.
		$max_installment = 0;

		$currency = "TL";

		####### Bu kısımda herhangi bir değişiklik yapmanıza gerek yoktur. #######
		$hash_str = $merchant_id . $user_ip . $merchant_oid . $email . $payment_amount . $user_basket . $no_installment . $max_installment . $currency . $test_mode;
		$paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $merchant_salt, $merchant_key, true));
		$post_vals = array(
			'merchant_id' => $merchant_id,
			'user_ip' => $user_ip,
			'merchant_oid' => $merchant_oid,
			'email' => $email,
			'payment_amount' => $payment_amount,
			'paytr_token' => $paytr_token,
			'user_basket' => $user_basket,
			'debug_on' => $debug_on,
			'no_installment' => $no_installment,
			'max_installment' => $max_installment,
			'user_name' => $user_name,
			'user_address' => $user_address,
			'user_phone' => $user_phone,
			'merchant_ok_url' => $merchant_ok_url,
			'merchant_fail_url' => $merchant_fail_url,
			'timeout_limit' => $timeout_limit,
			'currency' => $currency,
			'test_mode' => $test_mode
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		// XXX: DİKKAT: lokal makinanızda "SSL certificate problem: unable to get local issuer certificate" uyarısı alırsanız eğer
		// aşağıdaki kodu açıp deneyebilirsiniz. ANCAK, güvenlik nedeniyle sunucunuzda (gerçek ortamınızda) bu kodun kapalı kalması çok önemlidir!
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$result = @curl_exec($ch);

		if (curl_errno($ch))
			die("PAYTR IFRAME connection error. err:" . curl_error($ch));

		curl_close($ch);

		$result = json_decode($result, 1);

		if ($result['status'] == 'success') {
			$token = $result['token'];
		} else {
			die("PAYTR IFRAME failed. reason:" . $result['reason']);
		}
		#########################################################################

		$odeme_form =  '
			<script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
			<iframe src="https://www.paytr.com/odeme/guvenli/' . $token . '" id="paytriframe" frameborder="0" scrolling="no" style="width: 100%;"></iframe>
			<script>iFrameResize({},\'#paytriframe\');</script>
		
		';

		if (odeme_ayar()->paytr_komisyon != 0) {
			$odeme_form .= '<p class="text-danger">Sipariş tutarına ek ' . $tutar_pos_komisyon . '₺ pos komisyonu tahsil edilmektedir.</p>';
		}


		$veri = [
			'siparis' => $siparis,
			'sayfa_adi' => 'odeme/odeme_pos',
			'sayfa_title' => 'Ödeme Yap - ' . siteayar()->site_baslik,
			'odeme_form' => $odeme_form
		];

		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function paytr_kontrol()
	{

		if (!$_POST) {
			exit;
		}
		$post = $_POST;

		$merchant_key 	= odeme_ayar()->paytr_merchant_key;
		$merchant_salt	= odeme_ayar()->paytr_merchant_salt;

		$hash = base64_encode(hash_hmac('sha256', $post['merchant_oid'] . $merchant_salt . $post['status'] . $post['total_amount'], $merchant_key, true));
		if ($hash != $post['hash'])
			die('PAYTR notification failed: bad hash');
		###########################################################################

		$order_id = $post['merchant_oid'];


		$siparis = $this->db->get_where('siparisler', ['sipariskey' => $order_id]);
		if ($siparis->num_rows() == 0) {
			echo "OK";
			exit;
		}
		$siparis = $siparis->row();

		if ($post['status'] == 'success') {

			if ($this->basarili_islem_yap($siparis)) {
				
			} else {
				$mesaj = "Ödemeniz alındı. Site içsel hata oluştu (basarili_islem_yap). " . $siparis->id;
				$this->db->where("id", $siparis->id);
				$this->db->update("siparisler", array('pos_mesaj' => $mesaj));
				
			}
		} else {
			$mesaj = "Ödemeniz alındı. Site içsel hata oluştu. " . $siparis->id;
			$this->db->where("id", $siparis->id);
			$this->db->update("siparisler", array('pos_mesaj' => $mesaj));


			//$order_id = $post['merchant_oid'];
			//$veri = array('odeme_durum' => 'odeme_iptal');
			//$this->db->where(array('sipariskey' => $order_id));
			//$this->db->update('siparisler', $veri);

			//$this->load->model('Mail_model');
			//$this->Mail_model->siparis_durumu_guncellendi($order_id);
		}

		echo "OK";
		exit;
	}
	/* ------------------ */

	/* SHOPİER POS SİSTEMİ */
	public function shopier_start($siparis)
	{

		$adres		  = (object)json_decode($siparis->teslimat_adresi);

		$tutar_pos_komisyon = $siparis->tutar * odeme_ayar()->shopier_komisyon / 100;
		$siparis_tutar 		= $tutar_pos_komisyon + $siparis->tutar;


		$sipariskey = rand(1000000000, 9999999999);
		$this->db->where("id", $siparis->id);
		$this->db->update("siparisler", array('sipariskey' => $sipariskey));

		$shopier = new Shopier(odeme_ayar()->shopier_api_key,odeme_ayar()->shopier_api_secret, odeme_ayar()->api_url_index);

		$shopier->setBuyer([
			'id' => 0,
			'first_name' => $adres->isim,
			'last_name' => $adres->soyisim,
			'email' => $adres->eposta,
			'phone' => $adres->telefon
		]);

		$shopier->setOrderBilling([
			'billing_address' => $adres->adres . ' ' . $adres->il . ' ' . $adres->ilce,
			'billing_city' => $adres->il,
			'billing_country' => "tr",
			'billing_postcode' => "10000",
		]);

		$shopier->setOrderShipping([
			'shipping_address' => $adres->adres . ' ' . $adres->il . ' ' . $adres->ilce,
			'shipping_city' =>  $adres->il,
			'shipping_country' => "tr",
			'shipping_postcode' => "10000",
		]);

		echo $shopier->run($sipariskey, $siparis_tutar, base_url('odeme/kontrol/shopier'));
	}

	public function shopier_kontrol()
	{

		header('Set-Cookie: ' . session_name() . '=' . session_id() . '; SameSite=None; Secure');


		if (!$_POST) {
			exit;
		}

		$shopier = new Shopier(odeme_ayar()->shopier_api_key, odeme_ayar()->shopier_api_secret, odeme_ayar()->api_url_index);
		if ($shopier->verifyShopierSignature($_POST) && $_POST["status"] == "success") {
			$siparis_key = post('platform_order_id');
			$siparis = $this->db->get_where('siparisler', ['sipariskey' => $siparis_key]);
			if ($siparis->num_rows() == 0) {
				echo "siparisyok";
				exit;
			}
			if ($_POST["status"] == "success") {

				$siparis = $siparis->row();

				if ($siparis->siparis_durum != "odeme_bekliyor") {
					redirect(base_url());
				}

				if ($this->basarili_islem_yap($siparis)) {
					redirect(base_url("odeme/odeme-basarili/" . $siparis->sipariskey));
					exit;
				} else {
					$mesaj = "Ödemeniz alındı. Site içsel hata oluştu. " . $siparis->id;
					$this->db->where("id", $siparis->id);
					$this->db->update("siparisler", array('pos_mesaj' => $mesaj));

					redirect(base_url("odeme/odeme-basarisiz/" . $siparis->sipariskey));
					exit;
				}
			} else {
				redirect(base_url("odeme/odeme-basarisiz/" . $siparis->sipariskey));
			}
		}
	}
	/* ------------------ */

	/* SHOPİER POS SİSTEMİ */
	public function weepay_start($siparis)
	{
		$sipariskey = rand(1000000000, 9999999999);
		$this->db->where("id", $siparis->id);
		$this->db->update("siparisler", array('sipariskey' => $sipariskey));

		$tutar_pos_komisyon = $siparis->tutar * odeme_ayar()->weepay_komisyon / 100;
		$siparis_tutar 		= $tutar_pos_komisyon + $siparis->tutar;


		include("appty/libraries/weepay/weepayBootstrap.php");
		weepayBootstrap::initialize();

		//
		$options = new \weepay\Auth();
		$options->setBayiID(odeme_ayar()->weepay_bayi_id); // weepay tarafıdan verilen bayiId
		$options->setApiKey(odeme_ayar()->weepay_api_key); // weepay tarafıdan verilen apiKey
		$options->setSecretKey(odeme_ayar()->weepay_secret_key); // weepay tarafıdan verilen secretKey
		$options->setBaseUrl("https://api.weepay.co");

		//Request
		$request = new \weepay\Request\FormInitializeRequest();
		$request->setOrderId($sipariskey);
		$request->setIpAddress(get_ip());
		$request->setPrice($siparis_tutar);
		$request->setCurrency(\weepay\Model\Currency::TL);
		$request->setLocale(\weepay\Model\Locale::TR);
		$request->setDescription('Açıklama Alanı');
		$request->setCallBackUrl(base_url('odeme/kontrol/weepay?siparis=' . $siparis->id));
		$request->setPaymentGroup(\weepay\Model\PaymentGroup::PRODUCT);
		$request->setPaymentChannel(\weepay\Model\PaymentChannel::WEB);

		$adres		  = (object)json_decode($siparis->teslimat_adresi);



		$sipariskey = rand(1000000000, 9999999999);
		$this->db->where("id", $siparis->id);
		$this->db->update("siparisler", array('sipariskey' => $sipariskey));

		//Customer
		$customer = new \weepay\Model\Customer();
		$customer->setCustomerId(45); // Üye işyeri müşteri Id 
		$customer->setCustomerName($adres->isim); //Üye işyeri müşteri ismi 
		$customer->setCustomerSurname($adres->soyisim); //Üye işyeri müşteri Soyisim
		$customer->setGsmNumber($adres->telefon); //Üye işyeri müşteri Cep Tel
		$customer->setEmail($adres->eposta); //Üye işyeri müşteri ismi 
		$customer->setIdentityNumber("11111111111"); //Üye işyeri müşteri TC numarası
		$customer->setCity($adres->il); //Üye işyeri müşteri il
		$customer->setCountry("turkey"); //Üye işyeri müşteri ülke
		$request->setCustomer($customer);



		//Adresler
		// Fatura Adresi
		$BillingAddress = new \weepay\Model\Address();
		$BillingAddress->setContactName($adres->isim . ' ' . $adres->soyisim);
		$BillingAddress->setAddress($adres->adres);
		$BillingAddress->setCity($adres->il);
		$BillingAddress->setCountry("turkey");
		$BillingAddress->setZipCode("1111");
		$request->setBillingAddress($BillingAddress);

		//Kargo / Teslimat Adresi
		$ShippingAddress = new \weepay\Model\Address();
		$ShippingAddress->setContactName($adres->isim . ' ' . $adres->soyisim);
		$ShippingAddress->setAddress($adres->adres);
		$ShippingAddress->setCity($adres->il);
		$ShippingAddress->setCountry("turkey");
		$ShippingAddress->setZipCode("1111");
		$request->setShippingAddress($ShippingAddress);

		// Sipariş Ürünleri
		$Products = array();

		$firstProducts = new \weepay\Model\Product();
		$firstProducts->setName(siteayar()->site_baslik . ' Sipariş');
		$firstProducts->setProductId($siparis->id);
		$firstProducts->setProductPrice($siparis_tutar);
		$firstProducts->setItemType(\weepay\Model\ProductType::PHYSICAL);
		$Products[0] = $firstProducts;
		$request->setProducts($Products);
		$checkoutFormInitialize = \weepay\Model\CheckoutFormInitialize::create($request, $options);

		$arra = $checkoutFormInitialize;
		$formdurum = json_decode($arra->getRawResult());


		$odeme_form = '';

		if ($formdurum->status == "success") {
			$odeme_form =  "<div id='weePay-checkout-form' class='responsive'>" . $arra->getCheckoutFormData();

			if (odeme_ayar()->weepay_komisyon != 0) {
				$odeme_form .= '<p class="text-danger">Sipariş tutarına ek ' . $tutar_pos_komisyon . '₺ pos komisyonu tahsil edilmektedir.</p>';
			}
		} else {
			$odeme_form =  $formdurum;
		}

		$veri = [
			'siparis' => $siparis,
			'sayfa_adi' => 'odeme/odeme_pos',
			'sayfa_title' => 'Ödeme Yap - ' . siteayar()->site_baslik,
			'odeme_form' => $odeme_form
		];


		$this->load->view($this->siteayar->tema . '/index', $this->_extraData);
	}

	public function weepay_kontrol()
	{
		if (!$_POST) {
			exit;
		}

		$api_secret_key = odeme_ayar()->weepay_secret_key;

		/* posdan gelen sipariş numarası (id) */
		$siparis_id = $_GET['siparis'];

		$siparis = $this->db->get_where('siparisler', ['id' => $siparis_id]);
		if ($siparis->num_rows() == 0) {
			echo "siparisyok";
			exit;
		}
		$siparis = $siparis->row();

		if (!$_POST) {
			redirect(base_url());
		}

		if ($siparis->siparis_durum != "odeme_bekliyor") {
			redirect(base_url());
		}

		if ($_POST['status'] == 'success' && $_POST['isSuccessful'] != "False" && $_POST['paymentStatus'] != "false" && $_POST['secretKey'] == $api_secret_key) {
			if ($this->basarili_islem_yap($siparis)) {
				redirect(base_url("odeme/odeme-basarili/" . $siparis->sipariskey));
				exit;
			} else {
				$mesaj = "Ödemeniz alındı. Site içsel hata oluştu. " . $siparis->id;
				$this->db->where("id", $siparis->id);
				$this->db->update("siparisler", array('pos_mesaj' => $mesaj));

				redirect(base_url("odeme/odeme-basarisiz/" . $siparis->sipariskey));
				exit;
			}
		}


		$veri = [
			'sayfa_adi' => 'siparis',
			'sayfa_title' => siteayar()
		];
	}
	/* ------------------ */

	/* IYZICO POS SİSTEMİ */

	public function iyzico_start($siparis)
	{

		$sipariskey = rand(1000000000, 9999999999);
		$this->db->where("id", $siparis->id);
		$this->db->update("siparisler", array('sipariskey' => $sipariskey));


		include("appty/libraries/iyzipay/IyzipayBootstrap.php");
		IyzipayBootstrap::init();

		$option = new \Iyzipay\Options();
		$option->setApiKey(odeme_ayar()->iyzico_apikey);
		$option->setSecretKey(odeme_ayar()->iyzico_secretkey);
		if (odeme_ayar()->iyzico_test) {
			$option->setBaseUrl("https://sandbox-api.iyzipay.com");
		} else {
			$option->setBaseUrl("https://api.iyzipay.com");
		}

		$tutar_pos_komisyon = $siparis->tutar * odeme_ayar()->iyzico_komisyon / 100;
		$siparis_tutar 		= $tutar_pos_komisyon + $siparis->tutar;

		$adres		  = (object)json_decode($siparis->teslimat_adresi);

		$iyzico = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest(); // İyziPay Form tetiklemesi için gerekli bilgiler
		$iyzico->setLocale(\Iyzipay\Model\Locale::TR);
		$iyzico->setConversationId($sipariskey); //Benzersiz oluşturulması gereken ürün kodu
		$iyzico->setPrice($siparis_tutar); // Ürün fiyatı 
		$iyzico->setPaidPrice($siparis_tutar); // Ödenecek ürün fiyatı (burası çekim işleminde tetiklenecek alan)
		$iyzico->setCurrency(\Iyzipay\Model\Currency::TL); // Ödeme şeklini belirtmek için kullanılır
		$iyzico->setBasketId($sipariskey); // Sipariş kodu, ürün kodu geri dönüş olarak gelmektedir.
		$iyzico->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT); // Ürün bilgilerini tetiklemesi
		$iyzico->setCallbackUrl(base_url() . "odeme/kontrol/iyzico?siparis=$sipariskey"); // Formun oluşturması için kullanılan geri dönüş URL adresi

		/*
		'shipping_address' => $adres->adres . ' ' . $adres->il . ' ' . $adres->ilce,
			'shipping_city' =>  $adres->il,
			'shipping_country' => "tr",
			'shipping_postcode' => "10000",
		*/
		$buyer = new \Iyzipay\Model\Buyer(); // Müşteri bilgilerinin oluşturulması
		$buyer->setId("1"); // Müşteri bazındaki ID 
		$buyer->setName($adres->isim); // Müşteri Adı
		$buyer->setSurname($adres->soyisim); // Müşteri Soyadı
		$buyer->setGsmNumber($adres->telefon); // Müşteri Telefon Numarası
		$buyer->setEmail($adres->eposta); // test@123.com
		$buyer->setIdentityNumber("00000000000"); // Müşteri TC Kimlik Numarası (zorunluluk sistem sahibine ait.)
		$buyer->setLastLoginDate(date('Y-m-d H:i:s')); // Müşteri Son giriş
		$buyer->setRegistrationDate(date('Y-m-d H:i:s')); // Müşteri Sipariş (Kayıt) Tarihi
		$buyer->setRegistrationAddress($adres->adres); // Müşteri Sipariş (Kayıt) Adresi
		$buyer->setIp(get_ip()); // Müşteri IP Adresi
		$buyer->setCity($adres->il); // Müşteri İl
		$buyer->setCountry($adres->ilce); // Müşteri İlçe
		$buyer->setZipCode("16000"); // Müşteri Posta Kodu
		$iyzico->setBuyer($buyer); // Müşteri sipariş (Sepet, ürün) bilgileri tetikletme

		$shippingAddress = new \Iyzipay\Model\Address(); // Müşteri kargo bilgilerinin oluşturulması
		$shippingAddress->setContactName($adres->isim); // Müşteri Adı
		$shippingAddress->setCity($adres->il); // Müşteri İl
		$shippingAddress->setCountry($adres->ilce); // Müşteri İlçe
		$shippingAddress->setAddress($adres->adres); // Müşteri Adresi
		$shippingAddress->setZipCode("16000"); // Müşteri Posta Kodu
		$iyzico->setShippingAddress($shippingAddress); // Sipariş kargo bilgileri tetikletme

		$billingAddress = new \Iyzipay\Model\Address(); //Fatura bilgileri için istenilen bilgiler
		$billingAddress->setContactName($adres->isim); // Müşteri Adı
		$billingAddress->setCity($adres->il); // Müşteri İl
		$billingAddress->setCountry($adres->ilce); // Müşteri İlçe
		$billingAddress->setAddress($adres->adres); // Müşteri Adresi
		$billingAddress->setZipCode("16000"); // Müşteri Posta Kodu
		$iyzico->setBillingAddress($billingAddress); // Gerekli tetikleme

		/**
		 * Burada $firstBasketItem kendimizin tanımladığı bir değişken tetiklemesidir.
		 * 1 den fazla ürün ekleyebilirsiniz tek dikkat edilmesi gerekilen nokta
		 * basketItems[] arrayını düzgün oluşturarak ürün1,ürün2,ürün3 vb. set yapmanız
		 */

		$basketItems = array();
		$_sepet = json_decode($siparis->sepet);

		$firstBasketItem = new \Iyzipay\Model\BasketItem(); // Ürün listesi için gerekli tetiklemeler 
		$firstBasketItem->setId($siparis->id); // Benzersiz oluşturulan ürün kodu
		$firstBasketItem->setName("Sipariş " . $siparis->sipariskey); // Ürün adı
		$firstBasketItem->setCategory1("Siparişler"); // Ürün Kategorisi
		$firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
		$firstBasketItem->setPrice($siparis_tutar);

		$basketItems[] = $firstBasketItem;



		$iyzico->setBasketItems($basketItems);


		$checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($iyzico, $option); // gerekli ürün bilgileri ve ayarlar ile api tetikleme.

		$arra = $checkoutFormInitialize;
		$formdurum = json_decode($arra->getRawResult());

		$odeme_form = '';

		if ($formdurum->status == "success") {
			if (odeme_ayar()->iyzico_test) {
				$odeme_form = '
				<p class="text-black">
					<strong class="text-black"><span class="text-danger">TEST MODU AKTİF</span> Sandbox Test Ortamı Kart Bilgileri</strong><br>
					Ad Soyad: <strong class="text-black">TAYSI WEB </strong><br>
					Kart Numarası: <strong class="text-black">5890040000000016 </strong><br>
					Ay / Yıl: <strong class="text-black">12/25</strong><br>
					CVC: <strong class="text-black">123 </strong>
				</p>
				';
			}
			$odeme_form .=  "<div id='iyzipay-checkout-form' class='responsive'>" . $arra->getCheckoutFormContent() . "</div>";

			if (odeme_ayar()->iyzico_komisyon != 0) {
				$odeme_form .= '<p class="text-danger text-center pt-3">Sipariş tutarına ek ' . $tutar_pos_komisyon . '₺ komisyon tahsil edilmektedir.</p>';
			}
		} else {
			$odeme_form =  "[IYZICO] " . $formdurum->errorMessage;
		}

		$veri = [
			'tutar_pos_komisyon' => $tutar_pos_komisyon,
			'siparis' => $siparis,
			'sayfa_adi' => 'odeme/odeme_pos',
			'sayfa_title' => 'Ödeme Yap - ' . siteayar()->site_baslik,
			'odeme_form' => $odeme_form
		];


		$this->load->_view($this->siteayar->tema . '/index', $this->_extraData, $veri);
	}

	public function iyzico_kontrol()
	{

		if (!$_POST) {
			exit;
		}

		/* posdan gelen sipariş numarası (id) */
		$siparis_id = $_GET['siparis'];

		$siparis = $this->db->get_where('siparisler', ['sipariskey' => $siparis_id]);
		if ($siparis->num_rows() == 0) {
			echo "siparisyok";
			exit;
		}

		$siparis = $siparis->row();

		if (!$_POST) {
			redirect(base_url());
		}

		if ($siparis->siparis_durum != "odeme_bekliyor") {
			redirect(base_url());
		}


		$token = $this->input->post('token'); // Ödeme sonrası dönen token

		if (!empty($token)) {

			include("appty/libraries/iyzipay/IyzipayBootstrap.php");
			IyzipayBootstrap::init();
			$option = new \Iyzipay\Options();

			$option->setApiKey(odeme_ayar()->iyzico_apikey);
			$option->setSecretKey(odeme_ayar()->iyzico_secretkey);
			if (odeme_ayar()->iyzico_test) {
				$option->setBaseUrl("https://sandbox-api.iyzipay.com");
			} else {
				$option->setBaseUrl("https://api.iyzipay.com");
			}

			$return = new \Iyzipay\Request\RetrieveCheckoutFormRequest(); //Token derlemesi yapılıp işlemin onaylanıp onaylanmadığını bildirir
			$return->setLocale(\Iyzipay\Model\Locale::TR);
			$return->setToken($token);

			$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($return, $option);

			$request = $checkoutForm;

			if ($request->getPaymentStatus() === "SUCCESS") {

				if ($this->basarili_islem_yap($siparis)) {
					redirect(base_url("odeme/odeme-basarili/" . $siparis->sipariskey));
					exit;
				} else {
					$mesaj = "Ödemeniz alındı. Site içsel hata oluştu. " . $siparis->id;
					$this->db->where("id", $siparis->id);
					$this->db->update("siparisler", array('pos_mesaj' => $mesaj));

					redirect(base_url("odeme/odeme-basarisiz/" . $siparis->sipariskey));
				}
			} else {
				redirect(base_url("odeme/odeme-basarisiz/" . $siparis->sipariskey));
			}
		}
	}

	/* ------------------ */
	public function basarili_islem_yap($siparis)
	{

		$_sepet                     = json_decode($siparis->sepet);
		$_urunler                   = (array)json_decode($siparis->urun_verileri);

		$urun_veri = array();
		foreach ($_sepet as $sepet_id => $sepet) {

			$urun = json_decode($_urunler[$sepet->urun_id]);

			if ($urun->urun_tipi == 'veri') {

				$___adet = $sepet->adet;
				$___urun_id = $sepet->urun_id;
				$verilen_veriler = array("_-_-_taysiweb_-_-__");

				for ($ii = 0; $ii < $___adet; $ii++) {

					$db_veri = $this->db
						->order_by('RAND()')
						->limit(1)
						->where_not_in('veri', $verilen_veriler)
						->where(['urun_id' => $___urun_id, 'durum' => 0, 'siparis_id' => 0])
						->get('urun_digital')
						->row();

					if (isset($db_veri)) {

						$urun_veri[$___urun_id][] = array(
							'veri_id' 	=> $db_veri->id,
							'veri' 		=> $db_veri->veri,
						);
						$verilen_veriler[] = $db_veri->veri;

						$this->db->where('id', $db_veri->id);
						$this->db->update('urun_digital', ['siparis_id' => $siparis->id, 'durum' => 1]);
					} else {

						$urun_veri[$___urun_id][] = array(
							'veri_id' 	=> 0,
							'veri' 		=> "stok_yok",
						);
					}
				}
			}

			$yeni_stok = intval($urun->stok) - intval($sepet->adet);
			$this->db->where('urun_id', $sepet->urun_id);
			$this->db->update('urunler', ['stok' => $yeni_stok]);
		}

		$update_siparis_array = array(
			'siparis_durum' => 'odeme_basarili',
			'urun_digital' => json_encode($urun_veri)
		);

		$this->db->where(array('id' => $siparis->id));
		$this->db->update('siparisler', $update_siparis_array);

		$this->load->model('Bildir_model');

		if (bildirim_ayar()->sms_bildirim) {
			$this->Bildir_model->sms_siparis_durumu_bildir($siparis->id);
		}
		if (bildirim_ayar()->mail_bildirim) {
			$this->Bildir_model->mail_siparis_durumu_bildir($siparis->id);
		}

		return true;
	}
}
