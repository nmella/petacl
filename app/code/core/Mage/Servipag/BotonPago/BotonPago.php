<?php

//BotonPago Class
//Versión 1.2 
//Implementación de nueva clase Botón de Pago 2.0
//Fecha última Modificación: 6-2-2013
//Francisco Hurtado - fhurtado@servipag.cl 
class BotonPago
{

	//--Variables Privadas del Metodo
	
	private $rutaLlavePrivada; //variable que contiene la ruta de la llave privada
	private $rutaLlavePublica; //variable que contiene la ruta de la llave publica
	private $arrayOrdenamiento;//variable que contiene array de conquetenación ordenado
	private $rutalog; //variable que contiene la ruta de los log.
	//-- Funciones
	
	//constructor de la clase
	public function __construct() {
	}
	
	//Nombre Funcion: setArrayOrdenamiento
	//Descripción:Función Pública Que setea el array de 
	//						concatenar y lo ordenamiento. 
	//Variable de Entrada:
	//	$ord: array de concatenación.
	//Valor de retorno:
	//	 ninguno
	public function setArrayOrdenamiento($ord = null){
		if($ord){
			asort($ord);
			$this->arrayOrdenamiento = $ord;
			$this->GeneraLog('0', 'Realiza ordenamiento:'.$ord);
			}
	}
	
	//Nombre Funcion: setRutaLlaves
	//Descripción: Función Pública Que setea las rutas de las Llaves
	// estableciendo los valores en las variables privadas 
	//Variable de Entrada:
	//	$rutaLlavePri: Ruta Llave Privada, el valor puede ser nulo.
	//  $rutaLlavePub: Ruta Llave Pública, el valor puede ser nulo.
	//Valor de retorno:
	//	 ninguno
	public function setRutaLlaves($rutaLlavePri = null, $rutaLlavePub = null){
		if($rutaLlavePri){
				$this->rutaLlavePrivada = $rutaLlavePri;
				$this->GeneraLog('1', 'Setea Ruta Llave Privada :'.$rutaLlavePri);
				}
		if($rutaLlavePub){
				$this->rutaLlavePublica = $rutaLlavePub;
				$this->GeneraLog('1', 'Setea Ruta Llave Publica :'.$rutaLlavePub);
				}
	}
	
	//Nombre Funcion: setRutaLog
	//Descripción: Función Pública Que setea la ruta log. 
	//Variable de Entrada:
	//	$rutaL: Ruta Llave Privada, el valor puede ser nulo.
	//Valor de retorno:
	//	 ninguno
	public function setRutaLog($rutaL=null){
		if($rutaL){
				$this->rutalog = $rutaL;
				$this->GeneraLog('1', 'Setea Ruta Log:'.$rutaL);
			}
	}
	
	//Nombre Funcion: getPublica
	//Descripción: Función  Que Obtiene Llave Pública
	//		 desde ubicación señalada desde variable 
	//		 de entrada.
	//Variable de Entrada:
	//	$file: Nombre de Archivo
	//Valor de retorno:
	//	string con llave pública
	function getPublica( $file = null ){
		if(!$file)
			return false;

		$fp = fopen($file , "r");
		$txtpublica = fread($fp, 8192);
		fclose($fp);
		
		$this->GeneraLog('1', 'Obtiene Llave Publica :');

		return $txtpublica;
	}

	//Nombre Funcion: getPrivada
	//Descripción: Función  Que Obtiene Llave Privada
	//		 desde ubicación señalada desde variable 
	//		 de entrada.
	//Variable(s) de Entrada:
	//		$file: Nombre de Archivo
	//Valor de retorno:
	//		string con llave privada
	function getPrivada($file = null){
		if(!$file)
			return false;

		$fp = fopen($file , "r");
		$txtprivada = fread($fp, 8192);
		fclose($fp);
		$this->GeneraLog('1', 'Obtiene Llave Privada:');
		return $txtprivada;
	}

