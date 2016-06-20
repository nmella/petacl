<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_TrackingNumberInput
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        Varien_Profiler::start(__METHOD__);
        if ($this->_canShip($row)) {
            $colId = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
            $html = '<input name="' . $colId . '-' . $row->getId() . '" rel="' . $row->getId() . '" class="input-text '
                . $colId . '"
                            value="' . $row->getData($this->getColumn()->getIndex()) . '" />';
        } else {
            $trackingNumbers = array();
            if (Mage::getResourceHelper('fooman_ordermanager')->getFoomanTrackingNumbers($this, $row)) {
                $maxLength = Mage::helper('fooman_ordermanager')->getStoreConfig(
                    'settings/trackingnumbercharacterlengthtodisplay'
                );
                foreach (Mage::getResourceHelper('fooman_ordermanager')->getFoomanTrackingNumbers($this, $row) as $number) {
                    $trackingNumbers[] = $this->getTrackingNrForDisplay($number, $maxLength);
                }
            }
            $html = '<small>' . implode('<br/>', $trackingNumbers) . '</small>';
        }
        Varien_Profiler::stop(__METHOD__);
        return $html;
    }

    /**
     * @param Varien_Object $row
     *
     * @return bool
     */
    private function _canShip(Varien_Object $row)
    {
        $canShip = Mage::helper('fooman_ordermanager/order')->canShip($row);
        $row->setCanShip($canShip);
        return $canShip;
    }

    /**
     * @return bool
     */
    public function getFilter()
    {
        return false;
    }

    /**
     * @param $number
     * @param $maxLength
     *
     * @return string
     */
    protected function getTrackingNrForDisplay($number, $maxLength)
    {
        return $this->escapeHtml(
            strlen($number) > $maxLength ? substr(
                    $number,
                    0, ($maxLength / 2) - 2
                ) . '...' . substr(
                    $number,
                    -($maxLength / 2)
                ) : $number
        );
    }

}
