<form style="margin: 0px;" action="http://www.spamund.cl/cgi-bin/tbk_bp_pago.cgi" name="webpay" method="post">
<input type="hidden" value="3634" name="TBK_ID_SESION">
<input type="hidden" value="TR_NORMAL" name="TBK_TIPO_TRANSACCION">
<input type="hidden" value="12100" name="TBK_MONTO">
<input type="hidden" value="3634" name="TBK_ORDEN_COMPRA">
<input type="hidden" value="http://www.spamund.cl/webpay/exito.php" name="TBK_URL_EXITO">
<input type="hidden" value="http://www.spamund.cl/webpay/fracaso.php" name="TBK_URL_FRACASO">
<p>
<img alt="WebPay" src="http://www.spamund.cl/webpay/web-pay-adq.gif">
<br>
<input class="button" type="submit" style="margin: 5px;" value="Ir a Webpay">
<br>
<input class="button" type="button" style="margin: 5px;" onclick="window.location = '/toma-de-hora-spa-mund.html'" value="Volver al inicio">
</form>