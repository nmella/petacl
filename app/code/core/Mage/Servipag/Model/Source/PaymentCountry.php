<?
//Modulo de Pago de Servipag para Mangento
//Versi�n 0.0.1 
//Fecha �ltima Modificaci�n: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

class Mage_Servipag_Model_Source_PaymentCountry{
 protected $_options;
 public function toOptionArray(){
  return $options = array('CL' => 'Chile');
 } 
}
?>