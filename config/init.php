<?php
//iniciando cockies de sesion
session_start();
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST');
date_default_timezone_set('America/Guatemala');
error_reporting(E_ALL);
ini_set('display_errors', 1);
//cargando configuracion de mysql
include_once($_SERVER['DOCUMENT_ROOT'].'/config/globals.php');
//setenado variable parametros
if($_SERVER['REQUEST_METHOD']=='GET'){
	$_parametros=$_REQUEST;
} else if($_SERVER['REQUEST_METHOD']=='POST'){
	/** esta version solo acepta query string mime type application/json */
	$queryString = file_get_contents('php://input');
	$_parametros = json_decode($queryString,true);
}
/** asignando directorios */
if(isset($_parametros['module']) and strlen($_parametros['module'])>0){
	$_parametros['pathModulo']='/modulos/'.$_parametros['module'].'/';
	$_parametros['pathViews']='/modulos/'.$_parametros['module'].'/views/';
	$_parametros['includes']=array();
} else {
	$_parametros['pathModulo']='/';
	$_parametros['pathViews']='/views/';
	$_parametros['includes']=array();
}
//asignando metodo y vista por defecto 
if(!isset($_parametros['method'])){
	$_parametros['method']='showView';
	$_parametros['view']='init';
}
//include de las clases
include_once($_SERVER['DOCUMENT_ROOT'].'/mainClasses/PHPMailer/class.phpmailer.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/mainClasses/PHPMailer/class.smtp.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/mainClasses/mainControl.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/mainClasses/controller.php'); 
include_once($_SERVER['DOCUMENT_ROOT'].$_parametros['pathModulo']."class.php");
if(file_exists($_SERVER['DOCUMENT_ROOT'].$_parametros['pathModulo']."rules.php")){
	include_once($_SERVER['DOCUMENT_ROOT'].$_parametros['pathModulo']."rules.php");
}
//setenado variables globales
$APICore=new APICore;
$classMain=new mainControl;
$phpMailer=new PHPMailer;
$conexionDB=$classMain->conexion_db();
//seteando variables de fecha para el dia actual
if($_tsIni==0){ $_tsIni=mktime(0,0,0,date('m'),date('d'),date('Y')); }
if($_tsFin==0){ $_tsFin=mktime(23,59,59,date('m'),date('d'),date('Y')); } 
?>