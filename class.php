<?php  
class APICore extends Controller { 
	private $_method = null;
	private $_parametros = null;
	private $_database=null;
	private $_code=200;
	private $_classMain=null;
	private $_smtpDefault=null;

	public function __construct() {  
		parent::__construct();   
	} 

	private function File2B64($file,$dir){
		$base64=null;
		$path = $_SERVER['DOCUMENT_ROOT']."/modulos/".$dir.$file;
		$type = pathinfo($path, PATHINFO_EXTENSION);
		$data = file_get_contents($path);
		
		$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
		return $base64;
	}
	private function B642File($base64,$modulo,$path,$nFile,$ext){
		$pathF=$_SERVER['DOCUMENT_ROOT']."/modulos/";
		$pathS=$path.$nFile.$ext;
		$content = base64_decode($base64);
		$file = fopen($pathF.$modulo.$pathS, "wb");
		if(fwrite($file, $content)==false){
			$pathS='';
		}
		fclose($file);
		return $pathS;
	}
	private function authorization($autho){
		$return=true; //default false
		if (!empty($autho)) {    
			if(strlen(_privateKey)>0 and strlen(_publicKey)>0){
				$base64=base64_encode(_publicKey.":"._privateKey);
				$autho=explode(' ',$autho);
				if($autho[1]==$base64){
					$return=true; 
				}
			} 
		} 
		return $return; 
	}
	public function procesarLLamada($_parametros,$conexionDB,$classMain,$smtpConfig) { 
		$this->_method = $_parametros['method']; 
		$this->_parametros= $_parametros;
		$this->_database=$conexionDB;
		$this->_classMain=$classMain;
		$this->_smtpDefault=$smtpConfig;
		if($this->authorization(_authorization)){
			if(!is_array($_parametros)){
				$_parametros=json_decode($_parametros, true);
			}
			if ((int) method_exists($this, $this->_method) > 0) {  
				if (count($_parametros) > 0) {  
					call_user_func(array($this, $this->_method), $this->_parametros);  
				} else {  
					call_user_func(array($this, $this->_method));  
				} 
			}  else { 
				$this->mostrarRespuesta($this->devolverError(0), 200); 
			}
		} else {
			$this->mostrarRespuesta($this->devolverError(3), 400); 	
		}
	}

