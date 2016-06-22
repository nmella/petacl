<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_ShippingInformation
    extends Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * @return bool
     */
    public function getFilter()
    {
        return false;
    }


    /**
     * @param Varien_Object $row
     *
     * @return mixed|string
     */
    public function _getValue(Varien_Object $row)
    {
        Varien_Profiler::start(__METHOD__);
        if ($row->getShippingDescription()) {
            $order = Mage::helper('fooman_ordermanager/order')->getOrder($row);

            $infoBlock = Mage::app()->getLayout()->createBlock('adminhtml/sales_order_view_info');
            $infoBlock->setOrder($order);

            $returnHtml = '';

            $trackingNrs = Mage::getResourceHelper('fooman_ordermanager')->getFoomanTrackingNumbers($this, $row);
            if (!empty($trackingNrs)) {
                $returnHtml = '<a href="#" id="linkId" onclick="popWin(\'' .
                    $this->helper('shipping')->getTrackingPopupUrlBySalesModel($order)
                    . '\',\'trackorder\',\'width=800,height=600,resizable=yes,scrollbars=yes\')" title="' . $this->__(
                        'Track Order'
                    ) . '">' . $this->__(
                        'Track Order'
                    ) . '</a>'
                    . '<br/>';
            }
            if ($row->getShippingDescription()) {
				if (strpos($row->getShippingDescription(),'Ultra Rápido') !== false) {
					$class = "class=' shipping_ultra'";
				}
				if (strpos($row->getShippingDescription(),'Peta LG.') !== false || strpos($row->getShippingDescription(),'Peta (') !== false) {
                                        $class = "class=' shipping_AMPM'";
                                }
            $returnHtml .= '<strong' . $class . '>' . $this->escapeHtml($row->getShippingDescription()) . '</strong><br/>';
            }
            if ($this->helper('tax')->displayShippingPriceIncludingTax()) {
                $_excl = $infoBlock->displayShippingPriceInclTax($order);
            } else {
                $_excl = $infoBlock->displayPriceAttribute('shipping_amount', false, ' ');
            }
            $_incl = $infoBlock->displayShippingPriceInclTax($order);
            $returnHtml .= $_excl;
            if ($this->helper('tax')->displayShippingBothPrices() && $_incl != $_excl) {
                $returnHtml .= '(' . $this->__('Incl. Tax') . $_incl . ')';
            }
        } else {
            $returnHtml = Mage::helper('sales')->__('No shipping information available');
        }
        Varien_Profiler::stop(__METHOD__);
        return $returnHtml;
    }
}
