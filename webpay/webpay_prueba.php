<?php 
ini_set('display_errors', 'on'); 
include("includes_webpay/configuration.php");
include("includes_webpay/database.php");
require_once( 'includes_webpay/phpmailer/class.phpmailer.php' );
$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );


$query = "SELECT * from webpay where Tbk_orden_compra=100078409 ";
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
	var_dump($rows);	
	
	
	
	
		
?>
<p><b>Pagos </b><br>
  Para finalizar el proceso de compra debes ingresar a Web Pay y realizar el pago con Tarjeta de Credito: Visa, Mastercard, Magna, American Express y Diners Club. 
  Si está todo ok tu pedido será Confirmado Definitivamente<br>
      <img src='web-pay-adq.gif' alt='WebPay'/>	
      <br>
</p>
<form name="form1" method="get" action="xt_1_pago.php">
  <label>Orden de compra
  <input type="text" name="id" id="id"  value="oc_1000">
  <br>
  </label>
   <label>monto pago
   <input type="text" name="monto" id="monto"  value="5000">
  </label>
   <br>
   <label>
   <input type="submit" name="button" id="button" value="pagar">
   </label>
</form>
<p>&nbsp;</p>
