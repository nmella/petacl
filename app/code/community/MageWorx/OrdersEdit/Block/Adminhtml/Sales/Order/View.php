<?php
/**
 * MageWorx
 * Admin Order Editor extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersEdit
 * @copyright  Copyright (c) 2016 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_OrdersEdit_Block_Adminhtml_Sales_Order_View extends MageWorx_OrdersEdit_Block_Adminhtml_Sales_Order_View_Abstract
{
    /** @var MageWorx_OrdersEdit_Helper_Data|null  */
    protected $_helper = null;

    public function __construct()
    {
        $this->_helper = Mage::helper('mageworx_ordersedit');
        parent::__construct();
    }

    /**
     * @param $action
     * @return mixed
     */
    protected function _isAllowedAction($action)
    {
        if ($action == 'emails' && $this->_helper->isEnabled() && $this->_helper->isEnableDeleteOrdersCompletely() && Mage::getSingleton('admin/session')->isAllowed('sales/mageworx_ordersedit/actions/delete_completely')) {
            $message = $this->_helper->__('Are you sure you want to completely delete this order?');
            $this->_addButton('order_delete', array(
                    'label' => $this->_helper->__('Delete'),
                    'onclick' => 'deleteConfirm(\'' . $message . '\', \'' . $this->getUrl('adminhtml/mageworx_ordersedit/massDeleteCompletely') . '\')',
                    'class' => 'delete'
                )
            );
        }
        return parent::_isAllowedAction($action);
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        $text = parent::getHeaderText();
        if ($this->_helper->isEnabled() && $this->getOrder()->getIsEdited()) {
            $text .= ' (' . $this->_helper->__('Edited') . ')';
        }
        return $text;
    }

    public function _beforeToHtml()
    {
        parent::_beforeToHtml();
        if ($this->_helper->isHideEditButton() || !$this->_helper->isOrderEditable($this->getOrder())) {
            $this->_removeButton('order_edit');
            
            
            $order = $this->getOrder();
            if ($this->_isAllowedAction('ship') && $order->canShip()
            && !$order->getForcedDoShipmentWithInvoice()) {
            $this->_addButton('order_ship', array(
                'label'     => Mage::helper('sales')->__('Ship'),
                'onclick'   => 'setLocation(\'' . $this->getShipUrl() . '\')',
                'class'     => 'go'
            ));
        }
        
        }
    }
}
