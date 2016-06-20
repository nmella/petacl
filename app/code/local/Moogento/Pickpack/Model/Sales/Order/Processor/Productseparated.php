<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Invoice.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Sales_Order_Processor_Productseparated
    extends Moogento_Pickpack_Model_Sales_Order_Processor_Abstract
{
    protected $_configGroupPrefix = 'product_separated';    

    public function getPdf($orderIds, $storeId = 0,$product_filter = array()) {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_productseparated')->getPdf($orderIds,'order',$product_filter);       
        return $pdf;
    }

    protected function _getFileName($orderIds) {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        $now = Mage::getModel('core/date')->timestamp(time());      
        
        $time =  date('m-d-y_his', $now); 

		$fileName = 'Order_' . implode('_', $orderIds).'_'.$time.'_Product_Separated.pdf';


        return $fileName;
    }
}
