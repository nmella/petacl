<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

class Mage_Servipag_Model_Standard extends Mage_Payment_Model_Method_Abstract{

 protected $_code  = 'Servipag_standard';
 protected $_formBlockType = 'Servipag/standard_form';
	
 public function getServipagCurrency(){
	 $currency_code = $this->getQuote()->getBaseCurrencyCode();
	 $ServipagCurrency['CL'] = array('CLP' => 1);
	 return @$ServipagCurrency[Mage::getStoreConfig('Servipag/wps/country')][$currency_code];
 }
	
 public function getServipagUrl(){
  $ServipagAction = array (
   'CL' => 'https://www.servipag.com/BotonPago/BotonPago/Pagar'
  );
  $url = $ServipagAction[Mage::getStoreConfig('Servipag/wps/country')];
  return $url;
 }
			
 public function getSession(){
  return Mage::getSingleton('Servipag/session');
 }

 public function getCheckout(){
  return Mage::getSingleton('checkout/session');
 }

 public function getQuote(){
  return $this->getCheckout()->getQuote();
 }

 public function canUseInternal(){
  return false;
 }

 public function canUseForMultishipping(){
  return false;
 }

 public function createFormBlock($name){
  $block = $this->getLayout()->createBlock('Servipag/standard_form', $name)
   ->setMethod('Servipag_standard')
   ->setPayment($this->getPayment())
   ->setTemplate('Servipag/standard/form.phtml');
  return $block;
 }

 public function validate(){
  parent::validate();
  $currency_code = $this->getQuote()->getBaseCurrencyCode();
//	 if(!$this->getServipagCurrency()){
//   Mage::throwException(Mage::helper('Servipag')->__('Selected currency code ('.$currency_code.') is not compatabile with Servipag('. Mage::getStoreConfig('Servipag/wps/country') .')'));
//  }
  return $this;
 }

 public function onOrderValidate(Mage_Sales_Model_Order_Payment $payment){
  return $this;
 }

 public function onInvoiceCreate(Mage_Sales_Model_Invoice_Payment $payment){
	
 }

 public function canCapture(){
  return true;
 }

 public function getOrderPlaceRedirectUrl(){
  return Mage::getUrl('Servipag/standard/redirect', array('_secure' => true));
 }

	public function getStandardCheckoutFormFields(){

		if ($this->getQuote()->getIsVirtual()) {
		//	$a = $this->getQuote()->getBillingAddress();
			$b = $this->getQuote()->getShippingAddress();
		}else{
		//	$a = $this->getQuote()->getShippingAddress();
			$b = $this->getQuote()->getBillingAddress();
		}
		
		$orderId = $this->getCheckout()->getLastOrderId();
		$order   = Mage::getModel('sales/order')->load($orderId);
		$a = $order->getIsNotVirtual() ? $order->getShippingAddress() : $order->getShippingAddress();

		$currency_code = $this->getServipagCurrency();
		$businessName = Mage::getStoreConfig('Servipag/wps/business_name');
		$storeName = Mage::getStoreConfig('store/system/name');
		//$amount = $a->getBaseGrandTotal(); 
//$order = Mage::getSingleton('sales/order');
//$order->load($lastOrderId);
$_totalData = $order->getData();
$amount = $_totalData['grand_total'];
		//$amount =$order->getBaseGrandTotal(); 
		
		
		//variable envio para giuardarla en la DB del Cliente
		//Depende de cada cliente si la aplica o no
		$envio = $a->getShippingAmount();
		$mont = sprintf('%.0f', $amount);
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->getCheckout()->getLastRealOrderId());
		$ord = $this->getCheckout()->getLastRealOrderId();	
		$TIENDA = 1;
		$session = Mage::getSingleton( 'customer/session' )->isLoggedIn();
		
		$usr_nombre = $a->getFirstname();
		$usr_apellido = $a->getLastname();
		$usr_email = $a->getEmail();
		//termino
										
		$NombreItem = '';
		$items = $this->getQuote()->getAllItems();
		if($items){
			foreach($items as $x){
				$sub_row = $x->getRowTotal();
				$trozo=explode(".",$sub_row);
				 $NombreItem .=  "<tr><td>".$x->getQty() ."</td><td>".$x->getName()."</td><td>".$trozo[0]."</td><td>".$x->getCalculationPrice()."</td></tr>";
			}
		}
		
		//BD cliente ISTEC 
		$hostname = "localhost";
		$database = "givecard";
		$username = "mysql";
		$password = "cangrejo";
		//Coneccion a DB ISTEC
		//$conexion = mysql_connect($hostname, $username, $password);
		//mysql_select_db($database ,$conexion) or die("Error seleccionando la base de datos."); 
		//$sql= "INSERT INTO pagos (tienda, TBK_MONTO, TBK_ORDEN_COMPRA, TBK_ID_SESION, PRODUCTO, usr_nombre, usr_apellido, usr_email, costo_envio ) VALUES ('".$TIENDA."', '".$mont."', '".$ord."', '".$session."', '".$NombreItem."', '".$usr_nombre."', '".$usr_apellido."', '".$usr_email."', '".$envio."')";           
		//$RS_Ingresa = mysql_query($sql, $conexion) or die(mysql_error());  		

		$sArr = array(
			'PrecioItem' => sprintf('%.0f', $amount),
			'NroItem' => $this->getCheckout()->getLastOrderId(),
			'trx_id' => $this->getCheckout()->getLastOrderId(),
			'DireccionExito' => Mage::getUrl('index.php/Servipag/standard/success',array('_secure' => true)),
			'DireccionFracaso' => Mage::getUrl('index.php/Servipag/standard/cancel/',array('_secure' => false)),
			'Mensaje' => 0,
			'DireccionEnvio' => 0,
			'IdTrxCliente' => $ord,
			);

	$logoUrl = Mage::getStoreConfig('Servipag/wps/logo_url');
	if($logoUrl){
		$sArr = array_merge($sArr, array(
			'image_url' => $logoUrl
			));
		}

	$sReq = '';
	$rArr = array();
	foreach ($sArr as $k=>$v) {
		$value =  str_replace("&","and",$v);
		$rArr[$k] =  $value;
		$sReq .= '&'.$k.'='.$value;
	}
	
	if ($this->getDebug() && $sReq) {
		$sReq = substr($sReq, 1);
		$debug = Mage::getModel('Servipag/api_debug')
		->setApiEndpoint($this->getServipagUrl())
		->setRequestBody($sReq)
		->save();
	}
	
	 return $rArr;
 }

 public function getDebug(){
  return Mage::getStoreConfig('Servipag/wps/debug_flag');
 }

 public function isInitializeNeeded(){
  return true;
 }

 public function initialize($paymentAction, $stateObject){
  $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
  $stateObject->setState($state);
  $stateObject->setStatus(Mage::getSingleton('sales/order_config')->getStateDefaultStatus($state));
  $stateObject->setIsNotified(false);
 }
}
