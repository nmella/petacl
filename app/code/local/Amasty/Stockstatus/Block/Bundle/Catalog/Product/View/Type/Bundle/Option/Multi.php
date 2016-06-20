<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Block_Bundle_Catalog_Product_View_Type_Bundle_Option_Multi extends Amasty_Stockstatus_Block_Bundle_Catalog_Product_View_Type_Bundle_Option
{
    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('bundle/catalog/product/view/type/bundle/option/multi.phtml');
    }
}
