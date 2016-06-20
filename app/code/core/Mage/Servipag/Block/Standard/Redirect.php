<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com
class Mage_Servipag_Block_Standard_Redirect extends Mage_Core_Block_Abstract{
 
protected function _toHtml(){
	//Se incluye la clase de Botón de pago	
	include("/home/petacl/peta.cl/html/app/code/core/Mage/Servipag/BotonPago/BotonPago.php");
	//se carga el archivo de configuración
	$matriz_ini = parse_ini_file("/home/petacl/peta.cl/html/app/code/core/Mage/Servipag/BotonPago/config.ini", true);
	//Instancia la clase botón de pago
	$BotonPago = new BotonPago();
	//se establece la ruta del log
	$BotonPago->setRutaLog($matriz_ini['rutaLog']['ruta']);
	//Se estable las llaves privadas y publicas
	$BotonPago->setRutaLlaves($matriz_ini['Config_Llaves']['privada'], $matriz_ini['Config_Llaves']['publica']);
	//Se establece los nodos del xml1
	$BotonPago->setArrayOrdenamiento($matriz_ini['Config_Nodo']);
 
 	$CodigoCanaldePago = $matriz_ini['CodigoCanalPago']['CodigoCanaldePago'];
	$FechaPago = date("Ymd");  
	$NumeroBoleta = "1";
	$IdSubtrx = "1";
	$FechaVencimiento = date("Ymd");

	

 
  	$standard = Mage::getModel('Servipag/standard');
  	$form = new Varien_Data_Form();
  	$form->setAction($standard->getServipagUrl())
   	->setId('Servipag_standard_checkout')
   	->setName('Servipag_standard_checkout')
   	->setMethod('POST')
   	->setUseContainer(true);
	
	$arrayValores = $standard->getStandardCheckoutFormFields();
  	
	$IdTxCliente =$arrayValores['IdTrxCliente'];
	$MontoTotalDeuda =$arrayValores['PrecioItem'];
	$CodigoIdentificador = $arrayValores['NroItem'];
	$Boleta =$arrayValores['NroItem'];
	$Monto =$arrayValores['PrecioItem'];
	$xml = $BotonPago->GeneraXML($CodigoCanaldePago, $IdTxCliente, $FechaPago, $MontoTotalDeuda, $NumeroBoleta, $IdSubtrx, $CodigoIdentificador, $Boleta, $Monto, $FechaVencimiento);
	
  	//se agrega el hidden xml que contiene el xml1
  	$form->addField('xml', 'hidden', array('name'=>'xml', 'value'=>$xml));
	
	$html = '<html><body>';
	$html.= $this->__('Usted sera redirigido a la pagina de Servipag para pago seguro.');
	$html.= $form->toHtml();
	$html.= '<script type="text/javascript">document.getElementById("Servipag_standard_checkout").submit();</script>';
	$html.= '</body></html>';
	return $html;
 }
}
