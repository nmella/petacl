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


class AW_Pquestion2_Model_Source_Question_Sharing_Type
{
    const PRODUCTS_VALUE      = 1;
    const ATTRIBUTE_SET_VALUE = 2;
    const WEBSITE_VALUE       = 3;
    const GLOBAL_VALUE        = 4;

    const PRODUCTS_LABEL      = 'Product(s)';
    const ATTRIBUTE_SET_LABEL = 'Attribute Set';
    const WEBSITE_LABEL       = 'Website';
    const GLOBAL_LABEL        = 'GLOBAL';

    public function toOptionArray()
    {
        return array(
            self::PRODUCTS_VALUE      => Mage::helper('aw_pq2')->__(self::PRODUCTS_LABEL),
            self::ATTRIBUTE_SET_VALUE => Mage::helper('aw_pq2')->__(self::ATTRIBUTE_SET_LABEL),
            self::WEBSITE_VALUE       => Mage::helper('aw_pq2')->__(self::WEBSITE_LABEL),
            self::GLOBAL_VALUE        => Mage::helper('aw_pq2')->__(self::GLOBAL_LABEL)
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