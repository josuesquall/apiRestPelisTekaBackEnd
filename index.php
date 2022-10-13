<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/config/init.php');
$APICore->procesarLLamada($_parametros,$conexionDB,$classMain,_smtpDefault);
?>