	//Nombre Funcion: getFirma
	//Descripción: Función  Que Obtiene firma
	//		 desde ubicación señalada desde variable 
	//		 de entrada.
	//Variable(s) de Entrada:
	//		$file: Nombre de Archivo
	//Valor de retorno:
	//		string con firma
	function getFirma($file = null){
		if(!$file)
			return false;

		$fp = fopen($file, "r");
		$txtfirma = fread($fp, 8192);
		fclose($fp);
		$this->GeneraLog('1', 'Obtiene Firma :'.$txtfirma);
		return $txtfirma;
	}

	//Nombre Funcion: encripta
	//Descripción: Función Pública que realiza 
	//             la incriptación de los datos
	//Variable(s) de Entrada:
	//		$datos: string con datos concatenado para la incriptación
	//Valor de retorno:
	//			string con datos firmados
	function encripta($datos){
		$result = "";
		$this->GeneraLog('1', 'Función Encripta :');
		//obtiene firma publica o llave privada
		$llavePrivada = $this->getPrivada($this->rutaLlavePrivada);
		
		//firma los datos enviados
		$this->GeneraLog('1', '-----------------------------------');
		$this->GeneraLog('1', 'Realiza Firmado de los Datos :');
		$this->GeneraLog('1', '------- variable datos: '.$datos );
		$this->GeneraLog('1', '------- variable result: '.$result );
		$this->GeneraLog('1', '------- variable llavePrivada: '.$llavePrivada );
		$this->GeneraLog('1', '-----------------------------------');
		
		// controla excepciones 
		try {
			$this->GeneraLog('1', '------------------Entra dentro del try---------------------');
    			openssl_sign($datos, $result, $llavePrivada, OPENSSL_ALGO_MD5);
    			$this->GeneraLog('1', 'Realizado Firmado de los Datos :'.$result);
		     } 
		catch( Exception $e ) {
			 // o el tipo de Excepcion que requieras...
			$this->GeneraLog('1', '------- Error en generación de firma ----: ');
			$this->GeneraLog('1', '------- Mensaje Error: '.$e->getMessage());
		    	$this->GeneraLog('1', '------- Fin Error ----------------: ');  
		}  
		
		$this->GeneraLog('1', '-----------------------------------');
		//encripta en base64
		$result = base64_encode($result);
		$this->GeneraLog('1', 'Realiza Encriptación de los Datos :'.$result);
		//returna el valor
		return $result;
	}
	
	//Nombre Funcion: desencripta
	//Descripción: Función Pública que realiza a través de la desencriptación de la llave
	//              y realiza la verificación de la firma
	//Variable(s) de Entrada:
	//		$datos: string con datos concatenado
	//						para la incriptación
	//		$firma: string con la firma
	//Valor de retorno:
	//		booleando con el resultado de la validación de la firma
	public function desencripta($datos, $firma){
		$this->GeneraLog('1', 'Función Desencripta :');
		//obtiene firma llave publica
		$llave = $this->getPublica($this->rutaLlavePublica);
		//desencripta en base64
		$base64 = base64_decode($firma);
		$this->GeneraLog('1', 'Desencripta en Base64 :'.$base64);
		
		$this->GeneraLog('1','Verificación de Firma Datos:'.$datos.'--b64:'.$base64.'--Llave:'.$llave);
		//Verifica firma
		if(openssl_verify($datos,$base64,$llave,OPENSSL_ALGO_MD5)){
			$this->GeneraLog('1', 'Verificación de Firma Positiva');
			return true;
			}
		else{
			$this->GeneraLog('1', 'Verificación de Firma Negativa');
			return false;
			}
	}

