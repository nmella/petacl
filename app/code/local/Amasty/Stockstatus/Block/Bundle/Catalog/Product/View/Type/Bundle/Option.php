<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Block_Bundle_Catalog_Product_View_Type_Bundle_Option extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
{
    public function getSelectionQtyTitlePrice($_selection, $includeContainer = true)
    {
        $html = parent::getSelectionQtyTitlePrice($_selection, $includeContainer);

        $stockStatus = Mage::helper('amstockstatus')->getCustomStockStatusText(Mage::getModel('catalog/product')->load($_selection->getId()));
        if ($stockStatus)
        {
            $stockStatus = '&nbsp;(' . $stockStatus . ') &nbsp; ';
        }
        if($includeContainer){
            $search = '<span class="price-notice">';
            $replace = $stockStatus . $search;
            $html = str_replace($search, $replace, $html);
        }
        else{
            $html .= $stockStatus;
        }

        return $html;
    }

    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $html = parent::getSelectionTitlePrice($_selection, $includeContainer);

        $stockStatus = Mage::helper('amstockstatus')->getCustomStockStatusText(Mage::getModel('catalog/product')->load($_selection->getId()));
        if ($stockStatus)
        {
            $stockStatus = '&nbsp;(' . $stockStatus . ') &nbsp; ';
        }
        if($includeContainer){
            $search = '<span class="price-notice">';
            $replace = $stockStatus . $search;
            $html = str_replace($search, $replace, $html);
        }
        else{
            $html .= $stockStatus;
        }

        return $html;
    }
}
