<?php
//Modulo de Pago de Servipag para Mangento
//Versión 0.0.1 
//Fecha última Modificación: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

class Mage_Servipag_Model_Mysql4_Api_Debug extends Mage_Core_Model_Mysql4_Abstract{
 protected function _construct(){
  $this->_init('Servipag/api_debug', 'debug_id');
 }
}
?>