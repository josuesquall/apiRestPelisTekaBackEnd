<?php
/** declara constantes de configuracion */
define("_domain", "https://munitest.nirvaria/");
define("_titleWeb", "Muni-Test-Pelis");
define("_hostdb", "localhost");
define("_namedb", "pelisteka");
define("_userdb", "root");
define("_passworddb", "");
define("_smtpDefault",array(
	'servidor'=>'mail.nirvaria.com',
	'autenticacion'=>true,
	'usuario'=>'noreply@nirvaria.com',
	'contrasena'=>'Makoto@21',
	'seguridad'=>'',
	'puerto'=>'587',
	'correoEnvia'=>'noreply@nirvaria.com',
	'nombreEnvia'=>'XML2PDF Nirvaria',
	'correoResponder'=>'soporte@nirvaria.com'
));
/** Basic Authorization APIRest */
define("_authorization", "");
define("_privateKey", "");
define("_publicKey", "");
/** uso global */
global $APICore;
global $classMain;
global $phpMailer;
global $conexionDB;
global $rules;
$rules=array();
/** definir una variable global para la HttpRequest */
global $_parametros;
/** variables globales filtros data */
global $_tsIni;//timeStamp de inicio
$_tsIni=0;
global $_tsFin;//timeStam de Fin
$_tsFin=0;
/** variable Login de Usuario */
$_SESSION["login"]=array();
?>