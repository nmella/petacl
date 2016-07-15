<?php

class GoMage_Xml_IndexController extends Mage_Adminhtml_Controller_Action {
	
	public function generateAction() {

		try {					
			$order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));

			$file_path = Mage::helper('gomage_xml')->getFilePath($order->getIncrementId());
						
			if ($order->getData('field_1') || $order->getData('field_2') || $order->getData('field_3') ||
				$order->getData('field_4') || $order->getData('field_5') || $order->getData('field_6') ){
				$xml_data = $this->getType33Content($order);
			}else{
				$xml_data = $this->getType39Content($order);
			}
	        
	        @file_put_contents($file_path, $xml_data);
			

			Mage::register('current_order', $order);
			$this->loadLayout();			
            $response = $this->getLayout()->getBlock('gomage_xml.admin.content')->toHtml(); 
		}
		catch (Exception $e) {
			 $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot generate file.')
            );
            $response = Mage::helper('core')->jsonEncode($response); 
		}

		$this->getResponse()->setBody($response); 
	
	}
	
	public function saveAction() {
		try {					
			$order = Mage::getModel('sales/order')->load($this->getRequest()->getParam('order_id'));

			$file_path = Mage::helper('gomage_xml')->getFilePath($order->getIncrementId());
			
			$xml_data = $this->getRequest()->getPost('gomage_xml_content'); 
	        
	        @file_put_contents($file_path, $xml_data);
			
			Mage::register('current_order', $order);
			$this->loadLayout();			
            $response = $this->getLayout()->getBlock('gomage_xml.admin.content')->toHtml(); 
		}
		catch (Exception $e) {
			 $response = array(
                'error'     => true,
                'message'   => $this->__('Cannot generate file.')
            );
            $response = Mage::helper('core')->jsonEncode($response); 
		}

		$this->getResponse()->setBody($response);
	}
	
	public function massgenerateAction() {
		
		$orderIds = $this->getRequest()->getPost('order_ids', array());
        $countCancelOrder = 0;
        $countNonCancelOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);     

			$file_path = Mage::helper('gomage_xml')->getFilePath($order->getIncrementId());
								
			if ($order->getData('field_1') || $order->getData('field_2') || $order->getData('field_3') ||
				$order->getData('field_4') || $order->getData('field_5') || $order->getData('field_6')){
				$xml_data = $this->getType33Content($order);
			}else{
				$xml_data = $this->getType39Content($order);
			}
			
			@file_put_contents($file_path, $xml_data);
        }    
		
		$this->_getSession()->addSuccess($this->__('Xml Invoices created.'));
		
        $this->_redirect('backendcontrol/sales_order/index');       
	}
	
	public function getType33Content($order){
		
		$xml = new DOMDocument('1.0', 'ISO-8859-1');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
				
		$Documento = $xml->createElement('Documento');
	    $Documento = $xml->appendChild($Documento);
	    
	    $Encabezado = $xml->createElement('Encabezado');
	    $Encabezado = $Documento->appendChild($Encabezado);
	    
	    //IdDoc
	    $IdDoc = $xml->createElement('IdDoc');
	    $IdDoc = $Encabezado->appendChild($IdDoc);
	    
	    $Folio = intval(Mage::getStoreConfig('gomage_xml/settings/next_starting_33'));
	    Mage::getModel('core/config')->saveConfig('gomage_xml/settings/next_starting_33', $Folio + 1); 
	    Mage::getConfig()->reinit();
        Mage::app()->reinitStores(); 
	    
	    $IdDoc->appendChild($xml->createElement('TipoDTE', 33));
	    $IdDoc->appendChild($xml->createElement('Folio', $Folio));
	    $date_formated = date('Y-m-d', strtotime($order->getCreatedAtDate()));
	    $date_formated_ven = date('Y-m-d', strtotime('+30 days',strtotime($order->getCreatedAtDate())));
	    $IdDoc->appendChild($xml->createElement('FchEmis', $date_formated));
	    $IdDoc->appendChild($xml->createElement('FchVenc', $date_formated_ven));

	    //Emisor
	    $Emisor = $xml->createElement('Emisor');
	    $Emisor = $Encabezado->appendChild($Emisor);
	    
	    $Emisor->appendChild($xml->createElement('RUTEmisor', Mage::getStoreConfig('gomage_xml/company/rut_field')));
	    $Emisor->appendChild($xml->createElement('RznSoc', Mage::getStoreConfig('gomage_xml/company/company_name')));
	    $Emisor->appendChild($xml->createElement('GiroEmis', Mage::getStoreConfig('gomage_xml/company/giro')));
	    $Emisor->appendChild($xml->createElement('Acteco', Mage::getStoreConfig('gomage_xml/company/ateco')));
	    $Emisor->appendChild($xml->createElement('DirOrigen', Mage::getStoreConfig('gomage_xml/company/direccion')));
	    $Emisor->appendChild($xml->createElement('CmnaOrigen', Mage::getStoreConfig('gomage_xml/company/comuna')));
	    $Emisor->appendChild($xml->createElement('CiudadOrigen', Mage::getStoreConfig('gomage_xml/company/ciudad')));

	    //Receptor
	    $Receptor = $xml->createElement('Receptor');
	    $Receptor = $Encabezado->appendChild($Receptor);
	    	    
	    $Receptor->appendChild($xml->createElement('RUTRecep', $order->getData('field_1')));
	    $Receptor->appendChild($xml->createElement('RznSocRecep', $order->getData('field_2')));
            $Receptor->appendChild($xml->createElement('GiroRecep', str_replace("&#8722;","",substr($order->getData('field_3'), 0, 40))));
	    $Receptor->appendChild($xml->createElement('DirRecep', substr($order->getData('field_4'), 0, 70)));	    
	    $Receptor->appendChild($xml->createElement('CmnaRecep', substr($order->getData('field_5'), 0, 20)));
	    $Receptor->appendChild($xml->createElement('CiudadRecep', substr($order->getData('field_6'), 0, 20)));

	    //Totales
	    $Totales = $xml->createElement('Totales');
	    $Totales = $Encabezado->appendChild($Totales);
	    
	    //$discount = round(abs($order->getDiscountAmount())/1.19);
	    $surcharge = round(abs($order->getFoomanSurchargeAmount())/1.19);
	    $subtotal = 0;
	    foreach ($order->getAllItems() as $item){
	    	$subtotal += round($item->getQtyOrdered())*round($item->getPrice());
	    }
	    
	    $shipping = round($order->getShippingAmount());
	    
	    $Totales->appendChild($xml->createElement('MntNeto', $subtotal + $shipping + $surcharge ));
	    $Totales->appendChild($xml->createElement('TasaIVA', '19.00'));
	    $Totales->appendChild($xml->createElement('IVA', round(0.19*($subtotal + $shipping +  $surcharge))));
	    $Totales->appendChild($xml->createElement('MntTotal', $subtotal + $shipping + $surcharge + round(0.19*($subtotal + $shipping + $surcharge))));
	    
	    //Detalle

  	    $i = 1;
  	    foreach ($order->getAllItems() as $item){

            if (!round($item->getPrice())){
              continue;
            }

  	    	$Detalle = $xml->createElement('Detalle');
  	    	$Detalle = $Documento->appendChild($Detalle);

  	    	$Detalle->appendChild($xml->createElement('NroLinDet', $i));

            $CdgItem = $xml->createElement('CdgItem');
            $Detalle->appendChild($CdgItem);
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
			 $codigo_contable = $product->getData('codigocontable');
                         if($codigo_contable == "2267") {$codigo_contable = "5501-07";} else {$codigo_contable = "5501-01";}
  	    $CdgItem->appendChild($xml->createElement('TpoCodigo', $codigo_contable));
		if($product->getData('partnumber')) {
            		$CdgItem->appendChild($xml->createElement('VlrCodigo', $product->getData('partnumber')));
		 }
  	    	$Detalle->appendChild($xml->createElement('NmbItem', str_replace("&","",substr($item->getName(), 0, 75))));
  	    	$Detalle->appendChild($xml->createElement('QtyItem', round($item->getQtyOrdered())));
  	    	$Detalle->appendChild($xml->createElement('PrcItem', round($item->getPrice())));
  	    	$Detalle->appendChild($xml->createElement('MontoItem', round($item->getQtyOrdered())*round($item->getPrice())));

  	    	$i++;
  	    }

        if (round($order->getShippingAmount())){
	    	$Detalle = $xml->createElement('Detalle');
	    	$Detalle = $Documento->appendChild($Detalle);

	    	$Detalle->appendChild($xml->createElement('NroLinDet', $i));

		$CdgItem = $xml->createElement('CdgItem');
            		$Detalle->appendChild($CdgItem);
                 	$CdgItem->appendChild($xml->createElement('TpoCodigo', "5101-02"));
			$CdgItem->appendChild($xml->createElement('VlrCodigo', "Despacho"));
	    	$Detalle->appendChild($xml->createElement('NmbItem', str_replace("&","",substr($order->getShippingDescription(), 0, 75))));
	    	$Detalle->appendChild($xml->createElement('QtyItem', 1));
	    	$Detalle->appendChild($xml->createElement('PrcItem', round($order->getShippingAmount())));
	    	$Detalle->appendChild($xml->createElement('MontoItem', round($order->getShippingAmount())));
	    }
	if (round($order->getShippingAmount())){	
	    if ($surcharge){		    
		    $Detalle = $xml->createElement('Detalle');
		    $Detalle = $Documento->appendChild($Detalle);
		    $Detalle->appendChild($xml->createElement('NroLinDet', $i+1));
				$CdgItem = $xml->createElement('CdgItem');
                        	$Detalle->appendChild($CdgItem);
                        	$CdgItem->appendChild($xml->createElement('TpoCodigo', "5101-09"));
				$CdgItem->appendChild($xml->createElement('VlrCodigo', "CarFin"));
		    $Detalle->appendChild($xml->createElement('NmbItem', 'Cargo Financiero'));
		    $Detalle->appendChild($xml->createElement('QtyItem', 1));
		    $Detalle->appendChild($xml->createElement('PrcItem', round($order->getFoomanSurchargeAmount()/1.19)));
		    $Detalle->appendChild($xml->createElement('MontoItem', round($order->getFoomanSurchargeAmount()/1.19)));
	    }
	}

        else {	
	if ($surcharge){
                    $Detalle = $xml->createElement('Detalle');
                    $Detalle = $Documento->appendChild($Detalle);
                    $Detalle->appendChild($xml->createElement('NroLinDet', $i));
				$CdgItem = $xml->createElement('CdgItem');
                                $Detalle->appendChild($CdgItem);
                                $CdgItem->appendChild($xml->createElement('TpoCodigo', "5101-09"));
				$CdgItem->appendChild($xml->createElement('VlrCodigo', "CarFin"));
                    $Detalle->appendChild($xml->createElement('NmbItem', 'Cargo Financiero'));
                    $Detalle->appendChild($xml->createElement('QtyItem', 1));
                    $Detalle->appendChild($xml->createElement('PrcItem', round($order->getFoomanSurchargeAmount()/1.19)));
                    $Detalle->appendChild($xml->createElement('MontoItem', round($order->getFoomanSurchargeAmount()/1.19)));
            }
	}
	    //DscRcgGlobal
//	    if ($discount){		    
//		    $DscRcgGlobal = $xml->createElement('DscRcgGlobal');
//		    $DscRcgGlobal = $Documento->appendChild($DscRcgGlobal);
//		    
//		    $DscRcgGlobal->appendChild($xml->createElement('NroLinDR', 1));
//		    $DscRcgGlobal->appendChild($xml->createElement('TpoMov', 'D'));
//		    $DscRcgGlobal->appendChild($xml->createElement('GlosaDR', 'Descuento'));
//		    $DscRcgGlobal->appendChild($xml->createElement('TpoValor', '$'));
//		    $DscRcgGlobal->appendChild($xml->createElement('ValorDR', $discount));
//	    
//	    }
        if($order->getData('field_7')) {
            $Referencia = $xml->createElement('Referencia');
            $Referencia = $Documento->appendChild($Referencia);
            $Referencia->appendChild($xml->createElement('NroLinRef', 1));
            $Referencia->appendChild($xml->createElement('TpoDocRef', 801));
            $Referencia->appendChild($xml->createElement('FolioRef', substr($order->getData('field_7'), 0, 20)));
            $Referencia->appendChild($xml->createElement('FchRef', $date_formated));
//        $Referencia->appendChild($xml->createElement('RazonRef', ''));
        }


        $Adjuntos = $xml->createElement('Adjuntos');
	    $Adjuntos = $Documento->appendChild($Adjuntos);

        $billing_address = $order->getBillingAddress();
        $email = $billing_address->getEmail();
	    if (!$email){
	    	$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
	    	if ($customer){
	    		$email = $customer->getEmail();
	    	}
	    }

// $Adjuntos->appendChild($xml->createElement('DatoAdj1', $order->getIncrementId()));Seleccione su despacho: - 
 //       $Adjuntos->appendChild($xml->createElement('DatoAdj2', $order->getPayment()->getMethodInstance()->getTitle()));
  //      $Adjuntos->appendChild($xml->createElement('DatoAdj3', $email));
//str_replace("(","-",str_replace(")","-",substr($order->getShippingDescription(), 26, 40 )))));
        $arreglo1=array('Seleccione su despacho: - ','(Entrega en 1 día hábil)','Día Hábil Sgte','');
	$arreglo2=array(' ',' ','DHS',' ');

	$Adjuntos->appendChild($xml->createElement('DatoAdj1', $order->getIncrementId()));
        $Adjuntos->appendChild($xml->createElement('DatoAdj2', str_replace($arreglo1,$arreglo2,$order->getShippingDescription())));
	$Adjuntos->appendChild($xml->createElement('DatoAdj3', str_replace("&","-",$order->getPayment()->getMethodInstance()->getTitle())));
        if($order->getData('field_8')) {
            $Adjuntos->appendChild($xml->createElement('Observacion', substr($order->getData('field_8'), 0, 100)));
        }
		$xml_data = $xml->saveXML();
		
	    return $xml_data;
	}
	
	public function getType39Content($order){
				
		$xml = new DOMDocument('1.0', 'ISO-8859-1');
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;

		$BOLETA = $xml->createElement('BOLETA');
	    $BOLETA = $xml->appendChild($BOLETA);
	    
	    $Encabezado = $xml->createElement('Encabezado');
	    $Encabezado = $BOLETA->appendChild($Encabezado);
	    
	    //IdDoc
	    $IdDoc = $xml->createElement('IdDoc');
	    $IdDoc = $Encabezado->appendChild($IdDoc);
	    
	    $Folio = intval(Mage::getStoreConfig('gomage_xml/settings/next_starting_39'));
	    Mage::getModel('core/config')->saveConfig('gomage_xml/settings/next_starting_39', $Folio + 1);
	    Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
	    
	    $IdDoc->appendChild($xml->createElement('TipoDTE', 39));
	    $IdDoc->appendChild($xml->createElement('Folio', $Folio));
	    $date_formated = date('Y-m-d', strtotime($order->getCreatedAtDate()));
	    $IdDoc->appendChild($xml->createElement('FchEmis', $date_formated));
	    $IdDoc->appendChild($xml->createElement('IndServicio', 3));
	    
	    //Emisor
	    $Emisor = $xml->createElement('Emisor');
	    $Emisor = $Encabezado->appendChild($Emisor);
	    	    	    
	    $Emisor->appendChild($xml->createElement('RUTEmisor', Mage::getStoreConfig('gomage_xml/company/rut_field')));
	    $Emisor->appendChild($xml->createElement('RznSocEmisor', Mage::getStoreConfig('gomage_xml/company/company_name')));
	    $Emisor->appendChild($xml->createElement('GiroEmisor', Mage::getStoreConfig('gomage_xml/company/giro')));	    
	    $Emisor->appendChild($xml->createElement('DirOrigen', Mage::getStoreConfig('gomage_xml/company/direccion')));
	    $Emisor->appendChild($xml->createElement('CmnaOrigen', Mage::getStoreConfig('gomage_xml/company/comuna')));
	    $Emisor->appendChild($xml->createElement('CiudadOrigen', Mage::getStoreConfig('gomage_xml/company/ciudad')));
	    

	    //Receptor
	    $Receptor = $xml->createElement('Receptor');
	    $Receptor = $Encabezado->appendChild($Receptor);
	    
	    $billing_address = $order->getBillingAddress();
	    $customer_vat = $billing_address->getVatId();
	    if (!$customer_vat){
	    	$customer_vat = $order->getData('customer_taxvat');
	    }
	    if (!$customer_vat){
	    	$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
	    	if ($customer){
	    		$customer_vat = $customer->getTaxvat();
	    	}
	    }

	    $customer_vat = preg_replace("|[^0-9kK]|i", "", $customer_vat);
	    if ($customer_vat){
		    $length = strlen($customer_vat);
	        $customer_vat = substr($customer_vat, 0, $length - 1) . '-' . substr($customer_vat, $length - 1, $length);
	    }
	    
	    	    
	    $Receptor->appendChild($xml->createElement('RUTRecep', $customer_vat));
	    $Receptor->appendChild($xml->createElement('RznSocRecep', $billing_address->getFirstname() . ' ' . $billing_address->getLastname()));	   
	    $Receptor->appendChild($xml->createElement('DirRecep', substr($billing_address->getStreetFull(), 0, 70)));
	    $Receptor->appendChild($xml->createElement('CmnaRecep', $billing_address->getRegion()));
	    $Receptor->appendChild($xml->createElement('CiudadRecep', substr($billing_address->getCity(), 0, 20)));
	    //Totales
	    $Totales = $xml->createElement('Totales');
	    $Totales = $Encabezado->appendChild($Totales);
	    	    	    
	    $subtotal = 0;
	    foreach ($order->getAllItems() as $item){
	    	$subtotal += round($item->getQtyOrdered())*round($item->getPriceInclTax());	
	    }
	    
	    //$discount = abs($order->getDiscountAmount());
	    $surcharge = abs($order->getFoomanSurchargeAmount());

	    $shipping = round($order->getShippingAmount() + $order->getShippingTaxAmount());
	    
	    $Totales->appendChild($xml->createElement('MntTotal', round($subtotal + $shipping )));

	    //Detalle

  	    $i = 1;
  	    foreach ($order->getAllItems() as $item){

            if (!round($item->getPriceInclTax())){
              continue;
            }

  	    	$Detalle = $xml->createElement('Detalle');
  	    	$Detalle = $BOLETA->appendChild($Detalle);

  	    	$Detalle->appendChild($xml->createElement('NroLinDet', $i));

            $CdgItem = $xml->createElement('CdgItem');
            $Detalle->appendChild($CdgItem);
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
			$codigo_contable = $product->getData('codigocontable');
                        if($codigo_contable == "2267") {$codigo_contable = "5501-07";} else {$codigo_contable = "5501-01";}
            		$CdgItem->appendChild($xml->createElement('TpoCodigo', $codigo_contable));	
            		if($product->getData('partnumber')) {
				$CdgItem->appendChild($xml->createElement('VlrCodigo', $product->getData('partnumber')));
				}
  	    	$Detalle->appendChild($xml->createElement('NmbItem', str_replace("&","",substr($item->getName(), 0, 75))));
  	    	$Detalle->appendChild($xml->createElement('QtyItem', round($item->getQtyOrdered())));
  	    	$Detalle->appendChild($xml->createElement('UnmdItem', 'UND'));
  	    	$Detalle->appendChild($xml->createElement('PrcItem', round($item->getPriceInclTax())));
  	    	$Detalle->appendChild($xml->createElement('MontoItem', round($item->getQtyOrdered())*round($item->getPriceInclTax()) ));

  	    	$i++;
  	    }

        if (round($order->getShippingAmount() + $order->getShippingTaxAmount())){
	    	$Detalle = $xml->createElement('Detalle');
	    	$Detalle = $BOLETA->appendChild($Detalle);

	    	$Detalle->appendChild($xml->createElement('NroLinDet', $i));
			$CdgItem = $xml->createElement('CdgItem');
                        $Detalle->appendChild($CdgItem);
                        $CdgItem->appendChild($xml->createElement('TpoCodigo', "5101-02"));
			$CdgItem->appendChild($xml->createElement('VlrCodigo', "Despacho"));
	    	$Detalle->appendChild($xml->createElement('NmbItem', str_replace("&","",substr($order->getShippingDescription(), 0, 75))));
	    	$Detalle->appendChild($xml->createElement('QtyItem', 1));
	    	$Detalle->appendChild($xml->createElement('UnmdItem', 'UND'));
	    	$Detalle->appendChild($xml->createElement('PrcItem', round($order->getShippingAmount() + $order->getShippingTaxAmount())));
	    	$Detalle->appendChild($xml->createElement('MontoItem', round($order->getShippingAmount() + $order->getShippingTaxAmount())));
	    }
	    
		    
	    //DscRcgGlobal
//	    if ($discount){		    
//		    $DscRcgGlobal = $xml->createElement('DscRcgGlobal');
//		    $DscRcgGlobal = $BOLETA->appendChild($DscRcgGlobal);
//		    
//		    $DscRcgGlobal->appendChild($xml->createElement('NroLinDR', 1));
//		    $DscRcgGlobal->appendChild($xml->createElement('TpoMov', 'D'));
//		    $DscRcgGlobal->appendChild($xml->createElement('GlosaDR', 'Descuento'));
//		    $DscRcgGlobal->appendChild($xml->createElement('TpoValor', '$'));
//		    $DscRcgGlobal->appendChild($xml->createElement('ValorDR', $discount));
//
//	    }




        $Adjuntos = $xml->createElement('Adjuntos');
	    $Adjuntos = $BOLETA->appendChild($Adjuntos);

        $email = $billing_address->getEmail();
	    if (!$email){
	    	$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
	    	if ($customer){
	    		$email = $customer->getEmail();
	    	}
	    }

//	$Adjuntos->appendChild($xml->createElement('DatoAdj1', $order->getIncrementId()));
//      $Adjuntos->appendChild($xml->createElement('DatoAdj2', $order->getPayment()->getMethodInstance()->getTitle()));
//      $Adjuntos->appendChild($xml->createElement('DatoAdj3', $email));
	$arreglo1=array('Seleccione su despacho: - ','(Entrega en 1 día hábil)','Día Hábil Sgte','');
        $arreglo2=array(' ',' ','DHS',' ');
        
	$Adjuntos->appendChild($xml->createElement('DatoAdj1', $order->getIncrementId()));
        $Adjuntos->appendChild($xml->createElement('DatoAdj2', str_replace($arreglo1,$arreglo2,$order->getShippingDescription())));
	$Adjuntos->appendChild($xml->createElement('DatoAdj3', str_replace("&","-",$order->getPayment()->getMethodInstance()->getTitle())));

	$xml_data = $xml->saveXML();
		
	    return $xml_data;
	}

}

