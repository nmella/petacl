<?php
$id='';
if (isset($_POST['TBK_ORDEN_COMPRA'])){$id=$_POST['TBK_ORDEN_COMPRA'];}

//fracaso
//$link="http://www.k3technology.cl/index.php?option=com_content&view=article&id=45:pagoincompleto:pago-incompleto&Itemid=206";
$http="http://";
		if (isset($_SERVER['HTTPS'])) {
    $http="https://";
		}


?> 
<script type="text/javascript">
window.location="<?php echo $http.$_SERVER['SERVER_NAME'];?>/index.php/checkout/onepage/failure";
</script>  
  
        		<!-- AKI-->   <h2>Webpay</h2>	
	 <!-- AKI-
	
<p style="color: rgb(255, 0, 0); font-weight:bold">Transacción fracasada</p>
<p>Su transacción número <?php echo $id;?> no ha podido ser procesada </p>
<p>Las posibles causas de este rechazo son:<br />
  - Error en el ingreso de los datos de su tarjeta de crédito o debito (fecha y/o código de seguridad).<br />
  - Su tarjeta de crédito o debito no cuenta con el cupo necesario para cancelar la compra.<br />
  - Tarjeta aún no habilitada en el sistema financiero. <br />
 
</p>
<p>Si desea confirmar su compra porfavor contáctese con ventas@peta.cl</p>
<p>&nbsp;</p>
 AKI-->     