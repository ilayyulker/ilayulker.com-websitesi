<?php

defined('BASEPATH') OR exit('Doğrudan erişime izin verilmiyor');

$autoload['packages'] = array();
$autoload['libraries'] = array("database", "upload", "session", "form_validation");
$autoload['drivers'] = array();
$autoload['helper'] = array("url","date","text","security", "tytools_helper", "cookie_helper", "shopier_helper");
$autoload['config'] = array();
$autoload['language'] = array();

$autoload['model'] = array(
    "Siparis_model",
    "Market_model",
    "Kupon_model", 
    "Urun_model",
    "Mail_model",
    "Sepet_model",
    "Uye_model",
    "Bildir_model"
    
);


