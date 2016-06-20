<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_TrackingCarrierInput
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Select
{
    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        Varien_Profiler::start(__METHOD__);
        $trackingCarriers = $this->getOptions($row);
        if ($row->getCanShip() && $trackingCarriers && is_array($trackingCarriers)) {
            $colId = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
            $html = '<select style="width:60px;" name="' . $colId . '-' . $row->getId() . '" rel="' . $row->getId()
                . '" class="' . $colId . '" >';
            foreach ($trackingCarriers as $val => $label) {
                $selected = (($val == $this->getPreSelectCarrier()) ? ' selected="selected"' : '');
                $html .= '<option ' . $selected . ' value="' . $val . '">' . $label . '</option>';
            }
            $html .= '</select>';
        } else {
            $html = '';
            if (Mage::getResourceHelper('fooman_ordermanager')->getFoomanCarrierTitles($this, $row)) {
                $html = '<small>' . implode(
                        '<br/>', Mage::helper('core')->escapeHtml(
                            Mage::getResourceHelper('fooman_ordermanager')->getFoomanCarrierTitles($this, $row)
                        )
                    ) . '<small>';

            }
        }
        Varien_Profiler::stop(__METHOD__);
        return $html;
    }

    /**
     * @param $row
     *
     * @return mixed
     */
    public function getOptions($row)
    {
        return Mage::helper('fooman_ordermanager')->getCarriersForStore($row->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getPreSelectCarrier()
    {
        return Mage::helper('fooman_ordermanager')->getStoreConfig('settings/preselectedcarrier');
    }

    /**
     * @return bool
     */
    public function getFilter()
    {
        return false;
    }

}
