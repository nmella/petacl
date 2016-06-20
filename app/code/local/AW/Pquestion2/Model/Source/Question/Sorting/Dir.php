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


class AW_Pquestion2_Model_Source_Question_Sorting_Dir
{
    const ASC_VALUE     = 'ASC';
    const DESC_VALUE    = 'DESC';

    const ASC_LABEL     = 'Ascending';
    const DESC_LABEL    = 'Descending';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::ASC_VALUE  => Mage::helper('aw_pq2')->__(self::ASC_LABEL),
            self::DESC_VALUE => Mage::helper('aw_pq2')->__(self::DESC_LABEL)
        );
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getInvertedValue($value)
    {
        if ($value == self::DESC_VALUE) {
            return self::ASC_VALUE;
        } else {
            return self::DESC_VALUE;
        }
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    public function getStorageValue($value)
    {
        $sourceToStorage = array(
            self::ASC_VALUE  => Zend_Db_Select::SQL_ASC,
            self::DESC_VALUE => Zend_Db_Select::SQL_DESC
        );
        return (isset($sourceToStorage[$value]) ? $sourceToStorage[$value] : null);
    }
}