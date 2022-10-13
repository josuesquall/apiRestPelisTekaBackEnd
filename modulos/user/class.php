<?php  
class APICore extends Controller { 
	//declaramos nuestra variable de conexion a la base de datos que sera recivida como parametro en el constructor
	//ademas de las demas variables que se podran utilizar
	private $_database = null;
	private $_metodo = null;
	private $_parametros = null;
	private $_session=null;
	private $_classMain=null;
	private $_phpMailer=null;
	private $_code=200;
	
	public function __construct() {  
		parent::__construct();   
	} 
	
	private function conectarDB($servidor,$nombre_db,$usuario_db,$pwd_db) {  
		$dsn = 'mysql:dbname=' . $nombre_db . ';host=' . $servidor;  
		try {  
			$this->_database = new PDO($dsn, $usuario_db, $pwd_db);  
		} catch (PDOException $e) {  
			echo 'Falló la conexión: ' . $e->getMessage();  
		}  
	} 
	
	private function login($userName,$pass) { 
		$return=array('id'=>0); 
		if (!empty($keyPrivate) and !empty($key)) {    
			//consulta preparada ya hace mysqli_real_escape() 
			$query = $this->_database->query("select * from clientes where email='".$userName."' and pass='".$pass."' and estatus=1");  
			if ($user = $query->fetch(PDO::FETCH_ASSOC)) {  
				if($user['id']>0){
					$return=$user;
				}
			}  
		} 
		return $return; 
	}
	
	public function procesarLLamada($session,$options,$classMain,$phpMailer,$parametros) { 
		$this->_classMain=$classMain;
		$this->_phpMailer=$phpMailer;
		$this->_session=$session;
		$this->conectarDB($options->hostdb,$options->db,$options->userdb,$options->passworddb);
		$this->_metodo = $parametros['method'];  
		$this->_parametros = $parametros;
		if($this->_session['login']['idCliente']==0){
			$this->_session['login']=$this->login($this->_session['login']['email'],$this->_session['login']['pass']);
		}
		if($this->_session['login']['idCliente']>0){
			if(!is_array($parametros)){
				$parametros=json_decode($parametros, true);
			}
			if ((int) method_exists($this, $this->_metodo) > 0) {  
				if (count($this->_parametros) > 0) {  
					call_user_func(array($this, $this->_metodo), $this->_parametros);  
				} else {  
					call_user_func(array($this, $this->_metodo));  
				} 
			}  else { 
				$this->mostrarRespuesta($this->devolverError(0), 200); 
			}
		} else {
			$this->mostrarRespuesta($this->devolverError(3), 200); 	
		}
	}

