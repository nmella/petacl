<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */

require_once(Mage::getModuleDir('controllers','Mage_Adminhtml').DS."Catalog".DS."Product".DS.'AttributeController.php');

class Amasty_Stockstatus_Adminhtml_Catalog_Product_AttributeController extends Mage_Adminhtml_Catalog_Product_AttributeController
{
    protected function _filterPostData($data)
    {
        /*
         * disable stripTags for custom_stock_status
         * */
        if( $data['attribute_code'] == "custom_stock_status" ){
           return $data;
        }
        else{
           return parent::_filterPostData($data);
        }
    }
}