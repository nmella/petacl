<?php
class Mage_Webpay_Model_Source_StandardAction{
 public function toOptionArray(){
  return array(
  array('value' => Mage_Webpay_Model_Standard::PAYMENT_TYPE_AUTH, 'label' => Mage::helper('Webpay')->__('Authorization')),
  array('value' => Mage_Webpay_Model_Standard::PAYMENT_TYPE_SALE, 'label' => Mage::helper('Webpay')->__('Sale')),
  );
 }
}
?>