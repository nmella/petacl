<?php 
ini_set('display_errors', 'on'); 
error_reporting(E_ALL);
include("includes_webpay/configuration.php");
$path = getcwd();
echo $path;

$database = mysqli_connect($mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db) or die('not connecting');
//$row=$database->query("SELECT * from webpay");
//var_dump($row);


// Realizar una consulta MySQL

$query = "CREATE TABLE IF NOT EXISTS `webpay` (
  `Tbk_tipo_transaccion` varchar(200) NOT NULL,
  `Tbk_respuesta` varchar(200) NOT NULL,
  `Tbk_orden_compra` varchar(200) NOT NULL,
  `Tbk_id_sesion` varchar(200) NOT NULL,
  `Tbk_codigo_autorizacion` varchar(200) NOT NULL,
  `Tbk_monto` varchar(200) NOT NULL,
  `Tbk_numero_tarjeta` varchar(200) NOT NULL,
  `Tbk_numero_final_tarjeta` varchar(200) NOT NULL,
  `Tbk_fecha_expiracion` date NOT NULL,
  `Tbk_fecha_contable` date NOT NULL,
  `Tbk_fecha_transaccion` varchar(200) NOT NULL,
  `Tbk_hora_transaccion` varchar(200) NOT NULL,
  `Tbk_id_transaccion` varchar(200) NOT NULL,
  `Tbk_tipo_pago` varchar(200) NOT NULL,
  `Tbk_numero_cuotas` varchar(200) NOT NULL,
  `Tbk_mac` varchar(200) NOT NULL,
  `Tbk_monto_cuota` varchar(200) NOT NULL,
  `Tbk_tasa_interes_max` varchar(200) NOT NULL,
  `Tbk_ip` varchar(200) NOT NULL,
  UNIQUE KEY `Tbk_tipo_transaccion` (`Tbk_tipo_transaccion`,`Tbk_respuesta`,`Tbk_orden_compra`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$database->query($query);
		
$row=$database->query("SELECT * from webpay");
var_dump($row);


exit();
/*



include("includes_webpay/database.php");
require_once( 'includes_webpay/phpmailer/class.phpmailer.php' );
$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );

*/
/*************************************RECOGE VALORES PARA INSERTARLOS EN LA TABLA WEBPAY******/

$query = "CREATE TABLE IF NOT EXISTS `webpay` (
  `Tbk_tipo_transaccion` varchar(200) NOT NULL,
  `Tbk_respuesta` varchar(200) NOT NULL,
  `Tbk_orden_compra` varchar(200) NOT NULL,
  `Tbk_id_sesion` varchar(200) NOT NULL,
  `Tbk_codigo_autorizacion` varchar(200) NOT NULL,
  `Tbk_monto` varchar(200) NOT NULL,
  `Tbk_numero_tarjeta` varchar(200) NOT NULL,
  `Tbk_numero_final_tarjeta` varchar(200) NOT NULL,
  `Tbk_fecha_expiracion` date NOT NULL,
  `Tbk_fecha_contable` date NOT NULL,
  `Tbk_fecha_transaccion` varchar(200) NOT NULL,
  `Tbk_hora_transaccion` varchar(200) NOT NULL,
  `Tbk_id_transaccion` varchar(200) NOT NULL,
  `Tbk_tipo_pago` varchar(200) NOT NULL,
  `Tbk_numero_cuotas` varchar(200) NOT NULL,
  `Tbk_mac` varchar(200) NOT NULL,
  `Tbk_monto_cuota` varchar(200) NOT NULL,
  `Tbk_tasa_interes_max` varchar(200) NOT NULL,
  `Tbk_ip` varchar(200) NOT NULL,
  UNIQUE KEY `Tbk_tipo_transaccion` (`Tbk_tipo_transaccion`,`Tbk_respuesta`,`Tbk_orden_compra`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		

//$path = getcwd();
//echo $path;
?>
