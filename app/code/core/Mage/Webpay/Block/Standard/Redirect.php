<?php
class Mage_Webpay_Block_Standard_Redirect extends Mage_Core_Block_Abstract{
 protected function _toHtml(){
  $standard = Mage::getModel('Webpay/standard');
  $form = new Varien_Data_Form();
  $form->setAction('/cgi-bin/tbk_bp_pago.cgi')
   ->setId('Webpay_standard_checkout')
   ->setName('Webpay_standard_checkout')
   ->setMethod('POST')
   ->setUseContainer(true);
  foreach ($standard->getStandardCheckoutFormFields() as $field=>$value) {
   $form->addField($field, 'hidden', array('name'=>$field, 'value'=>$value));
  }
  $html = '<html><body>';
  $html.= $this->__('Usted sera redirigido a Webpay en pocos segundos.');
  $html.= $form->toHtml();
  $html.= '<script type="text/javascript">document.getElementById("Webpay_standard_checkout").submit();</script>';
  $html.= '</body></html>';
  return $html;
 }
}