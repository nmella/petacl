<p align="justify"></p><?php
/****************************************************************/
// Modulo:	Validacion webpay para 
// Versión: 2.0
// Autor: 	Victor Araya Henriquez
// 			Ingeniero en Informatica 
// 			varaya_2000@yahoo.com
// Mejoras: validaciones adicionales para revición de check mac
/****************************************************************/
//phpinfo();
ini_set('display_errors', 'off'); 
include("includes_webpay/configuration.php");
include("includes_webpay/database.php");
require_once( 'includes_webpay/phpmailer/class.phpmailer.php' );
$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );

/*************************************RECOGE VALORES PARA INSERTARLOS EN LA TABLA WEBPAY******/
	$trs_transaccion = $_POST['TBK_TIPO_TRANSACCION']; $trs_respuesta = $_POST['TBK_RESPUESTA']; $trs_orden_compra = $_POST['TBK_ORDEN_COMPRA']; $trs_id_session = $_POST['TBK_ID_SESION']; $trs_cod_autorizacion = $_POST['TBK_CODIGO_AUTORIZACION'];
	$trs_monto = substr($_POST['TBK_MONTO'],0,-2).".00";
	$trs_nro_final_tarjeta = $_POST['TBK_FINAL_NUMERO_TARJETA'];
	$trs_nro_tarjeta = $_POST['TBK_NUMERO_TARJETA']; $trs_fecha_expiracion = $_POST['TBK_FECHA_EXPIRACION']; $trs_fecha_contable = $_POST['TBK_FECHA_CONTABLE']; $trs_fecha_transaccion = $_POST['TBK_FECHA_TRANSACCION']; $trs_hora_transaccion = $_POST['TBK_HORA_TRANSACCION']; $trs_id_transaccion = $_POST['TBK_ID_TRANSACCION']; $trs_tipo_pago = $_POST['TBK_TIPO_PAGO']; $trs_nro_cuotas = $_POST['TBK_NUMERO_CUOTAS']; $trs_mac = $_POST['TBK_MAC']; $trs_monto_cuota = $_POST['TBK_MONTO_CUOTA']; $trs_tasa_interes_max = $_POST['TBK_TASA_INTERES_MAX']; $trs_fecha_transaccion = ($trs_fecha_transaccion=='') ? strftime('%Y-%m-%d') : strftime('%Y').'-'.substr($trs_fecha_transaccion,0,2).'-'.substr($trs_fecha_transaccion,2,2);
	$trs_fecha_expiracion = ($trs_fecha_expiracion=='') ? strftime('%Y-%m-%d') : strftime('%Y').'-'.substr($trs_fecha_expiracion,0,2).'-'.substr($trs_fecha_expiracion,2,2);
	$trs_fecha_contable = ($trs_fecha_contable=='') ? strftime('%Y-%m-%d') : strftime('%Y').'-'.substr($trs_fecha_contable,0,2).'-'.substr($trs_fecha_contable,2,2);
	$correo='nmella@uc.cl';
	$NombreDestino="Victor";
	$bandera_no_aceptado_banco="";
	
	
/********************Chequeo de MAC************************/	
	global $mosConfig_absolute_path;
	
	
	$filename = $mosConfig_absolute_path."/cgi-bin/log/temporal.txt";
	if( $fp = fopen($filename, "w")) {
		fwrite($fp, $trs_cod_autorizacion);
		fclose($fp);
	}
	/* 1.- Abrir archivo y guardar variables POST recibidas */ 
	$filename = $mosConfig_absolute_path."/cgi-bin/log/log".$trs_id_transaccion.".txt";
	$filename2 = "log".$trs_id_transaccion.".txt";
	$fp=fopen($filename,"w");
	reset($_POST);
	while (list($key,$val) = each($_POST))
	{
		fwrite($fp,"$key=$val&");
	}
	fclose($fp);
	
	
	//$filename2="log738505843.txt";
	
	$resultado = file_get_contents($mosConfig_live_site."/cgi-bin/chkmac.cgi?filename=".$filename2);
	$result[0] = trim($resultado);
	//echo $result[0].$mosConfig_live_site."/cgi-bin/chkmac.cgi?filename=".$filename2;
/* 2.- Invocar a tbk_check_mac (Que en realidad no es una cgi) usando como parámetro el archivo generado */ 
	//$cmdline = $mosConfig_absolute_path ."/cgi-bin/tbk_check_mac.cgi $filename"; 
   //     exec($cmdline,$result,$retint); //echo $result[0];