	/** setters */
	private function saveVenta(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$reg=array(
		'idCliente'=>$_SESSION["login"]['idCliente'],	
		'ts'=>strtotime($this->_parametros['dte']['Certificacion']['FechaHoraCertificacion']),
		'cliente'=>json_encode($this->_parametros['dte']['DatosEmision']['Receptor'],JSON_UNESCAPED_UNICODE),
		'monto'=>floatval($this->_parametros['dte']['DatosEmision']['Totales']['GranTotal']),
		'metaData'=>json_encode($this->_parametros['dte'],JSON_UNESCAPED_UNICODE),
		'estado'=>1); 	
		$query=$this->_classMain->getQuery('create','ventas',$reg,$this->_database);
		$verify=$this->verifyXml($_SESSION["login"]['idCliente'],$this->_parametros['dte']['Certificacion']['NumeroAutorizacion']['Serie'],$this->_parametros['dte']['Certificacion']['NumeroAutorizacion']['Numero']);
		if($verify['code']==0){
			if($this->_database!=null){
				$json=array();
				try { 
					$consulta = $this->_database->query($query);
					if($consulta){
						$json = array('code'=>1,'msj'=>'Venta creada Exitosamente!','metaData'=>$this->_parametros['dte'],'idVenta'=>$this->_database->lastInsertId()); 
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
			$json = array('code'=>1,'msj'=>'Venta actualmente guardada.','metaData'=>$this->_parametros['dte'],'idVenta'=>$verify['idVenta']);
		}
		$this->mostrarRespuesta($json,$this->_code);
	}
	private function changeEstado(){
		//id_kardex,#id_cuenta,#id_proceso,#tipo,#id_user,#fecha,monto,saldo,descripcion,documento,num_doc
		$json=array('code'=>0,'msj'=>'El metodo llamado no tiene contenido a procesar.');
		$query="update ventas set estado=".$this->_parametros['estado']." where idVenta=".$this->_parametros['idVenta'];
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$json = array('code'=>1,'msj'=>'Venta actualizada Exitosamente!'); 
				} else {
					//$json = array('code'=>0,'msj'=>'Al parecer el query fallo: '.$query); 
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
	private function setMiniTuto(){
		//id_kardex,#id_cuenta,#id_proceso,#tipo,#id_user,#fecha,monto,saldo,descripcion,documento,num_doc
		$json=array('code'=>1,'msj'=>'Mini Tutorial desactivado');
		$_SESSION["miniTuto"]='false';
		$this->mostrarRespuesta($json,$this->_code);
	}
	private function setDate(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		if(isset($this->_parametros['fecha'])){
			$fecha=explode("/",$this->_parametros['fecha']);
			$this->_parametros['fecha']=$fecha[1]."/".$fecha[0]."/".$fecha[2];
			if($this->_parametros['tipo']=='in'){
				$_SESSION['tsIni']=mktime(0,0,0,date('m',strtotime($this->_parametros['fecha'])),date('d',strtotime($this->_parametros['fecha'])),date('Y',strtotime($this->_parametros['fecha'])));
			} else if($this->_parametros['tipo']=='out'){
				$_SESSION['tsFin']=mktime(23,59,59,date('m',strtotime($this->_parametros['fecha'])),date('d',strtotime($this->_parametros['fecha'])),date('Y',strtotime($this->_parametros['fecha'])));
			}
			$json = array('code'=>1,'msj'=>'Cambio la fecha sin problemas');
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		$this->mostrarRespuesta($json,$code);
	}
	private function updateConfigSmtp(){
		$json=array('code'=>0,'msj'=>'El metodo llamado no tiene contenido a procesar.');
		$tempJson=array(
			'servidor'=>$this->_parametros['smtp']['servidor'],
			'autenticacion'=>$this->_parametros['smtp']['autenticacion'],
			'usuario'=>$this->_parametros['smtp']['usuario'],
			'contrasena'=>$this->_parametros['smtp']['contrasena'],
			'seguridad'=>$this->_parametros['smtp']['seguridad'],
			'puerto'=>$this->_parametros['smtp']['puerto'],
			'correoEnvia'=>$this->_parametros['smtp']['correoEnvia'],
			'nombreEnvia'=>$this->_parametros['smtp']['nombreEnvia'],
			'correoResponder'=>$this->_parametros['smtp']['correoResponder']
		);
		$query="update clientes set metaData=";
		$query.="'".json_encode($tempJson,JSON_UNESCAPED_UNICODE)."'";
		$query.=" where idCliente=".$_SESSION['login']['idCliente'];
		if($this->_database!=null){
			$json=array();
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$_SESSION["login"]['metaData']=json_decode(json_encode($tempJson,JSON_UNESCAPED_UNICODE),true);
					$json = array('code'=>1,'msj'=>'Guardado Exitosamente!'); 
				} else {
					$json = array('code'=>0,'msj'=>$this->_database->errorInfo()[2].' :: Al parecer el query fallo: '.$query);  
				}
			} catch (PDOException $e) { 
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		$this->mostrarRespuesta($json,200);
	}
	
	//getter
	private function getVentas(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		$query="select *";
		$query.=" from ventas";
		$query.=" where idCliente=".$_SESSION["login"]['idCliente'];
		if(isset($this->_parametros['filters']) and count($this->_parametros['filters'])>0){
			if(isset($this->_parametros['filters']['numero']) and strlen($this->_parametros['filters']['numero'])>0){
				if(strpos($query,"where")==0){$query.=" where ";} else {$query.=" and ";}
				$query.="metaData->'$.Certificacion.NumeroAutorizacion.Numero'='".$this->_parametros['filters']['numero']."'";
			}
			if(isset($this->_parametros['filters']['serie']) and strlen($this->_parametros['filters']['serie'])>0){
				if(strpos($query,"where")==0){$query.=" where ";} else {$query.=" and ";}
				$query.="metaData->'$.Certificacion.NumeroAutorizacion.Serie'='".$this->_parametros['filters']['serie']."'";
			}
			if(isset($this->_parametros['filters']['cliente']) and strlen($this->_parametros['filters']['cliente'])>0){
				if(strpos($query,"where")==0){$query.=" where ";} else {$query.=" and ";}
				$query.="cliente->'$.NombreReceptor' LIKE UPPER('%".$this->_parametros['filters']['cliente']."%')";
			}
			if(isset($this->_parametros['filters']['nit']) and strlen($this->_parametros['filters']['nit'])>0){
				if(strpos($query,"where")==0){$query.=" where ";} else {$query.=" and ";}
				$query.="cliente->'$.IDReceptor' LIKE UPPER('%".$this->_parametros['filters']['nit']."%')";
			}
		} else {
			$query.=" and ts>=".$_SESSION['tsIni']." and ts<=".$_SESSION['tsFin'];
		}
		$query.=" order by ts asc";
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow>0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);  
						$code = 200;
						$json=array('code'=>1,'msj'=>'Se encontraron '.$numRow.' ventas realizadas.','count'=>$numRow,'ventas'=>array());
						foreach ($filas as $key => $value) {
							$value['cliente']=$this->objectKey2Utf8R(json_decode($value['cliente'],true));
							$value['metaData']=$this->objectKey2Utf8R(json_decode($value['metaData'],true));
							$json['ventas'][] = $value;
						}
					} else {
						$code = 200;
						$json = array('code'=>0,'msj'=>'No Hay Registros','count'=>0,'ventas'=>array(),'Query'=>$query); 
					}
				} else {
					$code = 200;
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$code = 200;
				$json = array('code'=>'error','msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$code = 200;
			$json = array('code'=>'error','msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		$this->mostrarRespuesta($json,$code);
	}
	private function getFormarFact(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		$query="select css, html from templates where estado=1";
		$query.=" and idCliente=".$_SESSION["login"]['idCliente'];
		$query.=" order by idTheme desc limit 1";
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow>0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);
						$json=array('code'=>1,'msj'=>'Formato encontrado.','format'=>array());  
						if (count($filas)> 0) {
							foreach ($filas as $key => $value) {
								$value['html']=utf8_decode($value['html']);
								$json['format'] = $value;
							}
						} 
					} else {
						$json=array('code'=>0,'msj'=>'Aun no haz establecido una plantilla.');
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
		$this->mostrarRespuesta($json,$code);
	}
	private function verifyXml($idCliente,$serie,$numero){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		$query="select idVenta";
		$query.=" from ventas";
		$query.=" where";
		$query.=" idCliente='".$idCliente."'";
		$query.=" and metaData->'$.Certificacion.NumeroAutorizacion.Serie'='".$serie."'";
		$query.=" and metaData->'$.Certificacion.NumeroAutorizacion.Numero'='".$numero."'";
		if($this->_database!=null){
			try { 
				$consulta = $this->_database->query($query);
				if($consulta){
					$numRow=$consulta->rowCount();
					if ($numRow>0) {
						$filas = $consulta->fetchAll(PDO::FETCH_ASSOC);  
						$code = 200;
						$json=array('code'=>1,'msj'=>'Se encontraron '.$numRow.' ventas realizadas.','count'=>$numRow,'ventas'=>$filas,'idVenta'=>$filas[0]['idVenta']);
					} else {
						$code = 200;
						$json = array('code'=>0,'msj'=>'No Hay Registros'); 
					}
				} else {
					$code = 200;
					$json = array('code'=>0,'msj'=>'Mysql Error: '.$this->_database->errorInfo()[2].' :: QUERY: '.$query);  
				}
			} catch (PDOException $e) { 
				$code = 200;
				$json = array('code'=>0,'msj'=>'Falló la conexión: '.$e->getMessage());  
			}
		} else {
			$code = 200;
			$json = array('code'=>0,'msj'=>'No se encontro una conexion con la base de datos establecida.');
		}
		return $json;
	}


	private function getPdf(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$html = stripslashes($this->_parametros['htmlCode']);
		$pdf=$this->_classMain->doPDF($html,false,false,$this->_parametros['paperS'],$this->_parametros['paperP']);
		if($pdf!=null){
			$base64 = base64_encode($pdf);
			$json=array('code'=>1,'msj'=>'Pdf Generado con exito.','file'=>array('mimeType'=>'application/pdf','base64'=>$base64));//
		}
		$this->mostrarRespuesta($json,200);
	}
	private function sendMail(){
		$json=array('code'=>0,'msj'=>'No se ha logrado procesar la informacion.');
		$code=200;
		$body="<p>Hola ".$this->_parametros['cliente']['NombreReceptor'].", te enviamos por este medio tu factura adjunta.</p>";
		$body.="<p>Gracias por preferirnos.</p>";
		$this->_parametros['adjunto']['fileName']='factura-'.time().'.pdf';
		$params=array(
			'config'=>$_SESSION['login']['metaData'],
			'adjunto'=>$this->_parametros['adjunto'],
			'asunto'=>'Factura Electronica -'.$this->_parametros['empresa']['NombreComercial'],
			'correoDestinatario'=>$this->_parametros['cliente']['CorreoReceptor'],
			'mensaje'=>$this->_classMain->mensaje('Factura Electronica',$body,$this->_parametros['empresa']['NombreComercial'])
		);
		if($this->_classMain->sendMail($params)){
			$json=array('code'=>1,'msj'=>'Mensaje enviado exitosamente.');
		} else {
			$json=array('code'=>0,'msj'=>'No se ha logrado enviar el correo electronico.');
		}
		$this->mostrarRespuesta($json,$code);
	}

	/** extras number to words */
	private static $nStr = array(array('cero', 'uno'),
		array('', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete',
				'ocho', 'nueve', 'diez', 'once', 'doce', 'trece',
				'catorce', 'quince', 'dieciséis', 'diecisite', 'dieciocho',
				'diecinueve', 'veinte', 'veintiuno', 'veintidós',
				'veintitrés', 'veinticuatro', 'veinticinco', 'veintiséis',
				'veintisiete', 'veintiocho', 'veintinueve', 100 => 'cien'),
		array('', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta',
				'setenta', 'ochenta', 'noventa'),
		array('', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos',
				'quinientos', 'seicientos', 'setecientos', 'ochocientos',
				'novecientos'),
		array('', '', 'mil', 'millón', 'mil', 'billón', 'mil', 'trillón',
				'mil', 'cuatrillón', 'mil', 'quintillón', 'mil',
				'sextillón', 'mil', 'septillón', 'mil', 'octillón'),
		array('', '', 'mil', 'millones', 'mil', 'billones', 'mil',
				'trillones', 'mil', 'cuatrillones', 'mil', 'quintillones',
				'mil', 'sextillones', 'mil', 'septillones', 'mil',
				'octillones', 'mil')
	);

    private function _num($n, $c = 1, $l = 1) {
        return ($n == 1 && !($l % 2)) || !$l ? '' : self::$nStr[$c][$n] . ' ';
    }
   
    private function _i2str($lev, $number) {
        $int = intval($num = substr($number, 0, 3));
        $next = substr($number, 3);
        $str = ''; //echo "($lev|$num|$int|$number)<hr>n"; //Debug
        if ($int) {
            if ($int == 100)
                $str = self::_num($int, 1);
            else {
                list($c, $d, $u) = $num;//centenas, decenas y unidad
                $str = $c ? self::_num($c, 3) : '';
                if (($du = (($d * 10) + $u)) < 30)
                    $str .= self::_num($du, $du == 1 && $lev < 2 ? 0 : 1, $lev);
                else {
                    $str .= $d ? self::_num($d, 2) . ($u ? 'y ' : '') : '';
                    $str .= $u ? self::_num($u, $u + $lev < 3 ? 0 : 1) : '';
                }
            }
            $str .= self::_num($lev, $int == 1 && ($lev % 2) ? 4 : 5)
                    . (preg_match('/^000+/', $next) ? self::_num($lev - 1, 5,
                                    !($lev % 2)) : '');
        }
        return $lev ? ($str . self::_i2str($lev - 1, $next)) : '';
    }
    
    public function toWord($number) {
        $less = preg_match('/^-/', $number);
        $number = preg_replace('/[^0-9.]/', '', $number);
        if (preg_match('/\./', $number)) {
			$test='dos';
            list($number, $decimal) = explode('.', $number);
            $result = self::toWord($number) . ' con ';
            for ($i = 0; $i < (strlen($decimal) - 1); $i++) {
                if ($decimal[$i])
                    break;
                $result .= self::_num(0, 0);
            }
            $result .= self::toWord($decimal);
			
        } else { //if (preg_match('/^d{1,54}.d{1,54}$/', $number))
			$test='uno';
            $lev = (floor(strlen($number) / 3) + 1);
            $number = str_pad($number, ($lev * 3), '0', STR_PAD_LEFT);
            $result = self::_i2str($lev, $number);
            $result || ($result = self::_num(0, 0));
        }
        //return $number;
		return isset($result) ? ($less ? 'menos ' : '') . $result : FALSE;
    }
    
    public function toCurrency($number) {
        $number = preg_replace('/[^0-9.-]/', '', $number);
        if (preg_match('/^[-]{0,1}(d{1,54})$/', $number))
            $number .= '.00';
        elseif (!preg_match('/^[-]{0,1}d{1,54}.d{1,54}$/', $number))
            return FALSE;
        list($number, $decimal) = explode('.', $number);
        $number = self::toWord($number);
        if (!$number)
            return FALSE;
        if (preg_match('/(llones|llón)$/', $number))
            $number .= ' de pesos ';
        else
            $number = preg_match('/uno$/', $number) ? (preg_replace('/uno$/',
                            '', $number) . ' un peso ') : ($number . ' pesos ');
        $decimal = round($decimal[0] . $decimal[1] . '.' . substr($decimal, 2));
        return $number . $decimal . '/100 M.N.';
    }

	public function logOut(){
		$json=array('code'=>1,'msj'=>'Sesion Cerrada.');
		unset($_SESSION['login']);
		unset($_SESSION['licencia']);
		$this->mostrarRespuesta($json,200);
	}

	private function objectKey2Utf8($objeto){
		$array=array();
		if(is_array($objeto)){
			foreach ($objeto as $key => $value) {
				if(is_array($value)){
					$array[$key]=$this->objectKey2Utf8($value);
				} else {
					$array[$key]=utf8_encode($value);
				}
			}
		} 
		return $array;
	}
	private function objectKey2Utf8R($objeto){
		$array=array();
		if(is_array($objeto)){
			foreach ($objeto as $key => $value) {
				if(is_array($value)){
					$array[$key]=$this->objectKey2Utf8R($value);
				} else {
					$array[$key]=utf8_decode($value);
				}
			}
		} 
		return $array;
	}
	
}
?>