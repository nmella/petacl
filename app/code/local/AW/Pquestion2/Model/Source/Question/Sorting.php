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


class AW_Pquestion2_Model_Source_Question_Sorting
{
    const DATE_VALUE        = 1;
    const HELPFULNESS_VALUE = 2;

    const DATE_LABEL        = 'Date';
    const HELPFULNESS_LABEL = 'Helpfulness';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::DATE_VALUE  => Mage::helper('aw_pq2')->__(self::DATE_LABEL),
            self::HELPFULNESS_VALUE => Mage::helper('aw_pq2')->__(self::HELPFULNESS_LABEL)
        );
    }
}