//$result[0]="CORRECTO";

	if ($result[0]=="CORRECTO") { 
		//echo "ACEPTADO";
		$theValue =  "ACEPTADO"; 
	} else {
		 echo "RECHAZADO"; 
		 $errors="check MAc adress, no coincide con el servidor";
			$sql="insert into webpay (Tbk_tipo_transaccion, Tbk_respuesta, Tbk_orden_compra, Tbk_id_sesion, Tbk_codigo_autorizacion, Tbk_monto, Tbk_numero_tarjeta, Tbk_numero_final_tarjeta, Tbk_fecha_expiracion, Tbk_fecha_contable, Tbk_fecha_transaccion, Tbk_hora_transaccion, Tbk_id_transaccion, Tbk_tipo_pago, Tbk_numero_cuotas, Tbk_mac, Tbk_monto_cuota, Tbk_tasa_interes_max,Tbk_ip)
		Values ('".$trs_transaccion."',
		'".$estado."','".$trs_orden_compra."','".$trs_id_session."','".$trs_cod_autorizacion."','".$trs_monto."','".$trs_nro_tarjeta."',
		'".$trs_nro_final_tarjeta."','".$trs_fecha_expiracion."','".$trs_fecha_contable."','".$trs_fecha_transaccion."','".$trs_hora_transaccion."',
		'".$trs_id_transacion."','".$trs_tipo_pago."','".$trs_nro_cuotas."','".$trs_mac."','".$trs_monto_cuota."','".$trs_tasa_interes_max."',
		'".$_SERVER['REMOTE_ADDR']."')";
		$database->setQuery($sql);
		$rows_tmp = $database->loadObjectList();
	 	exit();
	}
	
/****************REVISA APROBACIONDE TRANSACCION DE WEBPAY SI $trs_respuesta=0***********/
	if ($theValue=="ACEPTADO") {
		$theValue = ($trs_respuesta==0 ) ?  "ACEPTADO": "RECHAZADO"; 
		if ($theValue=="RECHAZADO") {
			$bandera_no_aceptado_banco="si";
			$errors="No aceptado por Webpay";
		}
	}
/****************************************REVISA MONTOS**************************************/
	if ($theValue=="ACEPTADO") {
		$query = "SELECT grand_total FROM `sales_flat_order` WHERE `entity_id` =".$trs_orden_compra." ";
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
		$row=$rows[0];
		$monto_webpay=round($trs_monto);
		$monto_tienda=round($row->grand_total); 
		$theValue = ($monto_webpay!=$monto_tienda) ? "RECHAZADO" : "ACEPTADO"; 
		
		if ($theValue=="RECHAZADO") {
			$estado="Montos no Coinciden";
			/*****CORREO DE LOG******/
			$subject="Montos no corresponde al cancelado por Webpay con respecto al del pedido Numero".$trs_orden_compra;
			$message="Monto Webpay=".$monto_webpay."  Monto Pedido=".$monto_tienda;
			miMail ($mosConfig_mailfrom, $mosConfig_fromname, $correo,$NombreDestino, $subject, $message);
		} else {
			$estado=$trs_respuesta;
		} 
	}
	/****************************VERIFICA SI ESTA PAGADO***************************************/
	if ($theValue=="ACEPTADO"){
		$query_RS_Busca = "select count(*) from webpay where Tbk_orden_compra='".$trs_orden_compra."' and Tbk_respuesta ='0'"; 
		$database->setQuery( $query_RS_Busca );
		$totalRows_RS_Busca = $database->loadResult();
		$theValue = ($totalRows_RS_Busca>0) ? "RECHAZADO" : "ACEPTADO";
		 if ($theValue=="RECHAZADO") {
				$estado="Pedido duplicado";
				/*****CORREO DE LOG******/
				$subject="Numero de orden pedido ".$trs_orden_compra." ya pagado";
				$message="Numero de orden de compra duplicada en el pedido".$trs_orden_compra." query=".$query_RS_Busca;
				miMail ($mosConfig_mailfrom, $mosConfig_fromname, $correo,$NombreDestino, $subject, $message);
		 } else {
			$estado=$trs_respuesta;
		 } 
	} 
