<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 14-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com
class Mage_Servipag_Block_Standard_Form extends Mage_Payment_Block_Form{
 protected function _construct(){
  $this->setTemplate('Servipag/standard/form.phtml');
  parent::_construct();
 }
}
?>
