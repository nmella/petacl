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


class AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions
{
    const DENIED_VALUE                               = 1;
    const REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_VALUE  = 2;
    const REGISTERED_CUSTOMERS_VALUE                 = 3;
    const ALL_CUSTOMERS_VALUE                        = 4;

    const DENIED_LABEL                              = 'Nobody (admin only)';
    const REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_LABEL = 'Only Registered Customers Who Purchased The Products';
    const REGISTERED_CUSTOMERS_LABEL                = 'Any Registered Customer';
    const ALL_CUSTOMERS_LABEL                       = 'Anyone';

    public function toOptionArray()
    {
        return array(
            self::DENIED_VALUE               => Mage::helper('aw_pq2')->__(self::DENIED_LABEL),
            self::REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_VALUE => Mage::helper('aw_pq2')->__(
                self::REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_LABEL
            ),
            self::REGISTERED_CUSTOMERS_VALUE => Mage::helper('aw_pq2')->__(self::REGISTERED_CUSTOMERS_LABEL),
            self::ALL_CUSTOMERS_VALUE        => Mage::helper('aw_pq2')->__(self::ALL_CUSTOMERS_LABEL)
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