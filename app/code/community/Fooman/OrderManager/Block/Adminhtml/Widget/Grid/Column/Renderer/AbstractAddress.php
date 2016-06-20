<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_AbstractAddress
    extends Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{

    protected $_address = null;
    protected $_addressDescription = null;
    protected $fieldPrefix = '';
    protected $addressRenderer = null;

    /**
     * @return string
     */
    protected function getAddressDescription()
    {
        return Mage::helper('sales')->__($this->_addressDescription);
    }

    /**
     * @param Varien_Object $row
     *
     * @return string
     */
    public function render(Varien_Object $row)
    {
        Varien_Profiler::start(__METHOD__);
        $colId = $this->getColumn()->getName() ? $this->getColumn()->getName() : $this->getColumn()->getId();
        $html = '<textarea style="overflow:auto;" rows="5" cols="25" onclick="this.select();" name="' . $colId . '-'
            . $row->getId() . '" rel="' . $row->getId() . '" class="input-text '
            . $colId . '">' . $this->_getValue($row) . '</textarea>';
        Varien_Profiler::stop(__METHOD__);
        return $html;
    }


    /**
     * @param Varien_Object $row
     *
     * @return mixed|string
     */
    public function _getValue(Varien_Object $row)
    {
        $address = $this->_getPopulatedAddress($row);
        if (!$address) {
            //For virtual product orders that have no shipping address
            $address = Mage::helper('fooman_ordermanager')->__('No %s available', $this->getAddressDescription());
        }
        return $address;
    }

    /**
     * return a faked address object to prevent separate db calls
     *
     * @param $row
     *
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _getPopulatedAddress($row)
    {
        if (!$row->getDataUsingMethod($this->fieldPrefix . 'country_id')) {
            return false;
        }
        $data = array(
            'firstname'  => $row->getDataUsingMethod($this->fieldPrefix . 'firstname'),
            'lastname'   => $row->getDataUsingMethod($this->fieldPrefix . 'lastname'),
            'region_id'  => $row->getDataUsingMethod($this->fieldPrefix . 'region_id'),
            'region'     => $row->getDataUsingMethod($this->fieldPrefix . 'region'),
            'postcode'   => $row->getDataUsingMethod($this->fieldPrefix . 'postcode'),
            'street'     => $row->getDataUsingMethod($this->fieldPrefix . 'street'),
            'city'       => $row->getDataUsingMethod($this->fieldPrefix . 'city'),
            'telephone'  => $row->getDataUsingMethod($this->fieldPrefix . 'telephone'),
            'country_id' => $row->getDataUsingMethod($this->fieldPrefix . 'country_id'),
        );

        //use the user defined format on smaller grids as formatting can be expensive
        if ($this->getColumn()->getGrid()->getCollection()->getPageSize() < 50) {
            $address = Mage::helper('fooman_ordermanager/order')->getOrderAddress($data);
            return $this->escapeHtml($this->formatAddress($address));
        } else {
            $address = sprintf(
                '%s %s' . PHP_EOL . '%s' . PHP_EOL . '%s ,%s, %s' . PHP_EOL . '%s',
                $data['firstname'],
                $data['lastname'],
                $data['street'],
                $data['city'],
                $data['region'],
                $data['postcode'],
                Mage::helper('fooman_ordermanager/order')->getCountryName($data['country_id'])
            );
            if (!empty($data['telephone'])) {
                $address .= PHP_EOL . 'T: ' . $data['telephone'];
            }
            return $this->escapeHtml($address);
        }
    }

    /**
     * use Magento text format for address
     *
     * @param $address
     *
     * @return string
     */
    protected function formatAddress($address)
    {
        return trim(
            preg_replace(
                '/(\r|\n|\r\n){2,}/',
                PHP_EOL,
                $address->format('text')
            )
        );
    }
}
