<?php

class Mage_Webpay_Model_Standard extends Mage_Payment_Model_Method_Abstract{

 protected $_code  = 'Webpay_standard';
 protected $_formBlockType = 'Webpay/standard_form';
	
 public function getWebpayCurrency(){
	 $currency_code = $this->getQuote()->getBaseCurrencyCode();
	 $WebpayCurrency['AR'] = array('ARS' => 1, 'USD' => 2);
	 $WebpayCurrency['BR'] = array('BRL' => 1);
	 $WebpayCurrency['CL'] = array('CLP' => 1, 'USD' => 2);
	 $WebpayCurrency['MX'] = array('MXN' => 1, 'USD' => 2);
	 return @$WebpayCurrency[Mage::getStoreConfig('Webpay/wps/country')][$currency_code];
 }
	
 public function getWebpayUrl(){
  $WebpayAction = array (
   'AR' => 'https://argentina.Webpay.com/Shop/Shop_Ingreso.asp', 
   'BR' => 'https://brasil.Webpay.com/dinero-tools/login/shop/shop_ingreso.asp',
   'CL' => 'https://chile.Webpay.com/Shop/Shop_Ingreso.asp',
   'MX' => 'https://mexico.Webpay.com/Shop/Shop_Ingreso.asp'
  );
  $url = $WebpayAction[Mage::getStoreConfig('Webpay/wps/country')];
  return $url;
 }
			
 public function getSession(){
  return Mage::getSingleton('Webpay/session');
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
  $block = $this->getLayout()->createBlock('Webpay/standard_form', $name)
   ->setMethod('Webpay_standard')
   ->setPayment($this->getPayment())
   ->setTemplate('Webpay/standard/form.phtml');
  return $block;
 }

 public function validate(){
  parent::validate();
  $currency_code = $this->getQuote()->getBaseCurrencyCode();
  //if(!$this->getWebpayCurrency()){
  // Mage::throwException(Mage::helper('Webpay')->__('Selected currency code ('.$currency_code.') is not compatabile with Webpay('. //Mage::getStoreConfig('Webpay/wps/country') .')'));
  //}
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
  return Mage::getUrl('Webpay/standard/redirect', array('_secure' => true));
 }

	public function getStandardCheckoutFormFields(){

// Cargamos la variable $customer con los datos de Sesion
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        $customer = mage::getModel('sales/order_address')->load($customerId);
		
// Cargamos la variable $order con los datos de la orden
		$orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

// Obtenemos el tipo de moneda
		$currency_code = $order->getBaseCurrencyCode();

// Obtenemos el nombre de la tienda y nombre definido en la configuración de Webpay
		$businessName = Mage::getStoreConfig('Webpay/wps/business_name');
		$storeName = Mage::getStoreConfig('store/system/name');

// Asignamos a la variable $NombreItem el nombre de cada item ;)
		$NombreItem = '';
		$items = $order->getAllItems();
		if($items){
			foreach($items as $x){
				$NombreItem .= $x->getName() .' (x '. $x->getQty() . ') ';
			}
		}
		
//Asignamos el total a $grandTotal
	
		//$grandTotal = $order->getBaseGrandTotal();
		
		$_totalData = $order->getData();
	$grandTotal = $_totalData['grand_total'];
	//var_dump($_SERVER);
		$http="http://";
		if (isset($_SERVER['HTTPS'])) {
    $http="https://";
		
		}
		
		
//exit('Mis pruebas');
// Mage::getUrl('index.php/Servipag/standard/success',array('_secure' => true)),
//'TBK_URL_EXITO' => 'https://www.peta.cl/webpay/webpay_exito.php',
//	'TBK_URL_EXITO' => Mage::getUrl('index.php/Webpay/standard/success/',array('_secure' => true)),
		$sArr = array(
			'E_Comercio' => Mage::getStoreConfig('Webpay/wps/business_account'),
			'NombreItem' => $NombreItem? $NombreItem : $businessName,
			'TBK_MONTO' => sprintf('%.2f', $grandTotal),
			'TBK_ID_SESION' => $this->getCheckout()->getLastOrderId(),
			'TipoMoneda' => $currency_code,
			'TBK_ORDEN_COMPRA' => $this->getCheckout()->getLastOrderId(),
			'TBK_URL_EXITO' => $http.$_SERVER['SERVER_NAME'].'/index.php/Webpay/standard/success/',
			'TBK_URL_FRACASO' => $http.$_SERVER['SERVER_NAME'].'/webpay/webpay_fracaso.php',
			'usr_nombre' => $customer->getFirstname(),
			'usr_apellido' => $customer->getLastname(),
			'usr_email' => $customer->getEmail(),
			'usr_tel_numero' => $customer->getTelephone(),
			'TBK_TIPO_TRANSACCION' => 'TR_NORMAL',
			'DireccionEnvio' => 0,
			//'MediosPago'=> '13,14',
		);

	$logoUrl = Mage::getStoreConfig('Webpay/wps/logo_url');
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
		$debug = Mage::getModel('Webpay/api_debug')
		->setApiEndpoint($this->getWebpayUrl())
		->setRequestBody($sReq)
		->save();
	}
	
	
	/*
	vaciar carro
	*/
		$cartHelper = Mage::helper('checkout/cart');
		$items = $cartHelper->getCart()->getItems();
		foreach ($items as $item) {
		     $itemId = $item->getItemId();
		     $cartHelper->getCart()->removeItem($itemId)->save();
		}
	/****fin vaciar carro**********/
	
	 return $rArr;
 }

 public function getDebug(){
  return Mage::getStoreConfig('Webpay/wps/debug_flag');
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
