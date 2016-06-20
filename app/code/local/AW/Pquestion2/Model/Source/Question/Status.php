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


class AW_Pquestion2_Model_Source_Question_Status
{
    const PENDING_VALUE  = 1;
    const APPROVED_VALUE = 2;
    const DECLINE_VALUE  = 3;

    const PENDING_LABEL  = 'Pending';
    const APPROVED_LABEL = 'Approved';
    const DECLINE_LABEL  = 'Declined';

    public function toOptionArray()
    {
        return array(
            self::PENDING_VALUE  => Mage::helper('aw_pq2')->__(self::PENDING_LABEL),
            self::APPROVED_VALUE => Mage::helper('aw_pq2')->__(self::APPROVED_LABEL),
            self::DECLINE_VALUE  => Mage::helper('aw_pq2')->__(self::DECLINE_LABEL)
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