<?php
require $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require_once 'PHPMailer/class.phpmailer.php';
require_once "PHPMailer/class.smtp.php";
use Spipu\Html2Pdf\Html2Pdf;
class mainControl{
	function conexion_db() {  
		$dsn = 'mysql:dbname=' . _namedb . ';host=' . _hostdb;  
		try {  
			$database = new PDO($dsn,_userdb,_passworddb);  
		} catch (PDOException $e) {  
			$database = 'Falló la conexión: ' . $e->getMessage();  
		}
		return $database;
	}
	function callApiREST($p){
		$result=null;		
		$headers = array();
		$headers[] = 'Cache-Control: no-cache';
		$headers[] = 'Content-Type: '.$p['contentType'];//comun application/json
		try {
			$curl = curl_init($p['url']);
			curl_setopt($curl, CURLOPT_URL, $p['url']);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			if(isset($p['queryString'])){
				curl_setopt($curl, CURLOPT_POST,true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($p['queryString']));
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			} 
			//ejecuto el request
			$resultNP = curl_exec($curl);
			$result = json_decode($resultNP,true);
			if(is_array($result)){
				return $result;	
			} else {
				return array('ERROR'=>'Server no respondio con JSON: '.$resultNP.', URL: '. $p['url']);
			}
			curl_close($ch);
		} catch (Exception $e) {
			return  array('ERROR'=>'Excepción capturada: '.$e->getMessage());
		}
	}
	function secure($access){	
		$return=false;
		$roles=array();
		if(isset($_SESSION["login"]["rules"])){
			$roles=explode("|",$_SESSION["login"]["rules"]);
		}
		foreach($roles as $value){
			if($value==$access){
				$return=true;
				break;
			}
		}
		return $return;
	}
	function get_mime_type($file){
		$mime_types = array(
				"pdf"=>"application/pdf"
				,"exe"=>"application/octet-stream"
				,"zip"=>"application/zip"
				,"docx"=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document"
				,"doc"=>"application/msword"
				,"xls"=>"application/vnd.ms-excel"
				,"xlsx"=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
				,"ppt"=>"application/vnd.ms-powerpoint"
				,"pptx"=>"application/vnd.openxmlformats-officedocument.presentationml.presentation"
				,"gif"=>"image/gif"
				,"png"=>"image/png"
				,"jpeg"=>"image/jpg"
				,"jpg"=>"image/jpg"
				,"mp3"=>"audio/mpeg"
				,"wav"=>"audio/x-wav"
				,"mpeg"=>"video/mpeg"
				,"mpg"=>"video/mpeg"
				,"mpe"=>"video/mpeg"
				,"mov"=>"video/quicktime"
				,"avi"=>"video/x-msvideo"
				,"3gp"=>"video/3gpp"
				,"css"=>"text/css"
				,"jsc"=>"application/javascript"
				,"js"=>"application/javascript"
				,"php"=>"text/html"
				,"htm"=>"text/html"
				,"html"=>"text/html"
		);
		$extension = strtolower(end(explode('.',$file)));
		return $mime_types[$extension];
	}
	function mensaje($title,$body,$logo){
		$mensaje='<!DOCTYPE html>
		<html lang="es">
			<body>
				<div style="width:600px; margin:0 auto; border:1px solid #0371C0; border-radius:15px; background-color:#FFFFFF; padding:15px;">
						<img src="'.$logo.'" />
						<h2>'.$title.'</h2>
						<div style="text-align:justify;">'.$body.'</div>
				</div>
				<div style="width:600px; text-align:justify; margin:0 auto;">
					<p>Este mensaje fue generado automáticamente desde XML2PDF – NIRVARIA, Este mensaje de correo electrónico es para uso exclusivo de los destinatarios previstos y puede contener información confidencial y privilegiada. Se prohíbe cualquier revisión no autorizada, uso, divulgación o distribución. Si usted no es el destinatario previsto, comuníquese con el remitente por correo electrónico de respuesta y destruya todas las copias del mensaje original.</p>
				</div>
			</body>
		</html>';
		return $mensaje;
	}
	function sendMail($params){
		$mail = new PHPMailer(true);
		//$mail->SMTPDebug = 2;  
		$mail->isSMTP(); // Set mailer to use SMTP
		$mail->Host = $params['config']['servidor'];  // Specify main and backup SMTP servers
		$mail->SMTPAuth = $params['config']['autenticacion']; // Enable SMTP authentication
		$mail->Username = $params['config']['usuario']; // SMTP username
		$mail->Password = $params['config']['contrasena']; // SMTP password
		$mail->SMTPSecure = $params['config']['seguridad']; // Enable TLS encryption, `ssl` also accepted
		$mail->Port = $params['config']['puerto']; // TCP port to connect to

		$mail->From=$params['config']['correoEnvia'];
		$mail->FromName = $params['config']['nombreEnvia']; //envia
		$mail->addAddress($params['correoDestinatario']); // destinatario
		$mail->addReplyTo($params['config']['correoResponder']); //eveto responder

		if(isset($params['adjunto'])){
			//$mail->addAttachment($params['pathAdjunto']);         // Add attachments enviar path
			$mail->AddStringAttachment(base64_decode($params['adjunto']['base64']), $params['adjunto']['fileName'], 'base64', $params['adjunto']['mimeType']);
		}
		
		$mail->IsHTML(true);                                  // Set email format to HTML

		$mail->Subject = $params['asunto']; //asunto del correo
		$mail->MsgHTML($params['mensaje']);
		//$mail->Body    = $params['mensaje'];// mensaje a enviar

		if(!$mail->Send()) {
			$return = false;
			//echo 'Message could not be sent.';
			//echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			$return = true;
		}
		return $return;
	}
	function doPDF($html='',$body=false,$debug=false,$paper_1='A4',$paper_2='P'){  
		//('P','A4','en', false, 'UTF-8', array(mL, mT, mR, mB))
		$html2pdf = new HTML2PDF($paper_2,$paper_1,'es', true, 'UTF-8', array(5, 5, 5, 5));
		if($body){ 
			$content=' 
			<!doctype html>
			<head>
			<style type="text/css">';
			$content.=file_get_contents('../../css/extrass.css');
			$content.='</style>
			</head>
			<html> 
			<body>' 
				.$html. 
			'</body> 
			</html>'; 
		} else {
			$content=$html;
		}
		 
		if( $content!='' ){         
			//Creamos el pdf 
			if($debug==false) {
    			$html2pdf->pdf->SetDisplayMode('fullpage');
				$html2pdf->WriteHTML($content);
				$html2pdf->pdf->SetAuthor("Nirvaria");
				$html2pdf->pdf->SetCreator("Nirvaria");
    			$source=$html2pdf->Output('temp.pdf', 'S');
			} else if($debug==true) {
				$source=$content;
			}
		} 
		return $source;
	}  
	function encrypt_decrypt($action, $string) {
		$output = false;
		$encrypt_method = "AES-256-CBC";
		$secret_key = '6167726f64617461';
		$secret_iv = '6e69727661726961';
		// hash
		$key = hash('sha256', $secret_key);
		
		// iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		if ( $action == 'encrypt' ) {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else if( $action == 'decrypt' ) {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}
		return $output;
	}
	function login($pdo,$userName,$password){
		$return=array('code'=>0,'msj'=>'sin proceso!','user'=>array());
		// Comprobar que el usuario esta autorizado a entrar
		$query = "select * ";
		$query .= "from usuarios where email='".$userName."' and password='".$password."' and status=1";
		if(is_object($pdo)){
			try { 
				$consulta = $pdo->query($query);
				if($consulta){
					$numRows=$consulta->rowCount();
					if ($numRows> 0) {
						$return['user'] = $consulta->fetch(PDO::FETCH_ASSOC); 
						$return['code']=1;
						$return['msj']='usuario correcto!';
					} else {
						$return['msj']="El usuario o contraseña son incorrectos, si aún no ha verificado su correo busque en su bandeja de entrada (correos no deseados y o spam) el mensaje de verificación. Ponte en contacto con soporte al correo soporte@nirvaria.com";
					}
				} else {
					$return['msj']='al parecer el query fallo: '.$query;
				}
			} catch (PDOException $e) { 
				$return['msj']='Falló la conexión: '.$e->getMessage();
			}
		} else {
			$return['msj'] = "No hay una conexion con el servidor activa!";
		}
		return $return;
	}
	function getModulosSys($pdo){
		$return=array();
		// Comprobar que el usuario esta autorizado a entrar
		$query="select id_modulo_sis, permisos, titulo from sis_modulos where estado='on'";
		if(is_object($pdo)){
			try { 
				$consulta = $pdo->query($query);
				if($consulta){
					$numRows=$consulta->rowCount();
					if ($numRows>0) {
						$tempOptGen = $consulta->fetchAll(PDO::FETCH_ASSOC); 
						foreach ($tempOptGen as $key => $value) {
							$return[$value['permisos']]=$value;
						}
					} 
				} else {
					$return='al parecer el query fallo: '.$query;
				}
			} catch (PDOException $e) { 
				$return='Falló la conexión: '.$e->getMessage();
			}
		} else {
			$return = "No hay una conexion con el servidor activa!";
		}
		return $return;
	}
	function getOpt($pdo,$meta){
		$return='{}';
		// Comprobar que el usuario esta autorizado a entrar
		$query="select value from opt_gen where tipo='option' and meta='".$meta."' order by idOpt desc limit 1";
		if(is_object($pdo)){
			try { 
				$consulta = $pdo->query($query);
				if($consulta){
					$numRows=$consulta->rowCount();
					if ($numRows> 0) {
						$tempOptGen = $consulta->fetch(PDO::FETCH_ASSOC); 
						$return = json_decode($tempOptGen['value']);
					} else {
						$return=json_decode('{}');
					}
				} else {
					$return='al parecer el query fallo: '.$query;
				}
			} catch (PDOException $e) { 
				$return='Falló la conexión: '.$e->getMessage();
			}
		} else {
			$return = "No hay una conexion con el servidor activa!";
		}
		return $return;
	}
	/** CRUD QUERYS */
	function getQuery($crud,$table,$data,$pdo){
		$query=null;
		$structure=$this->getStructureTable($pdo,$table);
		if($structure['code']==1){
			switch ($crud) {
				case 'create':
					$query=$this->queryCreate($table,$structure['table'],$data);
					break;
				case 'read':
					$query="Lectura debes de crear tu propio query";
					break;
				case 'update':
					$query=$this->queryUpdate($table,$structure['table'],$data);
					break;
				case 'delete':
					$query="i es igual a 2";
					break;
			}
		} else {
			$query=$structure['msj'];
		}
		return $query;
	}
	function getStructureTable($pdo,$table){
		$object=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$query="DESCRIBE ".$table;
		if($pdo!=null){
			try { 
				$consulta = $pdo->query($query);
				if($consulta){
					$numRows = $consulta->rowCount();
					if ($numRows>0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);  
						$object=array('code'=>1,'msj'=>'OK','table'=>array());
						foreach ($filas as $key => $value) {
							$object['table'][] = $value;
						}
					} else {
						$object = array('code'=>0,'msj'=>'No se ha encontrado la tabla.'); 
					}
				} else {
					$object = array('code'=>0,'msj'=>'Al parecer el query fallo: '.$query);  
				}
			} catch (PDOException $e) { 
				$object = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$object = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		return $object;
	}
	function queryCreate($table,$structure,$data){
		$query="INSERT INTO ".$table." (";
		$insert="(";
		foreach ($structure as $key => $value) {
			if($key>0){
				if(isset($data[$value['Field']])){
					if($key>1){ $query.=","; $insert.=","; }
					$query.=$value['Field'];
					$insert.="'".$data[$value['Field']]."'";
				}
			}
		}
		$query.=") VALUES "; 
		$insert.=")";
		return utf8_encode($query.$insert);
	}
	function queryUpdate($table,$structure,$data){
		$queryInit="UPDATE ".$table." SET ";
		$queryData=null;
		$where=null;
		foreach ($structure as $key => $value) {
			if($value['Extra']=='auto_increment'){
				$where=" WHERE ".$value['Field']."=".$data[$value['Field']];
			} else {
				if(isset($data[$value['Field']])){
					if(strlen($queryData)>0){ $queryData.=","; }
					$queryData.=$value['Field']."='".$data[$value['Field']]."'";	
				}
			}
			
		}
		return utf8_encode($queryInit.$queryData.$where);
	}
}
?>