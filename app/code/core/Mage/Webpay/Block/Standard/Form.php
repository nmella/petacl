<?php
class Mage_Webpay_Block_Standard_Form extends Mage_Payment_Block_Form{
 protected function _construct(){
  $this->setTemplate('Webpay/standard/form.phtml');
  parent::_construct();
 }
}
?>
