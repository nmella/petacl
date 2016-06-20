<?php
//Modulo de Pago de Servipag para Mangento
//Versi�n 0.0.1 
//Fecha �ltima Modificaci�n: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

class Mage_Servipag_Model_Source_StandardAction{
 public function toOptionArray(){
  return array(
  array('value' => Mage_Servipag_Model_Standard::PAYMENT_TYPE_AUTH, 'label' => Mage::helper('Servipag')->__('Authorization')),
  array('value' => Mage_Servipag_Model_Standard::PAYMENT_TYPE_SALE, 'label' => Mage::helper('Servipag')->__('Sale')),
  );
 }
}
?>