<?php
/****************************************************************/
// Modulo:	Validacion webpay para Joomla 1.5.x + Virtuemart
// Versión: 2.0
// Autor: 	Victor Araya Henriquez
// 			Ingeniero en Informatica 
// 			varaya_2000@yahoo.com
// Mejoras: validaciones adicionales para revición de check mac
/****************************************************************/
include("includes_webpay/configuration.php");
include("includes_webpay/database.php");
require_once( 'includes_webpay/phpmailer/class.phpmailer.php' );
$database = new database( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
$id=$_POST['TBK_ORDEN_COMPRA'];
$query_RS_Busca = "select * from webpay where Tbk_orden_compra='".$id."' and Tbk_respuesta ='0' limit 0,1"; 
$database->setQuery( $query_RS_Busca );
$rows = $database->loadObjectList();
if (count($rows)==0) {
	header("Location: webpay_fracaso.php"); 
}

$row=$rows[0];
/*****Nombre comprador*******/
$query_RS_Busca = "SELECT * FROM `wp_wpsc_submited_form_data` where log_id='".$id
									."' and form_id in (2,3) Order by form_id"; 
$database->setQuery( $query_RS_Busca );
$rows2 = $database->loadObjectList();
$comprador= htmlentities($rows2[0]->value . " " .$rows2[1]->value);
/*****Detalle de la compra*******/
$query_RS_Busca = "SELECT * FROM `wp_wpsc_cart_contents` where purchaseid=".$id; 
$database->setQuery( $query_RS_Busca );
$rows2 = $database->loadObjectList();
$productos="";
for ($j=0;$j<count($rows2);$j++){
	$productos=$productos. $rows2[$j]->quantity." " .htmlentities($rows2[$j]->name)."<br>";
	}
/******************/


$TBK_FINAL_NUMERO_TARJETA=$row->Tbk_numero_final_tarjeta;
$TBK_ORDEN_COMPRA=$_POST['TBK_ORDEN_COMPRA'];
$Comercio="santosydiablitos";
$url="http://www.santosydiablitos.cl";
$trs_monto = substr($row->Tbk_monto,0,-3);
$dateArray=explode('-',$row->Tbk_fecha_transaccion);
$trs_fecha_transaccion = $dateArray[2]."/".$dateArray[1]."/".$dateArray[0]; 

//$trs_hora_transaccion = $_POST['TBK_HORA_TRANSACCION'];
$TBK_CODIGO_AUTORIZACION = $row->Tbk_codigo_autorizacion ;
$TIPO_TRANSACCION="Venta";
$trs_tipo_pago = $row->Tbk_tipo_pago; 
$trs_nro_cuotas = $row->Tbk_numero_cuotas;
if ($trs_nro_cuotas=='0'){$trs_nro_cuotas='00';}
$tipo_pago_descripcion="";
if ($trs_tipo_pago=="VN"){	$tipo_pago_descripcion=" Sin Cuotas";}
if ($trs_tipo_pago=="VC"){	$tipo_pago_descripcion=" Normales";}
if ($trs_tipo_pago=="SI"){	$tipo_pago_descripcion=" Sin inter&eacute;s";}
if ($trs_tipo_pago=="CIC"){	$tipo_pago_descripcion=" Cuotas Comercio";}


$link="http://www.santosydiablitos.cl/index.php?page=account.order_details&order_id=".$id."&option=com_virtuemart&Itemid=56";

?>

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="es-ES">
<head profile="http://gmpg.org/xfn/11">
	<title>Webpay | Santos y Diablitos</title>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<meta name="robots" content="index,follow" />

	<link rel="canonical" href="http://www.santosydiablitos.cl/catalogo/" />

	<link rel="stylesheet" type="text/css" href="http://www.santosydiablitos.cl/wp-content/themes/syd/style.css" />

	<link rel="alternate" type="application/rss+xml" href="http://www.santosydiablitos.cl/feed/" title="Santos y Diablitos Canal RSS de entradas" />
	<link rel="alternate" type="application/rss+xml" href="http://www.santosydiablitos.cl/comments/feed/" title="Santos y Diablitos Canal RSS de comentarios" />

	<link rel="pingback" href="http://www.santosydiablitos.cl/xmlrpc.php" />

<meta name='Admin Management Xtended WordPress plugin' content='2.1.1' />
<link rel="alternate" type="application/rss+xml" title="Santos y Diablitos &raquo; 3 a 6 meses RSS de los comentarios" href="http://www.santosydiablitos.cl/catalogo/feed/" />
<link rel='stylesheet' id='slideshow-gallery-css'  href='http://www.santosydiablitos.cl/wp-content/plugins/slideshow-gallery/css/gallery-css.php?1=1&amp;resizeimages=N&amp;width=720&amp;height=220&amp;border=1px+solid+%23CCCCCC&amp;background=%23000000&amp;infobackground=%23000000&amp;infocolor=%23FFFFFF&#038;ver=1.0' type='text/css' media='screen' />
<link rel='stylesheet' id='wpsc-theme-css-css'  href='http://www.santosydiablitos.cl/wp-content/uploads/wpsc/themes/syd/syd.css?ver=3.7.53' type='text/css' media='all' />
<link rel='stylesheet' id='wpsc-theme-css-compatibility-css'  href='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/themes/compatibility.css?ver=3.7.53' type='text/css' media='all' />
<link rel='stylesheet' id='wpsc-product-rater-css'  href='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/product_rater.css?ver=3.7.53' type='text/css' media='all' />
<link rel='stylesheet' id='wp-e-commerce-dynamic-css'  href='http://www.santosydiablitos.cl/index.php?wpsc_user_dynamic_css=true&#038;category&#038;ver=3.7.53' type='text/css' media='all' />
<link rel='stylesheet' id='wpsc-thickbox-css'  href='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/thickbox.css?ver=3.7.53' type='text/css' media='all' />

<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-includes/js/prototype.js?ver=1.6'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-includes/js/scriptaculous/wp-scriptaculous.js?ver=1.8.0'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-includes/js/scriptaculous/effects.js?ver=1.8.0'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/lightbox-2/lightbox-resize.js?ver=1.8'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-includes/js/jquery/jquery.js?ver=1.3.2'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/slideshow-gallery/js/gallery.js?ver=1.0'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/kimili-flash-embed/js/swfobject.js?ver=2.2'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/wp-e-commerce.js?ver=3.7.53'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/ajax.js?ver=3.7.53'></script>

<script type='text/javascript' src='http://www.santosydiablitos.cl/index.php?wpsc_user_dynamic_js=true&#038;ver=3.7.53'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/wpsc-admin/js/jquery.livequery.js?ver=1.0.3'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/jquery.rating.js?ver=3.7.53'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/user.js?ver=3.753'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-e-commerce/js/thickbox.js?ver=Instinct_e-commerce'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-includes/js/comment-reply.js?ver=20090102'></script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/google-analyticator/external-tracking.min.js?ver=6.1.1'></script>

<script type="text/javascript" charset="utf-8">

	/**
	 * Courtesy of Kimili Flash Embed - Version 2.1.4
	 * by Michael Bester - http://kimili.com
	 */

	(function(){
		try {
			// Disabling SWFObject's Autohide feature
			if (typeof swfobject.switchOffAutoHideShow === "function") {
				swfobject.switchOffAutoHideShow();
			}
		} catch(e) {}
	})();
</script>

<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://www.santosydiablitos.cl/xmlrpc.php?rsd" />
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://www.santosydiablitos.cl/wp-includes/wlwmanifest.xml" /> 
<link rel='index' title='Santos y Diablitos' href='http://www.santosydiablitos.cl' />


<!-- All in One SEO Pack 1.6.11 by Michael Torbert of Semper Fi Web Design[230,248] -->
<link rel="canonical" href="http://www.santosydiablitos.cl/catalogo/3-a-6-meses/" />
<!-- /all in one seo pack -->
    <script language='JavaScript' type='text/javascript'>
    var TXT_WPSC_PRODUCTIMAGE = 'Product Image';

var TXT_WPSC_USEDEFAULTHEIGHTANDWIDTH = 'TXT_WPSC_USEDEFAULTHEIGHTANDWIDTH';

var TXT_WPSC_USE = 'use';

var TXT_WPSC_PXHEIGHTBY = 'TXT_WPSC_PXHEIGHTBY';

var TXT_WPSC_PXWIDTH = 'px width';

    </script>
    <script src="http://www.santosydiablitos.cl/wp-content/plugins/gold_cart_files_plugin/gold_cart.js" language='JavaScript' type="text/javascript"></script>
      <link href='http://www.santosydiablitos.cl/wp-content/plugins/gold_cart_files_plugin/gold_cart.css' rel="stylesheet" type="text/css" />
          
      <link href='http://www.santosydiablitos.cl/wp-content/plugins/gold_cart_files_plugin/grid_view.css' rel="stylesheet" type="text/css" />

      
	<!-- begin lightbox scripts -->
	<script type="text/javascript">
    //<![CDATA[
    document.write('<link rel="stylesheet" href="http://www.santosydiablitos.cl/wp-content/plugins/lightbox-2/Themes/Dark Grey/lightbox.css" type="text/css" media="screen" />');
    //]]>
    </script>
	<!-- end lightbox scripts -->
<link rel='alternate' type='application/rss+xml' title='Santos y Diablitos Product List RSS' href='/catalogo/3-a-6-meses/?wpsc_action=rss'/> 
	<script type="text/javascript">
	 //<![CDATA[ 
	function toggleLinkGrp(id) {
	   var e = document.getElementById(id);
	   if(e.style.display == 'block')
			e.style.display = 'none';
	   else
			e.style.display = 'block';
	}
	// ]]>
	</script> 
	    <link rel="shortcut icon" href="http://www.santosydiablitos.cl/wp-content/themes/syd/img/favicon.ico" />

	<script type="text/javascript" src="http://www.santosydiablitos.cl/wp-content/themes/thematic/library/scripts/hoverIntent.js"></script>
	<script type="text/javascript" src="http://www.santosydiablitos.cl/wp-content/themes/thematic/library/scripts/superfish.js"></script>

	<script type="text/javascript" src="http://www.santosydiablitos.cl/wp-content/themes/thematic/library/scripts/supersubs.js"></script>
    <script type="text/javascript">
    jQuery.noConflict();
    jQuery(document).ready(function(){ 
        jQuery(".sf-menu").supersubs({ 
            minWidth:    20,                                // minimum width of sub-menus in em units 
            maxWidth:    22,                                // maximum width of sub-menus in em units 
            extraWidth:  2                                  // extra width can ensure lines don't sometimes turn over 
                                                            // due to slight rounding differences and font-family 
        }).superfish({ 
            delay:       100,                               // delay on mouseout 
            animation:   {opacity:'show',height:'show'},    // fade-in and slide-down animation 
			speed:       'fast',                            // faster animation speed 
			autoArrows:  true,                              // enabled generation of arrow mark-up 
			dropShadows: true                               // enabled drop shadows 
        }); 
    });
    </script>
 
	<script type="text/javascript">
		jQuery.noConflict();
	</script>
<!-- Vipers Video Quicktags v6.2.14 | http://www.viper007bond.com/wordpress-plugins/vipers-video-quicktags/ -->
<style type="text/css">
.vvqbox { display: block; max-width: 100%; visibility: visible !important; margin: 10px auto; } .vvqbox img { max-width: 100%; height: 100%; } .vvqbox object { max-width: 100%; } 
</style>
<script type="text/javascript">
// <![CDATA[
	var vvqflashvars = {};
	var vvqparams = { wmode: "opaque", allowfullscreen: "true", allowscriptacess: "always" };
	var vvqattributes = {};
	var vvqexpressinstall = "http://www.santosydiablitos.cl/wp-content/plugins/vipers-video-quicktags/resources/expressinstall.swf";
// ]]>
</script>
<link rel='canonical' href='http://www.santosydiablitos.cl/catalogo/3-a-6-meses/' />
<!-- Google Analytics Tracking by Google Analyticator 6.1.1: http://ronaldheft.com/code/analyticator/ -->
<script type="text/javascript">
	var analyticsFileTypes = [''];
	var analyticsEventTracking = 'enabled';

</script>
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-17237455-1']);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>

</head>


<body class="wordpress y2012 m02 d02 h11 singular slug-catalogo page pageid-7 page-author-admin page-comments-open page-pings-open loggedin windows firefox ff8 wpsc wpsc-category wpsc-category-2 wpsc-group-1">
    

<div id="wrapper" class="hfeed">

       

    <div id="header">
    
        		    	<div id="branding">
				    		<div id="blog-title"><span><a href="http://www.santosydiablitos.cl/" title="Santos y Diablitos" rel="home">Santos y Diablitos</a></span></div>

			
		    		<div id="blog-description">Ropa para Bebes y Niños</div>
		    				    	</div><!--  #branding -->
				    	<div id="access">
		    		<div class="skip-link"><a href="#content" title="Saltar la navegación al contenido">Saltar al contenido</a></div>
		            <div class="menu"><ul class="sf-menu"><li class="page_item page-item-30"><a href="http://www.santosydiablitos.cl/santos-y-diablitos/" title="Santos y Diablitos">Santos y Diablitos</a><ul><li class="page_item page-item-34"><a href="http://www.santosydiablitos.cl/santos-y-diablitos/comercio-justo/" title="Comercio Justo">Comercio Justo</a></li><li class="page_item page-item-37"><a href="http://www.santosydiablitos.cl/santos-y-diablitos/nosotras/" title="Nosotras">Nosotras</a></li></ul></li><li class="page_item page-item-39"><a href="http://www.santosydiablitos.cl/nuestras-telas/" title="Nuestras Telas">Nuestras Telas</a><ul><li class="page_item page-item-41"><a href="http://www.santosydiablitos.cl/nuestras-telas/nuestras-tallas/" title="Nuestras Tallas">Nuestras Tallas</a></li></ul></li><li class="page_item page-item-43"><a href="http://www.santosydiablitos.cl/como-comprar/" title="Cómo Comprar">Cómo Comprar</a><ul><li class="page_item page-item-50"><a href="http://www.santosydiablitos.cl/como-comprar/envios/" title="Envíos">Envíos</a></li><li class="page_item page-item-52"><a href="http://www.santosydiablitos.cl/como-comprar/devoluciones-y-cambios/" title="Devoluciones y Cambios">Devoluciones y Cambios</a></li></ul></li><li class="page_item page-item-324"><a href="http://www.santosydiablitos.cl/prensa/" title="Prensa">Prensa</a><ul><li class="page_item page-item-350"><a href="http://www.santosydiablitos.cl/noticias/eventos/" title="Eventos">Eventos</a></li></ul></li><li class="page_item page-item-769"><a href="http://www.santosydiablitos.cl/tiendas/" title="Tiendas">Tiendas</a></li><li class="page_item page-item-58"><a href="http://www.santosydiablitos.cl/mayoristas/" title="Mayoristas">Mayoristas</a></li></ul></div>

		        </div><!-- #access -->
		<div id="header-aside" class="aside">
<ul class="xoxo">
<li id="shopping-cart" class="widgetcontainer widget_wp_shopping_cart"><h3 class="widgettitle">Carro de Compra</h3>
    <div id='sliding_cart' class='shopping-cart-wrapper' >
<p class='gocheckout bolsa-syd'>
    <a target='_parent' href='http://www.santosydiablitos.cl/carro-de-compra/'>ver carro &raquo;</a>
</p>

<p class='items'>
    <span class='cartcount'>

        0    </span>
    <span class='numberitems'>
        Prendas    </span>
</p>

<p class='total'>
    <span class="pricedisplay checkout-total">
        &#036;  0    </span>

</p>

<p class='gocheckout'>
    <a target='_parent' href='http://www.santosydiablitos.cl/carro-de-compra/'>ver carro &raquo;</a>
</p>

    </div></li>
<li id="linkcat-8" class="widgetcontainer widget_mylinkorder"><h3 class="widgettitle">Encuéntranos</h3>

	<ul class='xoxo blogroll'>
<li><a href="http://es-la.facebook.com/pages/Santos-Diablitos/109924819029155" rel="me" title="Házte Fan de Santos y Diablitos en Facebook!" target="_blank"><img src="http://www.santosydiablitos.cl/wp-content/uploads/header-boton-facebook.png"  alt="Facebook"  title="Házte Fan de Santos y Diablitos en Facebook!" /> Facebook</a></li>

<li><a href="http://twitter.com/santosydiablito" title="estamos en Twitter" target="_blank"><img src="http://www.santosydiablitos.cl/wp-content/uploads/twitter.jpg"  alt="Twitter"  title="estamos en Twitter" /> Twitter</a></li>
<li><a href="http://www.santosydiablitos.cl/feed/" rel="me" title="Santos y Diablitos RSS"><img src="http://www.santosydiablitos.cl/wp-content/uploads/header-boton-feed.png"  alt="rss"  title="Santos y Diablitos RSS" /> rss</a></li>
<li><a href="http://www.santosydiablitos.cl/link/" rel="me" title="Te recomendamos visitar a nuestros amigos…"><img src="http://www.santosydiablitos.cl/wp-content/uploads/header-boton-link.png"  alt="Link"  title="Te recomendamos visitar a nuestros amigos…" /> Link</a></li>
<li><a href="http://www.santosydiablitos.cl/contacto/" rel="me" title="Contáctanos, llena el formulario!"><img src="http://www.santosydiablitos.cl/wp-content/uploads/header-boton-contacto.png"  alt="Contacto"  title="Contáctanos, llena el formulario!" /> Contacto</a></li>

	</ul>
</li>

</ul>

</div><!-- #header-aside .aside -->
        
    </div><!-- #header-->
    
       

    <div id="main">
    
	<div id="container">
		<div id="content">
	
	
		<!-- AKI-->
	<p style="color: rgb(255, 0, 0); font-weight:bold">Transacci&oacute;n Realizada correctamente </p>
											
												  <img src="http://www.racle.cl/web-pay-adq.gif" />
                                         
            <table width="100%" cellspacing="0" cellpadding="0">
<tr>
<td colspan="2" align="center" class="list_categoria" ><p style="color: rgb(255, 0, 0);"><strong>Datos de la Transacci&oacute;n</strong></p>  </td>
</tr>
<tr>
  <td width="16%" align="left" class="list_categoria" >Tarjeta de Cr&eacute;dito : <br /></td>
  <td width="17%" align="center" class="list_categoria" ><div align="left">XXXX - <?php echo $TBK_FINAL_NUMERO_TARJETA;?></div>    </td>
</tr>
<tr>
  <td class="list_categoria" align="center" ><div align="left">N&ordm; del Pedido: <br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $TBK_ORDEN_COMPRA;?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Nombre del Comercio:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $Comercio;?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">URL del comercio:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $url;?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Monto:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php   echo " $".number_format($trs_monto, 0, ",", ".")."";	 ?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Moneda: <strong> </strong><br />
  </div></td>
  <td class="list_categoria" align="left" ><div align="left">Pesos chilenos </div></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Fecha transacci&oacute;n:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $trs_fecha_transaccion;?> </td>
 </tr>
 <tr>
  <td class="list_categoria" align="left" ><div align="left">Nombre Comprador:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $comprador;?> </td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">C&oacute;digo Autorizaci&oacute;n:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $TBK_CODIGO_AUTORIZACION;?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Tipo de transacci&oacute;n:<br />
  </div></td>
  <td class="list_categoria" align="left" > <div align="left">Venta</div></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Tipo de Cuota:<br />
  </div></td>
  <td class="list_categoria" align="left" ><?php echo $tipo_pago_descripcion;?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Numero de cuotas:</div></td>
  <td class="list_categoria" align="left" ><div align="left"></div>    <?php echo $trs_nro_cuotas;?></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Descripci&oacute;n de los Bienes y Servicios </div></td>
  <td class="list_categoria" align="left" ><div align="left"><?php echo $productos;?></div></td>
</tr>
<tr>
  <td class="list_categoria" align="left" ><div align="left">Revise recctriciones con respecto a  devoluciones y reembolsos.</div></td>
  <td class="list_categoria" align="left" ><div align="left">
  <a class="thickbox" target="_blank" href="http://www.santosydiablitos.cl?termsandconds=true&amp;width=360&amp;height=400" '="">Términos y Condiciones</a>
  
  </div></td>
						  </tr>
</table>
<!-- AKI-->
	
	</div>

	</div><!-- #container -->


<div id="primary" class="aside main-aside">
	<ul class="xoxo">
<li id="wpsc_categorisation-3" class="widgetcontainer widget_wpsc_categorisation"><h3 class="widgettitle">Catálogo</h3>

<div class='wpsc_categorisation_group' id='categorisation_group_1'>
		<h4 class='wpsc_category_title'>Rango de Edad</h4>
		
			<ul class='wpsc_categories wpsc_top_level_categories '>

								<li class='wpsc_category_1'>
						<a href="http://www.santosydiablitos.cl/catalogo/0-a-3-meses/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/0-a-3-meses/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-1">
							0 a 3 meses													</a>
																	</li>
								<li class='wpsc_category_2'>
						<a href="http://www.santosydiablitos.cl/catalogo/3-a-6-meses/" class='wpsc_category_image_link'>

													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/3-a-6-meses/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-2 wpsc-current-cat wpsc-cat-ancestor">
							3 a 6 meses													</a>
																	</li>
								<li class='wpsc_category_3'>
						<a href="http://www.santosydiablitos.cl/catalogo/6-a-12-meses/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/6-a-12-meses/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-3">
							6 a 12 meses													</a>
																	</li>
								<li class='wpsc_category_4'>
						<a href="http://www.santosydiablitos.cl/catalogo/1-a-2-anos/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/1-a-2-anos/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-4">
							1 a 2 a&ntilde;os													</a>

																	</li>
								<li class='wpsc_category_5'>
						<a href="http://www.santosydiablitos.cl/catalogo/2-a-3-anos/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/2-a-3-anos/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-5">
							2 a 3 a&ntilde;os													</a>
																	</li>

								<li class='wpsc_category_25'>
						<a href="http://www.santosydiablitos.cl/catalogo/3-a-4-anos/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/3-a-4-anos/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-25">
							3 a 4 a&ntilde;os													</a>
																	</li>
								<li class='wpsc_category_42'>

						<a href="http://www.santosydiablitos.cl/catalogo/4-a-6-anos/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/4-a-6-anos/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-42">
							4 a 6 a&ntilde;os													</a>
																	</li>
						
		</ul>
		<div class='clear_category_group'></div>

</div>
<div class='wpsc_categorisation_group' id='categorisation_group_2'>
		<h4 class='wpsc_category_title'>Tipos de Prenda</h4>
		
			<ul class='wpsc_categories wpsc_top_level_categories '>
								<li class='wpsc_category_32'>
						<a href="http://www.santosydiablitos.cl/catalogo/regalos-recien-nacido/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/regalos-recien-nacido/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-32">
							Regalos Recien Nacido													</a>

																	</li>
								<li class='wpsc_category_43'>
						<a href="http://www.santosydiablitos.cl/catalogo/calzones-y-boxer/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/calzones-y-boxer/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-43">
							Calzones y Boxer													</a>
																	</li>
								<li class='wpsc_category_44'>

						<a href="http://www.santosydiablitos.cl/catalogo/conjuntos-bebes/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/conjuntos-bebes/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-44">
							Conjuntos Beb&eacute;s													</a>
																	</li>
								<li class='wpsc_category_45'>
						<a href="http://www.santosydiablitos.cl/catalogo/pijamas-bebes/" class='wpsc_category_image_link'>

													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/pijamas-bebes/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-45">
							Pijamas Beb&eacute;s													</a>
																	</li>
								<li class='wpsc_category_46'>
						<a href="http://www.santosydiablitos.cl/catalogo/conjuntos-ninos/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/conjuntos-ninos/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-46">
							Conjuntos Ni&ntilde;os													</a>
																	</li>
								<li class='wpsc_category_48'>
						<a href="http://www.santosydiablitos.cl/catalogo/liquidacion-verano1/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/liquidacion-verano1/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-48">

							LIQUIDACI&Oacute;N VERANO													</a>
																	</li>
								<li class='wpsc_category_29'>
						<a href="http://www.santosydiablitos.cl/catalogo/conjuntos-ninas/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/conjuntos-ninas/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-29">
							Conjuntos Ni&ntilde;as													</a>

																	</li>
								<li class='wpsc_category_41'>
						<a href="http://www.santosydiablitos.cl/catalogo/piluchos-manga-corta/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/piluchos-manga-corta/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-41">
							Piluchos Manga Corta 													</a>
																	</li>
								<li class='wpsc_category_13'>

						<a href="http://www.santosydiablitos.cl/catalogo/piluchos-manga-larga/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/piluchos-manga-larga/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-13">
							Piluchos Manga Larga													</a>
																	</li>
								<li class='wpsc_category_40'>
						<a href="http://www.santosydiablitos.cl/catalogo/poleras-musculosas/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/poleras-musculosas/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-40">
							Poleras - Musculosas													</a>
																	</li>
								<li class='wpsc_category_8'>
						<a href="http://www.santosydiablitos.cl/catalogo/bombachos-bermudas/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/bombachos-bermudas/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-8">

							Bombachos - Bermudas													</a>
																	</li>
								<li class='wpsc_category_17'>
						<a href="http://www.santosydiablitos.cl/catalogo/poleras-manga-larga/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/poleras-manga-larga/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-17">
							Poleras Manga Larga													</a>

																	</li>
								<li class='wpsc_category_38'>
						<a href="http://www.santosydiablitos.cl/catalogo/vestidos-faldas-short/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/vestidos-faldas-short/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-38">
							Vestidos - Faldas - Short													</a>
																	</li>
								<li class='wpsc_category_18'>

						<a href="http://www.santosydiablitos.cl/catalogo/polerones-chaqueta/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/polerones-chaqueta/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-18">
							Polerones - Chaqueta													</a>
																	</li>
								<li class='wpsc_category_31'>
						<a href="http://www.santosydiablitos.cl/catalogo/baberos/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/baberos/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-31">
							Baberos													</a>
																	</li>
								<li class='wpsc_category_23'>
						<a href="http://www.santosydiablitos.cl/catalogo/bolsas-para-regalo/" class='wpsc_category_image_link'>
													</a>

						<a href="http://www.santosydiablitos.cl/catalogo/bolsas-para-regalo/" class="wpsc_category_link wpsc-cat-item wpsc-cat-item-23">

							Bolsas para Regalo													</a>
																	</li>
						
		</ul>
		<div class='clear_category_group'></div>
</div></li>
	</ul>
</div><!-- #primary .aside -->

    </div><!-- #main -->
    
        

	<div id="footer">

    
                    
            <div id="subsidiary">
            
        
<div id="first" class="aside sub-aside">
	<ul class="xoxo">
<li id="wp_bannerize-6" class="widgetcontainer widget_wp_bannerize"><div class="wp_bannerize"><ul><li ><a  href="http://es-la.facebook.com/pages/Santos-Diablitos/109924819029155"><img width="280" height="100" alt="" src="http://www.santosydiablitos.cl/wp-content/uploads/banner5.jpg" /></a></li><li class="alt"><a  href="http://www.santosydiablitos.cl/catalogo/bolsas-para-regalo/packaging-santos-y-diablitos/"><img width="280" height="100" alt="nuestro packaging" src="http://www.santosydiablitos.cl/wp-content/uploads/bolsas.gif" /></a></li></ul></div></li>
	</ul>
</div><!-- #first .aside -->

<div id="second" class="aside sub-aside">
	<ul class="xoxo">
<li id="wp_bannerize-7" class="widgetcontainer widget_wp_bannerize"><div class="wp_bannerize"><ul><li ><a target="_blank" href="http://www.santosydiablitos.cl/catalogo/conjuntos-ninos/"><img width="280" height="218" alt="" src="http://www.santosydiablitos.cl/wp-content/uploads/caluga_conj.jpg" /></a></li></ul></div></li>
	</ul>

</div><!-- #second .aside -->

<div id="third" class="aside sub-aside">
	<ul class="xoxo">
<li id="text-3" class="widgetcontainer widget_text">			<div class="textwidget"><div class="wpcf7" id="wpcf7-f3-w2-o1">
<form action="/catalogo/3-a-6-meses/#wpcf7-f3-w2-o1" method="post" class="wpcf7-form">
<div style="display: none;">
<input type="hidden" name="_wpcf7" value="3" />
<input type="hidden" name="_wpcf7_version" value="2.2.1" />
<input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f3-w2-o1" />
</div>
<p>Nombre y Apellido<span class="wpcf7-form-control-wrap nombreyapellido"><br />
<input type="text" name="nombreyapellido" value="" class="wpcf7-validates-as-required" size="40" /></span></p>
<p>E-mail<span class="wpcf7-form-control-wrap email"><br />

<input type="text" name="email" value="" class="wpcf7-validates-as-email wpcf7-validates-as-required" size="40" /></span></p>
<p class="enviar">
<input type="submit" value="Enviar" /> <img class="ajax-loader" style="visibility: hidden;" alt="ajax loader" src="http://www.santosydiablitos.cl/wp-content/plugins/contact-form-7/images/ajax-loader.gif" /><span class="sinbr"><span class="wpcf7-form-control-wrap cancelar"><span class="wpcf7-checkbox"><span class="wpcf7-list-item"><br />
<input type="checkbox" name="cancelar[]" value="Deseo cancelar mi suscripción" />&nbsp;<span class="wpcf7-list-item-label">Deseo cancelar mi suscripción</span></span></span></span></span></p>
<div class="wpcf7-response-output wpcf7-display-none"></div>
</form>
</div>
</div>
		</li>
	</ul>
</div><!-- #third .aside -->
            
            </div><!-- #subsidiary -->

            
            
        <div id="siteinfo">        

    <p id="copyright"><strong><span class="blog-title">Santos y Diablitos</span></strong> © <span class="the-year">2012</span> Todos los derechos reservados</a><p id="contacto">
<br />Galería Drugstore, Avda. Providencia 2124 - local 6. Providencia</span><br />Pueblo del Inglés, Avda. Manquehue Norte 169 - local 63. Vitacura</span><br />
<p id="contacto">Teléfono: <span id="fono"><span class="areacode">
(56 2)</span> 234 5687</span><br />

Celular: <span id="fono"><span class="areacode">(56 9)</span> 8139 4470</span><br />
E-mail: <a href="mailto:contacto@santosydiablitos.cl">contacto@santosydiablitos.cl</a></p>
<p id="creditos"><a target="_blank" href="http://renderbox.cl" title="powered by renderbox©" rel="author" class="author-link">powered by renderbox©</a> <a target="_blank" href="http://getshopped.org/" title="WP e-Commerce" rel="plugin" class="wpec-link">Nuestra tienda funciona con WP e-Commerce para WordPress</a> <a target="_blank" href="http://themeshaper.com/thematic/" title="Construido sobre Thematic Theme Framework" rel="designer" class="theme-link">Construido sobre Thematic Theme Framework</a> <a target="_blank" href="http://WordPress.org/" title="Alimentado por WordPress" rel="generator" class="wp-link">Alimentado por WordPress</a></p>
<p id="licencia"><span class="the-year">2012</span> (<a title="Licencia Creative Commons - Atribución 3.0 Unported - Chile" href="http://creativecommons.org/licenses/by/3.0/deed.es_CL" rel="license" target="_blank">CC</a>) Algunos derechos reservados</p>    
		</div><!-- #siteinfo -->

    
            
	</div><!-- #footer -->
	
      

</div><!-- #wrapper .hfeed -->

<script type='text/javascript'>
/* <![CDATA[ */
var wpBannerizeMainL10n = {
	ajaxURL: "http://www.santosydiablitos.cl/wp-content/plugins/wp-bannerize/ajax_clickcounter.php"
};
/* ]]> */
</script>
<script type='text/javascript' src='http://www.santosydiablitos.cl/wp-content/plugins/wp-bannerize/js/wp_bannerize_frontend.js?ver=1.4'></script>

</body>
</html>