	//Nombre Funcion: GeneraXML
	//Descripción: Función Pública que realiza
	//             la Generación del XML1 Firmado
	//Variable(s) de Entrada:
	//		$CodigoCanalPago = Código que contiene el Canal de Pago 
	// 		$IdTxPago = Id Cliente
	// 		$FechaPago = Fecha de Pago
	//		$MontoTotalDeuda = Monto Total de Deuda 
	//		$NumeroBoletas = Numero de Boletas 
	//		$IdSubTx = Id Sub Trx
	//		$Identificador = Código de Identificador 
	//		$Boleta = Boleta
	// 		$Monto = Monto 
	//		$FechaVencimiento = Fecha Vencimiento
	//		$NombreCliente= nombre del cliente
	//		$RutCliente = Rut Cliente
	//		$EmailCliente = email del cliente
	//Valor de retorno:
	//		Devuelve XML con firma
	public function GeneraXML($CodigoCanalPago = null, $IdTxPago = null, $FechaPago = null, $MontoTotalDeuda = null, $NumeroBoletas = null, $IdSubTx = null, $Identificador = null, $Boleta = null, $Monto = null, 	$FechaVencimiento = null,$NombreCliente=null, $RutCliente=null, $EmailCliente = null){

	$datos ="";
	$this->GeneraLog('1', 'Funcion Generación XML1');
	$this->GeneraLog('1', 'IdSubTx '.$IdSubTx);
	//$datos = $IdTxPago."".$FechaPago."".$MontoTotalDeuda."".$NumeroBoletas."".$FechaVencimiento."".$IdSubTx."".$Identificador."".$Boleta."".$Monto;  
	
	foreach ($this->arrayOrdenamiento as $key => $val) {		
			switch($key)
			{
			  	case "CodigoCanalPago":
					$datos = $datos.$CodigoCanalPago;			
				break;
				case "IdTxPago":
					$datos = $datos.$IdTxPago;
				break;
				case "FechaPago":
					$datos = $datos.$FechaPago;
				break;
				case "MontoTotalDeuda":
					$datos = $datos.$MontoTotalDeuda;	
				break;
				case "NumeroBoletas":
					$datos = $datos.$NumeroBoletas;
				break;
			} 
	}
	foreach ($this->arrayOrdenamiento as $key => $val) {		
			switch($key)
			{
				
				case "IdSubTx":
					$datos = $datos.$IdSubTx;		
				break;	
				case "Identificador":
					$datos = $datos.$Identificador;
				break;
				case "Boleta":	
					$datos = $datos.$Boleta;
				break;
				case "Monto":
					$datos = $datos.$Monto;
				break;
				case "FechaVencimiento":
					$datos = $datos.$FechaVencimiento;
				break;
			} 
	}
	
		$this->GeneraLog('1', 'Datos Concatenado :'.$datos);
		$firma = $this->encripta($datos);
  		$this->GeneraLog('1', 'Firma para XML1:'.$firma);
		$xml = "<?xml version='1.0' encoding='ISO-8859-1'?><Servipag><Header><FirmaEPS>$firma</FirmaEPS><CodigoCanalPago>$CodigoCanalPago</CodigoCanalPago><IdTxPago>$IdTxPago</IdTxPago><EmailCliente>$EmailCliente</EmailCliente><NombreCliente>$NombreCliente</NombreCliente><RutCliente>$RutCliente</RutCliente><FechaPago>$FechaPago</FechaPago><MontoTotalDeuda>$MontoTotalDeuda</MontoTotalDeuda><NumeroBoletas>$NumeroBoletas</NumeroBoletas><Version>2</Version></Header><Documentos><IdSubTx>$IdSubTx</IdSubTx><Identificador>$Identificador</Identificador><Boleta>$Boleta</Boleta><Monto>$Monto</Monto><FechaVencimiento>$FechaVencimiento</FechaVencimiento></Documentos></Servipag>";
		$this->GeneraLog('1', 'XML1 completo:'.$xml);
    	return $xml;
	}

 	//Nombre Funcion: GeneraLog
	//Descripción: Función Pública que realiza
	//             la Generación del log en caso de error
	//Variable(s) de Entrada:
	//		$numero = número de log 
	// 		$texto = mensaje de log
 	public function GeneraLog($numero, $texto){
 		
		//se agrega ruta donde se guarda log
		$realtime =  "/var/log/Servipag1".date("Ymd").".log"; 
 		$ddf = fopen($realtime,'a');
 		fwrite($ddf,"[".date("r")."]     $numero: $texto \r\n");
 		fclose($ddf);
 	}
 
	 //	set_error_handler('error');
	
