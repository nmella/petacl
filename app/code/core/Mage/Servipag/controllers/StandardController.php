<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com
class Mage_Servipag_StandardController extends Mage_Core_Controller_Front_Action{

 protected $_order;

 public function getOrder(){
  if ($this->_order == null){ }
  return $this->_order;
 }


 protected function _expireAjax(){
  if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
   $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
   exit;
  }
 }

 public function getStandard(){
  return Mage::getSingleton('Servipag/standard');
 }

 public function redirectAction(){
  $session = Mage::getSingleton('checkout/session');
  $session->setServipagStandardQuoteId($session->getQuoteId());
  $this->getResponse()->setBody($this->getLayout()->createBlock('Servipag/standard_redirect')->toHtml());
  $session->unsQuoteId();
 }

 public function cancelAction(){
  $session = Mage::getSingleton('checkout/session');
  $session->setQuoteId($session->getServipagStandardQuoteId(true));
  
		if ($session->getLastRealOrderId()) {
   $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
   if ($order->getId()) {
     $order->cancel();
				 $order->save();
    }
   }
  $this->_redirect('checkout/cart');
 }

//Comunicación entre servidor Servipag y cliente
 public function h2hAction(){
  	$Xml_out = $_POST['XML'];
	
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
	//Se establece los nodos del xml2
	$nodo = $matriz_ini['Config_Nodo_XML2'];
	
	//realizo la comprobación del XML2
 	$result =  $BotonPago->CompruebaXML2($Xml_out, $nodo);
 
 	//genero codigo y mensaje para  el xml3
 	$codigo = '1';
	$mensaje= 'Transaccion Mala';
 	if($result){
 	$codigo = '0';
	$mensaje= 'Transaccion OK 10-4';
 	
	//se actualiza orden de compra
	$CodigoIdentificador = substr($Xml_out,strrpos($Xml_out,"<CodigoIdentificador>"),strrpos($Xml_out,"</CodigoIdentificador>") - strrpos($Xml_out,"<CodigoIdentificador>"));			
	$CodigoIdentificador = str_replace('<CodigoIdentificador>', '',$CodigoIdentificador);
	
	$idServipag = substr($Xml_out,strrpos($Xml_out,"<IdTrxServipag>"),strrpos($Xml_out,"</IdTrxServipag>") - strrpos($Xml_out,"<IdTrxServipag>"));			
	$idServipag = str_replace('<IdTrxServipag>', '',$idServipag);
	
	$order = Mage::getModel('sales/order')->load($CodigoIdentificador);
	//$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true);
	
	$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Notificacion de XML2 con Id Transaccion Servipag :'.$idServipag, true);
	$order->save();
	}
	
 	//genero el xml3
 	$Xml_Resultado = $BotonPago->GeneraXML3($codigo,$mensaje);
	echo($Xml_Resultado);
  }

 public function  successAction(){
  	$Xml_out = $_REQUEST['xml'];
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
	//Se establece los nodos del xml2
	$nodo = $matriz_ini['Config_Nodo_XML4'];

	//Resultado de la validación
	$resuladoValidacion = $BotonPago->ValidaXml4($Xml_out, $nodo);

	if($resuladoValidacion){
		$session = Mage::getSingleton('checkout/session');
		$session->setQuoteId($session->getServipagStandardQuoteId(true));
		if ($session->getLastRealOrderId()) {
			$idServipag = substr($Xml_out,strrpos($Xml_out,"<IdTrxServipag>"),strrpos($Xml_out,"</IdTrxServipag>") - strrpos($Xml_out,"<IdTrxServipag>"));			
	$idServipag = str_replace('<IdTrxServipag>', '',$idServipag);
			
			
			$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
			//$order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
			
			$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Notificacion de XML4 con Id Transaccion Servipag :'.$idServipag, true);
			$order->sendNewOrderEmail();
 	 		$order->setEmailSent(true);
			$order->save();
			$this->_redirect('checkout/onepage/success');
		}  
	}
 }
}
?>