/****************************************INSERTA EN TABLA WEBPAY***************************/
	$sql="insert into webpay (Tbk_tipo_transaccion, Tbk_respuesta, Tbk_orden_compra, Tbk_id_sesion, Tbk_codigo_autorizacion, Tbk_monto, Tbk_numero_tarjeta, Tbk_numero_final_tarjeta, Tbk_fecha_expiracion, Tbk_fecha_contable, Tbk_fecha_transaccion, Tbk_hora_transaccion, Tbk_id_transaccion, Tbk_tipo_pago, Tbk_numero_cuotas, Tbk_mac, Tbk_monto_cuota, Tbk_tasa_interes_max,Tbk_ip)
	Values ('".$trs_transaccion."',
	'".$estado."','".$trs_orden_compra."','".$trs_id_session."','".$trs_cod_autorizacion."','".$trs_monto."','".$trs_nro_tarjeta."',
	'".$trs_nro_final_tarjeta."','".$trs_fecha_expiracion."','".$trs_fecha_contable."','".$trs_fecha_transaccion."','".$trs_hora_transaccion."',
	'".$trs_id_transacion."','".$trs_tipo_pago."','".$trs_nro_cuotas."','".$trs_mac."','".$trs_monto_cuota."','".$trs_tasa_interes_max."',
	'".$_SERVER['REMOTE_ADDR']."')";
	$database->setQuery($sql);
	//$database->query();
	$rows_tmp = $database->loadObjectList();
/***********************************************************************************************/
?>
<html>
<?

if ($theValue=="ACEPTADO"){

		//listado admin
		$query="UPDATE `sales_flat_order_grid` SET `status` = 'complete' WHERE `entity_id`  =".$trs_orden_compra ."";
		$database->setQuery($query);
		//$database->query();
		$rows_tmp = $database->loadObjectList();
		
		//dettale pedido frontal
		$query="UPDATE `sales_flat_order` SET `status` = 'complete' WHERE `entity_id` =".$trs_orden_compra ."";
		$database->setQuery($query);
		$rows_tmp = $database->loadObjectList();
		

		echo "ACEPTADO";
		/*********************REGISTRO DE PAGO VIA EMAIL ***********************************************/
			/*********************REGISTRO DE PAGO VIA EMAIL ***********************************************/
		$subject="El Pedido ".$trs_orden_compra." Se ha pagado Via Web Pay en Forma Correcta";
		
		$trs_tipo_pago = $trs_tipo_pago; 
		$trs_nro_cuotas = $trs_nro_cuotas;
		if ($trs_nro_cuotas=='0'){$trs_nro_cuotas='00';}
		$tipo_pago_descripcion="";
		if ($trs_tipo_pago=="VN"){	$tipo_pago_descripcion=" Sin Cuotas";}
		if ($trs_tipo_pago=="VC"){	$tipo_pago_descripcion=" Normales";}
		if ($trs_tipo_pago=="SI"){	$tipo_pago_descripcion=" Sin inter&eacute;s";}
		if ($trs_tipo_pago=="CIC"){	$tipo_pago_descripcion=" Cuotas Comercio";}
		if ($trs_tipo_pago=="VD"){	$tipo_pago_descripcion=" Red Compra";}

	
		$message="El Pedido ".$trs_orden_compra." Se ha pagado Via Web Pay en Forma Correcta <br>
		Cod. Autorización:".$trs_cod_autorizacion."<br>
		Monto:$".$monto_webpay."<br>
		Tipo de pago:".$tipo_pago_descripcion."<br>
		Cuotas:".$trs_nro_cuotas."<br>
		";
		
	//	miMail ($mosConfig_mailfrom, $mosConfig_fromname, $correo,$NombreDestino, $subject, $message);
	

}else{
	if ($bandera_no_aceptado_banco=="si"){//solo en el caso que el banco lo rechazo, para poder enviar a la pagina rechazo
		echo "ACEPTADO"; //ojo pero no a pagado si no que lo envbiara a la pagina fracaso
	}else{
		echo "RECHAZADO"; //caso de montos, orden de compra o mac
	}
}	
?> 
</html> 
<?php
//funcion de cooreo
function miMail($mosConfig_mailfrom, $mosConfig_fromname, $email_destino,$nombre_destino, $subject, $message){
	$corre_asunto=$subject;
	$corre_msj=$message;
	$cabeceras  = "MIME-Version: 1.0\r\n";
	$cabeceras .= "Content-type: text/html; charset=iso-8859-1\r\n";
	/* cabeceras adicionales */
	$cabeceras .= "To:".$nombre_destino." <".$email_destino.">\r\n";
	$cabeceras .= "From: ".$mosConfig_fromname." <".$mosConfig_mailfrom.">\r\n";
	/* y ahora, enviarlo */
	mail($email_destino, $corre_asunto, $corre_msj, $cabeceras);
}
?>