		//Nombre Funcion: CompruebaXML2
		//Descripción: Función Pública que realiza
		//             la validación del XML2 recibido
		//Variable(s) de Entrada:
		//		$xml = xml 
		// 		$nodo = Array con nodo 
		//Valor de retorno:
		//		booleando con el resultado de la validación
		public function CompruebaXML2($xml,$nodo){
		$this->GeneraLog('1', 'Función Comprueba Xml2:');
		$this->GeneraLog('1', 'xml:'.$xml);
		$this->GeneraLog('1', 'nodo:'.$nodo);
		$datos = '';
		$firma = substr($xml,strrpos($xml,"<FirmaServipag>"),(strrpos($xml,"</FirmaServipag>") - strrpos($xml,"<FirmaServipag>")));
		$firma = str_replace('<FirmaServipag>', '',$firma);
		$this->GeneraLog('1', 'Obtención Firma dentro XML2 :'.$firma);
 		asort($nodo);
 		foreach ($nodo as $key => $val) {
			switch($key)
			{
 				case "IdTrxServipag":
					$datos = $datos.substr($xml,strrpos($xml,"<IdTrxServipag>"),strrpos($xml,"</IdTrxServipag>") - strrpos($xml,"<IdTrxServipag>"));
					$datos = str_replace('<IdTrxServipag>', '',$datos);			
				break;
				case "IdTxCliente":
					$datos = $datos.substr($xml,strrpos($xml,"<IdTxCliente>"),strrpos($xml,"</IdTxCliente>") - strrpos($xml,"<IdTxCliente>"));
					$datos = str_replace('<IdTxCliente>', '',$datos);			
				break;
				case "FechaPago":
					$datos = $datos.substr($xml,strrpos($xml,"<FechaPago>"),strrpos($xml,"</FechaPago>") - strrpos($xml,"<FechaPago>"));
					$datos = str_replace('<FechaPago>', '',$datos);			
				break;
				case "CodMedioPago":
					$datos = $datos.substr($xml,strrpos($xml,"<CodMedioPago>"),strrpos($xml,"</CodMedioPago>") - strrpos($xml,"<CodMedioPago>"));
					$datos = str_replace('<CodMedioPago>', '',$datos);			
				break;
				case "FechaContable":
					$datos = $datos.substr($xml,strrpos($xml,"<FechaContable>"),strrpos($xml,"</FechaContable>") - strrpos($xml,"<FechaContable>"));
					$datos = str_replace('<FechaContable>', '',$datos);			
				break;
				case "CodigoIdentificador":
					$datos = $datos.substr($xml,strrpos($xml,"<CodigoIdentificador>"),strrpos($xml,"</CodigoIdentificador>") - strrpos($xml,"<CodigoIdentificador>"));			
					$datos = str_replace('<CodigoIdentificador>', '',$datos);
				break;
				case "Boleta":
					$datos = $datos.substr($xml,strrpos($xml,"<Boleta>"),strrpos($xml,"</Boleta>") - strrpos($xml,"<Boleta>"));
					$datos = str_replace('<Boleta>', '',$datos);			
				break;
				case "Monto":
					$datos = $datos.substr($xml,strrpos($xml,"<Monto>"),strrpos($xml,"</Monto>") - strrpos($xml,"<Monto>"));
				$datos = str_replace('<Monto>', '',$datos);				
				break;
}
}
		$this->GeneraLog('5', 'Datos concatenacion para verificación de Firma:'.$datos);
		$datos = str_replace(' ', '',$datos);	
		$this->GeneraLog('5', 'Desencriptación Datos:'.$datos.'--Firma:'.$firma);
		$result = $this->desencripta($datos,$firma);
 
 		if($result){
 		//log bueno
  			$this->GeneraLog('1', 'Firma Valida :');
			return true;
			 }
 		else{
  		//log malo
  			$this->GeneraLog('2', 'Firma No Valida : ');
			return false;
			}
			
		}
		