	private function login(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		if($this->_database!=null){
			$json=$this->_classMain->login($this->_database,$this->_parametros['email'],$this->_classMain->encrypt_decrypt('encrypt',$this->_parametros['password']));
		} else {
			$json['msj']='No se encontro una conexion con la base de datos establecida.';
		}
		$this->mostrarRespuesta($json,$code);
	}
	private function singin(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		//encriptando contraseña
		$this->_parametros['password']=$this->_classMain->encrypt_decrypt('encrypt',$this->_parametros['password']);
		$this->_parametros['status']=1;
		if($this->verifyEmail($this->_parametros['email'])){
			$query=$this->_classMain->getQuery('create','usuarios',$this->_parametros,$this->_database);
			if($this->_database!=null){
				$json=array();
				try { 
					$consulta = $this->_database->query($query);
					if($consulta){
						$json = array('code'=>1,'msj'=>'Usuario creado exitosamente!'); 
						$mensaje='<p>Bienvenido a nuestra plataforma Pelisteka, gracias por confiar en nuestros servicios, antes de continuar, necesitamos que confirmes tu correo electrónico haciendo click en el siguiente link:</p>';
						$mensaje.='<p><a href="https://pelisteka.nirvaria/?md='.base64_encode($this->_database->lastInsertId()).'">Confirmar esta direccion de correo '.$this->_parametros['email'].'!</a></p>';
						$mensaje.='<p>Al confirmar tu correo electrónico, quedara activa tu cuenta.</p>';
						$mensaje.='<p>Si tienes algún problema no dudes en contactarnos mediante el correo electrónico soporte@nirvaria.com</p>';
						$correo=array(
							'config'=>$this->_smtpDefault,
							'correoDestinatario'=>$this->_parametros['email'],
							'asunto'=>'Confirmacion de Correo - Pelisteka',
							'mensaje'=>''
						);
						$correo['mensaje']=$this->_classMain->mensaje('Confirmacion de Correo Electronico',$mensaje,'https://xml2pdf.nirvaria.com/views/assets/img/xml2pd500.png');
						$this->_classMain->sendMail($correo);
					} else {
						$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
					}
				} catch (PDOException $e) { 
					$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
				}
			} else {
				$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
			}
		} else {
			$json=array('code'=>0,'msj'=>'El correo ya estan registrados en el sistema!');
		}
		$this->mostrarRespuesta($json,200);
	}
	private function getCatalogo(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.','movies'=>array());
		$code=200;
		$query="SELECT *";
		$query.=",(SELECT `getScore`(movies.idMovie)) as score";
		if(isset($this->_parametros['idUser']) and $this->_parametros['idUser']>0){
			$query.=",(SELECT count(*) from favorites where idMovie=movies.idMovie and idUser=".$this->_parametros['idUser'].") as favorite";
		}
		$query.=" FROM movies WHERE 1";
		if(isset($this->_parametros['title']) and strlen($this->_parametros['title'])>0){
			$query.=" AND metaData LIKE '%".$this->_parametros['title']."%'";
		}
		if(isset($this->_parametros['limit']) and isset($this->_parametros['page'])){
			$inicio=$this->_parametros['limit']-($this->_parametros['page']*$this->_parametros['limit']);
			$fin=($this->_parametros['page']*$this->_parametros['limit']);
			$query.=" order by id desc limit ".$inicio.", ".$fin;
		}
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow> 0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC); 
						$json=array(); 
						foreach ($filas as $key => $value) {
							$value['metaData']=json_decode($value['metaData'],true,512,JSON_INVALID_UTF8_SUBSTITUTE);
							$json[] = $value;
						}
					} else {
						$json=$this->importCatalogo();
					}
				} else {
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		
		$this->mostrarRespuesta($json,$this->_code);
	}
	private function importCatalogo(){
		$json=array('code'=>0,'msj'=>'Error solicitando Importacion!','movies'=>array());
		$dataSend=array();
		$dataSend['contentType']='application/json';
		$dataSend['url']="https://api.themoviedb.org/3/movie/popular?api_key=425c2727ef8a53c0a2f20ab2584db89f&page=".$this->_parametros['page'];
		$resultCert=$this->_classMain->callApiREST($dataSend);
		if(is_array($resultCert) && !isset($resultCert['ERROR'])){
			foreach ($resultCert as $key => $value) {
				$movieSave=$this->saveMovie($value);
				if($movieSav>0){
					$json['code']=1;
					$json['movies'][]=array('idMovie'=>$movieSav,'id'=>$value['id'],'metaData'=>$value);
				}
				
			}
		} else {
			$json['msj'].='Error solicitando importacion!'.$resultCert['ERROR'];
		}
		return $json;
	}
	private function getComments(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		$query="SELECT *";
		$query.=",(SELECT name FROM usuarios where idUser=comments.idUser) as nameUser";
		$query.=" FROM comments WHERE idMovie=".$this->_parametros['idMovie'];
		$query.=" order by ts desc";
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				
				if($consulta){
					$json=array();
					$numRow=$consulta->rowCount();
					if ($numRow> 0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);  
						
						foreach ($filas as $key => $value) {
							$value['text']=utf8_decode($value['text']);
							$value['ts']=date('d/m/Y H:s',$value['ts']);
							$json[] = $value;
						}
					} else {
						$json[]=array("text"=>"no hay comentarios","nameUser"=>"---","ts"=>0);
					}
				} else {
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		
		$this->mostrarRespuesta($json,$this->_code);
	}
	private function getFavorites(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.','movies'=>array());
		$code=200;
		$query="SELECT *";
		$query.=",(SELECT `getScore`(movies.idMovies)) as score";
		$query.=" FROM movies";
		$query.=" WHERE idMovies=any(SELECT idMovie FROM favorites where idUser=".$this->_parametros['idUser'].")";
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow> 0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);  
						$json['msj']='registros encontrados '.$numRow;
						foreach ($filas as $key => $value) {
							$value['metaData']=json_decode($value['metaData'],true);
							$json['movies'][] = $value;
						}
					} else {
						$json=$this->importCatalogo();
					}
				} else {
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		
		$this->mostrarRespuesta($json,$this->_code);
	}


	private function sendSmtp(){
        $json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		//$this->_phpMailer->SMTPDebug = 2; // Enable verbose debug output
		$this->_phpMailer->isSMTP(); // Set mailer to use SMTP
		$this->_phpMailer->Host = $_parametros['config']['servidor'];  // Specify main and backup SMTP servers
		$this->_phpMailer->SMTPAuth = $_parametros['config']['autenticacion']; // Enable SMTP authentication
		$this->_phpMailer->Username = $_parametros['config']['usuario']; // SMTP username
		$this->_phpMailer->Password = $_parametros['config']['contrasena']; // SMTP password
		$this->_phpMailer->SMTPSecure = $_parametros['config']['seguridad']; // Enable TLS encryption, `ssl` also accepted
		$this->_phpMailer->Port = $_parametros['config']['puerto']; // TCP port to connect to

		$this->_phpMailer->From=$_parametros['config']['correoEnvia'];
		$this->_phpMailer->FromName = $_parametros['config']['nombreEnvia']; //envia
		$this->_phpMailer->addAddress($_parametros['correoDestinatario']); // destinatario
		$this->_phpMailer->addReplyTo($_parametros['config']['correoResponder']); //eveto responder

		if(isset($_parametros['adjunto'])){
			//$this->_phpMailer->addAttachment($_parametros['pathAdjunto']); 
			$this->_phpMailer->AddStringAttachment($_parametros['adjunto']['base64'], $_parametros['adjunto']['fileName'], 'base64', $_parametros['adjunto']['mimeType']);
		}
		
		$this->_phpMailer->IsHTML(true);                                  // Set email format to HTML

		$this->_phpMailer->Subject = $_parametros['asunto']; //asunto del correo
		$this->_phpMailer->MsgHTML($_parametros['mensaje']);
		//$this->_phpMailer->Body    = $_parametros['mensaje'];// mensaje a enviar

		if(!$this->_phpMailer->Send()) {
			$json=array('code'=>0,'msj'=>'No se ha logrado enviar el correo.'.$this->_phpMailer->ErrorInfo);
		} else {
			$json=array('code'=>1,'msj'=>'El correo fue enviado con exito.');
		}
		$this->mostrarRespuesta($json,200);
    }
	private function verifyEmail($email){
		$res=true;
		$query="select idUser from usuarios where email='".$email."'";
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow> 0) {
						$res=false;
					} 
				} 
			} catch (PDOException $e) { 
				$res=false;
			}
		} 
		return $res;
	}
	private function verifyMovie($id){
		$res=true;
		$query="select idMovie from movies where id=".$id;
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow> 0) {
						$res=false;
					} 
				} 
			} catch (PDOException $e) { 
				$res=false;
			}
		} 
		return $res;
	}
	private function sendSupport(){
		$json=array('code'=>1,'msj'=>'Correo Enviado Exitosamente.');
		$mensaje='<p>Un cliente de XML2PDF esta pidiendo soporte:</p>';
		$mensaje.='<p>Datos del cliente:'.$_parametros['user'].'</p>';
		$mensaje.='<p>Tipo Soporte:'.$_parametros['tipo'].'</p>';
		$mensaje.='<p>Descripcion:'.$_parametros['descripcion'].'</p>';
		$correo=array(
			'config'=>$this->_smtpDefault,
			'correoDestinatario'=>'soporte@nirvaria.com',
			'asunto'=>'Soporte XML2PDF',
			'mensaje'=>''
		);
		$correo['config']['correoResponder']=$this->_session['login']['email'];
		$correo['mensaje']=$this->_classMain->mensaje('Soporte XML2PDF',$mensaje,'https://xml2pdf.nirvaria.com/views/assets/img/xml2pd500.png');
		$this->_classMain->sendMail($correo);
		$this->mostrarRespuesta($json,200);
	}
	
	private function saveMovie($movie){
		$json=0;
		//arreglando arrays de respuesta a json
		$result['id']=$movie['id'];
		$result['metaData']=json_encode($movie);
		if($this->verifyMovie($movie['id'])){
			$query=$this->_classMain->getQuery('create','movie',$result,$this->_database);
			if($this->_database!=null){
				$json=array();
				try { 
					$consulta = $this->_database->query($query);
					if($consulta){
						$json = $this->_database->lastInsertId(); 
					} 
				} catch (PDOException $e) { 
					$json = 0;  
				}
			}
		}
		return $json;
	}
	private function saveComments(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$this->_parametros['text']=utf8_encode($this->_parametros['text']);
		$this->_parametros['ts']=time();
		$query=$this->_classMain->getQuery('create','comments',$this->_parametros,$this->_database);
		if($this->_database!=null){
			$json=array();
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$json = array('code'=>1,'text'=>$this->_parametros['text'],'nameUser'=>'yo ahora mismo!',"ts"=>date('d/m/Y H:s',time())); 
				} else {
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		$this->mostrarRespuesta($json,200);
	}
	private function saveScore(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$query=$this->_classMain->getQuery('create','scores',$this->_parametros,$this->_database);
		if($this->_database!=null){
			$json=array();
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$json = array('code'=>1,'msj'=>'Score creado exitosamente!','score'=>$this->_parametros['score']); 
				} else {
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		$this->mostrarRespuesta($json,200);
	}
	private function saveFavorite(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$query=$this->_classMain->getQuery('create','favorites',$this->_parametros,$this->_database);
		if($this->_database!=null){
			$json=array();
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$json = array('code'=>1,'msj'=>'Score creado exitosamente!'); 
				} else {
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		$this->mostrarRespuesta($json,200);
	}

}
?>