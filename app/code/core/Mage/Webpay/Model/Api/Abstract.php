<?php
abstract class Mage_Webpay_Model_Api_Abstract extends Varien_Object{
 const PAYMENT_TYPE_SALE = 'Sale';
 const PAYMENT_TYPE_ORDER = 'Order';
 const PAYMENT_TYPE_AUTH = 'Authorization';
 //const REFUND_TYPE_FULL = 'Full';
//const REFUND_TYPE_PARTIAL = 'Partial';

 const COMPLETE = 'Complete';
 const NOTCOMPLETE = 'NotComplete';
 const USER_ACTION_COMMIT = 'commit';
 const USER_ACTION_CONTINUE = 'continue';

 public function getServerName(){
  if (!$this->hasServerName()) {
   $this->setServerName($_SERVER['SERVER_NAME']);
		}
  return $this->getData('server_name');
 }

 public function getConfigData($key, $default=false){
  if (!$this->hasData($key)) {
   $value = Mage::getStoreConfig('Webpay/wpp/'.$key);
   if (is_null($value) || false===$value) {
    $value = $default;
   }
   $this->setData($key, $value);
  }
  return $this->getData($key);
 }

 public function getSession(){
  return Mage::getSingleton('Webpay/session');
 }

 public function getUseSession(){
  if (!$this->hasData('use_session')) {
   $this->setUseSession(true);
  }
  return $this->getData('use_session');
 }

	public function getSessionData($key, $default=false){
		if (!$this->hasData($key)) {
			$value = $this->getSession()->getData($key);
			if ($this->getSession()->hasData($key)) {
				$value = $this->getSession()->getData($key);
			} else {
				$value = $default;
			}
			$this->setData($key, $value);
		}
		return $this->getData($key);
	}

 public function setSessionData($key, $value){
  if ($this->getUseSession()) {
   $this->getSession()->setData($key, $value);
  }
  $this->setData($key, $value);
  return $this;
 }


 public function getApiUsername(){
  return $this->getConfigData('api_username');
 }

 public function getApiSignature(){
  return $this->getConfigData('api_signature');
 }

 public function getDebug(){
  return $this->getConfigData('debug_flag', true);
 }

 public function getApiErrorUrl(){
  return Mage::getUrl($this->getConfigData('api_error_url', 'Webpay/express/error'));
 }

 public function getReturnUrl(){
  return Mage::getUrl($this->getConfigData('api_return_url', 'Webpay/express/return'));
 }

 public function getCancelUrl(){
  return Mage::getUrl($this->getConfigData('api_cancel_url', 'Webpay/express/cancel'));
 }

 public function getUserAction(){
  return $this->getSessionData('user_action', self::USER_ACTION_CONTINUE);
 }

 public function setUserAction($data){
  return $this->setSessionData('user_action', $data);
 }

 public function getTransactionId(){
  return $this->getSessionData('transaction_id');
 }

 public function setTransactionId($data){
  return $this->setSessionData('transaction_id', $data);
 }

 public function getAuthorizationId(){
  return $this->getSessionData('authorization_id');
 }

 public function setAuthorizationId($data){
  return $this->setSessionData('authorization_id', $data);
 }

 public function getPayerId(){
  return $this->getSessionData('payer_id');
 }

 public function setPayerId($data){
  return $this->setSessionData('payer_id', $data);
 }

 public function getCompleteType(){
  return $this->getSessionData('complete_type');
 }

 public function setCompleteType($data){
  return $this->setSessionData('complete_type', $data);
 }

 public function getPaymentType(){
  return $this->getSessionData('payment_type');
 }

 public function setPaymentType($data){
  return $this->setSessionData('payment_type', $data);
 }

 public function getAmount(){
  return $this->getSessionData('amount');
 }

 public function setAmount($data){
  $data = sprintf('%.2f', $data);
  return $this->setSessionData('amount', $data);
 }

 public function getCurrencyCode(){
  return $this->getSessionData('currency_code', Mage::app()->getStore()->getBaseCurrencyCode());
 }

 public function setCurrencyCode($data){
  return $this->setSessionData('currency_code', $data);
 }

 public function getError(){
  return $this->getSessionData('error');
 }

 public function setError($data) {
  return $this->setSessionData('error', $data);
 }

 public function unsError(){
  return $this->setSessionData('error', null);
 }
	
}