<?php
class Controller {
	public $tipo = "application/json";  
	public $datosPeticion = array();  
	private $_codEstado = 200;  
	public function __construct() {  
		$this->tratarSolicitud();  
	}
	public function setTipo($_tipo){
		$this->tipo=$_tipo;
	}
	private function setCabecera() {  
		header("HTTP/1.1 " . $this->_codEstado . " " . $this->getCodEstado());  
		header("Content-Type:" . $this->tipo . ';charset=utf-8');  
	}  
	private function limpiarEntrada($data) {  
		$entrada = array();  
		if (is_array($data)) {  
			foreach ($data as $key => $value) {  
				$entrada[$key] = $this->limpiarEntrada($value);  
			}  
		} else {  
			//eliminamos etiquetas html y php  
			$data = strip_tags($data);  
			//Conviertimos todos los caracteres aplicables a entidades HTML  
			$data = htmlentities($data);  
			$entrada = trim($data);  
		}  
		return $entrada;  
	}  
	private function tratarSolicitud() {  
		$metodo = $_SERVER['REQUEST_METHOD'];  
		switch ($metodo) {  
			case "GET":  
				$this->datosPeticion = $this->limpiarEntrada($_GET);  
				break;  
			case "POST":  
				$this->datosPeticion = $this->limpiarEntrada($_POST);  
				break;  
			case "DELETE"://"falling though". Se ejecutará el case siguiente  
				case "PUT":  
				//php no tiene un método propiamente dicho para leer una petición PUT o DELETE por lo que se usa un "truco":  
				//leer el stream de entrada file_get_contents("php://input") que transfiere un fichero a una cadena.  
				//Con ello obtenemos una cadena de pares clave valor de variables (variable1=dato1&variable2=data2...)
				//que evidentemente tendremos que transformarla a un array asociativo.  
				//Con parse_str meteremos la cadena en un array donde cada par de elementos es un componente del array.  
				parse_str(file_get_contents("php://input"), $this->datosPeticion);  
				$this->datosPeticion = $this->limpiarEntrada($this->datosPeticion);  
				break;  
			default:  
				$this->response('', 404);  
				break;  
		}  
	}  
	private function getCodEstado() {  
		$estado = array(  
			200 => 'OK',  
			201 => 'Created',  
			202 => 'Accepted',  
			204 => 'No Content',  
			301 => 'Moved Permanently',  
			302 => 'Found',  
			303 => 'See Other',  
			304 => 'Not Modified',  
			400 => 'Bad Request',  
			401 => 'Unauthorized',  
			403 => 'Forbidden',  
			404 => 'Not Found',  
			405 => 'Method Not Allowed',  
			500 => 'Internal Server Error'
		);  
		$respuesta = ($estado[$this->_codEstado]) ? $estado[$this->_codEstado] : $estado[500];  
		return $respuesta;  
	}
	public function devolverError($id) {  
		$errores = array(  
			array('code' => 0, 'msj' => "Metodo no encontrado"), //0 
			array('code' => 0, 'msj' => "Metodo no aceptado"),  //1
			array('code' => 0, 'msj' => "Metodo sin contenido"), //2 
			array('code' => 0, 'msj' => "Fallo de Autenticacion, Llaves Incorrectas."), //3 
			array('code' => 0, 'msj' => "La Plantilla no existe."),  //4
			array('code' => 0, 'msj' => "Al parecer tenemos inconvenientes con su metodo de pago, contacte al area de soporte."), //5 
			array('code' => 0, 'msj' => "error buscando usuario por email"),  //6
			array('code' => 0, 'msj' => "error creando nuevo delivery"),  //7
			array('code' => 0, 'msj' => "Debe de enviar un objeto JSON"),  //8
			array('code' => 0, 'msj' => "Es Necesario enviar Query String") //9  
		);  
		return $errores[$id];  
	}
	public function mostrarRespuesta($data, $estado) {  
		$this->_codEstado = ($estado) ? $estado : 200;//si no se envía $estado por defecto será 200  
		$this->setCabecera();  
		echo $this->convertirJson($data);  
		exit;  
	} 
	private function convertirJson($data) { 
		$error=array(JSON_ERROR_NONE=>"No ocurrió ningún error",
			JSON_ERROR_DEPTH=>"Se ha excedido la profundidad máxima de la pila",
			JSON_ERROR_STATE_MISMATCH=>"JSON con formato incorrecto o inválido",
			JSON_ERROR_CTRL_CHAR=>"Error del carácter de control, posiblemente se ha codificado de forma incorrecta",	 
			JSON_ERROR_SYNTAX=>"Error de sintaxis",	 
			JSON_ERROR_UTF8=>"Caracteres UTF-8 mal formados, posiblemente codificados de forma incorrecta",
			JSON_ERROR_RECURSION=>"Una o más referencias recursivas en el valor a codificar",
			JSON_ERROR_INF_OR_NAN=>"Uno o más valores NAN o INF en el valor a codificar",
			JSON_ERROR_UNSUPPORTED_TYPE=>"Se proporcionó un valor de un tipo que no se puede codificar",
			JSON_ERROR_INVALID_PROPERTY_NAME=>"Se dio un nombre de una propiedad que no puede ser codificada",
			JSON_ERROR_UTF16=>"Caracteres UTF-16 malformados, posiblemente codificados de forma incorrecta");
		$jsonString=json_encode($data);
		if(json_last_error() != JSON_ERROR_NONE){
			if(json_last_error() == JSON_ERROR_UTF8){
				$jsonString = json_encode($this->recodeUtf8($data));
			} else {
				$jsonString = array('code'=>0,'msj'=>$error[json_last_error()]);
				$jsonString = json_encode($jsonString);	
			}
			
		} 
		return $jsonString;
	} 
	private function recodeUtf8($array){
		$jtemp='';
		$temp=array();
		foreach ($array as $key => $item)  {
			if(is_array($item)){
				$temp[$key]=$this->recodeUtf8($item);
			} else {
				if(is_object($item)){
					$temp[$key]=$item;
				} else {
					$jtemp=json_encode($item);
					if(json_last_error() === JSON_ERROR_UTF8){
						$temp[$key]=utf8_decode($item);
						$jtemp=json_encode($temp[$key]);
						if(json_last_error() === JSON_ERROR_UTF8){
							$temp[$key]=htmlentities($item);
						}
					} else {
						$temp[$key]=$item;
					}
				}
			}
		}
		return $temp;
	}
}
?>