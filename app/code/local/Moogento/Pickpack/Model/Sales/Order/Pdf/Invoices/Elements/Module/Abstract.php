<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

abstract class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    protected $addressXY;

    public function __construct($arguments) {
        parent::__construct($arguments);

        $pageConfig = $this->getPageConfig();
        $this->addressXY = array($pageConfig['addressX'], $pageConfig['addressY']);
    }
}