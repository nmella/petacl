<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Pquestion2
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Pquestion2_Model_Source_Answer_CustomerGroup
{
    const ADMIN_VALUE    = 1;
    const CUSTOMER_VALUE = 2;
    const GUEST_VALUE    = 3;

    const ADMIN_LABEL    = 'Admin';
    const CUSTOMER_LABEL = 'Customer';
    const GUEST_LABEL    = 'Guest';

    public function toOptionArray()
    {
        return array(
            self::ADMIN_VALUE    => Mage::helper('aw_pq2')->__(self::ADMIN_LABEL),
            self::CUSTOMER_VALUE => Mage::helper('aw_pq2')->__(self::CUSTOMER_LABEL),
            self::GUEST_VALUE    => Mage::helper('aw_pq2')->__(self::GUEST_LABEL)
        );
    }

    public function toOptionMultiArray()
    {
        return array(
            0 => array ('value' => self::ADMIN_VALUE, 'label'    => Mage::helper('aw_pq2')->__(self::ADMIN_LABEL)),
            1 => array ('value' => self::CUSTOMER_VALUE, 'label' => Mage::helper('aw_pq2')->__(self::CUSTOMER_LABEL)),
            2 => array ('value' => self::GUEST_VALUE, 'label'    => Mage::helper('aw_pq2')->__(self::GUEST_LABEL)),
        );
    }

    public function getOptionByValue($value)
    {
        $options = $this->toOptionArray();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}