<?
class Mage_Webpay_Model_Source_PaymentCountry{
 protected $_options;
 public function toOptionArray(){
  return $options = array(
  'AR' => 'Argentina', 
  'BR' => 'Brasil',
  'CL' => 'Chile',
  'MX' => 'México'
	 );
 } 
}
?>