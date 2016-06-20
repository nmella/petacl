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
* File        ItemsPick.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Sales_Order_Processor_ItemsPick3
    extends Moogento_Pickpack_Model_Sales_Order_Processor_Abstract3
{
    protected $_configGroupPrefix = 'messages';
    protected $_hiddenOrderFlag = 'pp_items_pick_list_printed';
    protected $_flagColumn = 'combined';
	

    public function getPdf($orderIds, $storeId = 0) {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        $pdf = Mage::getModel('pickpack/sales_order_pdf_invoices_combined')->getPickCombined($orderIds);

        return $pdf;
    }

    protected function _getFileName($orderIds) {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        $now = Mage::getModel('core/date')->timestamp(time());
		$time =  date('m_d_y_his', $now); 
        $fileName = 'Pick_list_separated_' . implode('_', $orderIds).$time. '.pdf';
        return $fileName;
    }

}
