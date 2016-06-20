<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

abstract class Mage_Servipag_Model_Abstract extends Mage_Payment_Model_Method_Abstract{
 
 public function getApi(){
  return Mage::getSingleton('Servipag/api_nvp');
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

 public function getRedirectUrl(){
  return $this->getApi()->getRedirectUrl();
 }

 public function getCountryRegionId(){
  $a = $this->getApi()->getShippingAddress();
  return $this;
 }
}