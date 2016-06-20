<?php
abstract class Mage_Webpay_Model_Abstract extends Mage_Payment_Model_Method_Abstract{
 public function getApi(){
  return Mage::getSingleton('Webpay/api_nvp');
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

 public function getRedirectUrl(){
  return $this->getApi()->getRedirectUrl();
 }

 public function getCountryRegionId(){
  $a = $this->getApi()->getShippingAddress();
  return $this;
 }
	
}