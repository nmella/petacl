<?php
/**
 * 
 * Date: 20.12.15
 * Time: 14:53
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Moogento_Cn22 extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public $itemQtyArray = array();

    public function showCustomsDeclaration() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();

        $case_rotate = $this->_getConfig('case_rotate_address_label',0, false, $wonder, $storeId);
        if($case_rotate > 0)
            $this->rotateLabel($case_rotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);
        try{
            $show_customs_declaration_nudge = explode(',',$this->_getConfig('show_customs_declaration_nudge','340,250', true, $wonder, $storeId));
            $show_customs_declaration_dimension = $this->_getConfig('show_customs_declaration_dimension','100', false, $wonder, $storeId);
            $customs_section_model = new Moogento_Cn22_Model_Pdf();
            $label_width = $this->_getConfig('label_width', '279', false,'cn22_label', $storeId,true,'cn22_options');
            $label_height = $this->_getConfig('label_height', '245', false,'cn22_label', $storeId,true,'cn22_options');
            $customs_section_model->printCustomsSection(0, $page, $order, $wonder,$show_customs_declaration_nudge[0], $show_customs_declaration_nudge[1], $this->itemQtyArray, $label_width, $label_height, $show_customs_declaration_dimension);
        }
        catch(Exception $e)
        {
            echo $e->getMessage(); exit;
        }
        if($case_rotate > 0)
            $this->reRotateLabel($case_rotate,$page,$pageConfig['page_top'], $pageConfig['padded_right']);
    }
}