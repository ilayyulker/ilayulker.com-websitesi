<?php
defined('BASEPATH') OR exit('Doğrudan erişime izin verilmiyor');

$active_group = 'default';
$query_builder = TRUE;
  
$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'ilayulker_ilay',
	'password' => 'gQwXsfVoVH$l',
	'database' => 'ilayulker_ilay',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