		//Nombre Funcion: GeneraXML3
		//Descripción: Función Pública que genera
		//             XML3
		//Variable(s) de Entrada:
		//		$Codigo = Código con resultado(0 = correcto; 1 = fallido)  
		// 		$Mensaje = Mensaje del resultado
		//Variable de retorno:
		//		string con XML3
		public function GeneraXML3($Codigo, $Mensaje){
		 $this->GeneraLog('5', 'Función Genera Xml3 Código:'.$Codigo.'--Mensaje:'.$Mensaje);
		 $xml = "<?xml version='1.0' encoding='ISO-8859-1'?><Servipag><CodigoRetorno>$Codigo</CodigoRetorno><MensajeRetorno>$Mensaje</MensajeRetorno></Servipag>";
		 $this->GeneraLog('5', 'Xml3 Generado:'.$xml);
    	return $xml;
		}

		//Nombre Funcion: ValidaXml4
		//Descripción: Función Pública que genera XML4
		//Variable(s) de Entrada:
		//		$Xml4 = Xml con los datos
		//		$nodo = array con los nodos correspondiente al xml4   
		//Variable de retorno:
		//		boleando si el xml es valido o no
		public function ValidaXml4($Xml4,$nodo){
			$this->GeneraLog('4', '***********************************************************************************');
			$this->GeneraLog('4', 'Función Valida XML4 xml:'.$Xml4.'--Nodos:'.$nodo);
			
			if(strpos($Xml4, '&lt;')!== false){
				$this->GeneraLog('4', '---------Se Reemplaza &lt; a <');
				$Xml4 = str_replace('&lt;', '<',$Xml4);
				$this->GeneraLog('4', '--------- XML4 Resultante: '.$Xml4);
			}
			
			if(strpos($Xml4, '&gt;')!== false){
				$this->GeneraLog('4', '---------Se Reemplaza &gt; a >');
				$Xml4 = str_replace('&gt;', '>',$Xml4);
				$this->GeneraLog('4', '--------- XML4 Resultante: '.$Xml4);
			}
						
			$datos = '';
			$firma = substr($Xml4,strrpos($Xml4,"<FirmaServipag>"),(strrpos($Xml4,"</FirmaServipag>") - strrpos($Xml4,"<FirmaServipag>")));
			$firma = str_replace('<FirmaServipag>', '',$firma);
			$firma = trim($firma); 
			$firma = str_replace(' ', '+',$firma);
			$this->GeneraLog('4', 'Firma que contiene XML4 :'.$firma);
			asort($nodo);
 			foreach ($nodo as $key => $val) {
				switch($key)
				{
					case "IdTrxServipag":
						$datos = $datos.substr($Xml4,strrpos($Xml4,'<IdTrxServipag>'),(strrpos($Xml4,'</IdTrxServipag>') - strrpos($Xml4,'<IdTrxServipag>')));
						$datos = str_replace('<IdTrxServipag>', '',$datos);			
					break;
					case "IdTxCliente":
						$datos = $datos.substr($Xml4,strrpos($Xml4,"<IdTxCliente>"),strrpos($Xml4,"</IdTxCliente>") - strrpos($Xml4,"<IdTxCliente>"));
						$datos = str_replace('<IdTxCliente>', '',$datos);			
					break;
					case "EstadoPago":
						$datos = $datos.substr($Xml4,strrpos($Xml4,"<EstadoPago>"),strrpos($Xml4,"</EstadoPago>") - strrpos($Xml4,"<EstadoPago>"));
						$datos = str_replace('<EstadoPago>', '',$datos);			
					break;
					case "Mensaje":
						$datos = $datos.substr($Xml4,strrpos($Xml4,"<Mensaje>"),strrpos($Xml4,"</Mensaje>") - strrpos($Xml4,"<Mensaje>"));			
						$datos = str_replace('<Mensaje>', '',$datos);
					break;
				}
			}  
			$this->GeneraLog('4', 'valor de concatenacion de Nodos XML4:'.$datos);
			$result = $this->desencripta($datos,$firma);
 			if($result){
 			//log bueno
  				$this->GeneraLog('4', 'Firma Valida XML4 :');
$this->GeneraLog('4', '*******************************************************************************************');
				return true;
			}
 			else{
  			//log malo
  				$this->GeneraLog('4', 'Firma No Valida XML4 : ');
$this->GeneraLog('4', '***********************************************************************************');
				return false;
			}
			
		}

	}
	
	
?>
