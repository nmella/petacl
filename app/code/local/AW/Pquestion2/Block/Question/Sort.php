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


class AW_Pquestion2_Block_Question_Sort extends Mage_Core_Block_Template
{
    protected $_sourceSorting;
    protected $_sourceDir;

    protected $_orderParam  = 'orderby';
    protected $_dirParam    = 'dir';

    protected function _construct()
    {
        parent::_construct();
        $this->_sourceSorting = Mage::getModel('aw_pq2/source_question_sorting');
        $this->_sourceDir = Mage::getModel('aw_pq2/source_question_sorting_dir');
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_sourceSorting->toOptionArray();
    }

    /**
     * @return string
     */
    public function getCurrentOrder()
    {
        return $this->getRequest()->getParam(
            $this->_orderParam,
            Mage::helper('aw_pq2/config')->getDefaultQuestionsSortBy()
        );
    }

    /**
     * @return string
     */
    public function getCurrentDir()
    {
        return $this->getRequest()->getParam(
            $this->_dirParam,
            Mage::helper('aw_pq2/config')->getDefaultSortOrder()
        );
    }

    /**
     * @return string
     */
    public function getTargetDir()
    {
        return Zend_Json::encode(
            $this->_sourceDir->getInvertedValue($this->getCurrentDir())
        );
    }

    /**
     * @return string
     */
    public function getSortUrl()
    {
        return Zend_Json::encode(
            $this->getUrl(
                'aw_pq2/question/list',
                array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure())
            )
        );
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public function getImageUrl($dir)
    {
        return $this->getSkinUrl('aw_pq2/image/sort_' . strtolower($dir) . '_arrow.gif');
    }

    /**
     * @return string
     */
    public function getImages()
    {
        $asc = AW_Pquestion2_Model_Source_Question_Sorting_Dir::ASC_VALUE;
        $desc = AW_Pquestion2_Model_Source_Question_Sorting_Dir::DESC_VALUE;
        return Zend_Json::encode(
            array(
                $asc => $this->getImageUrl($asc),
                $desc => $this->getImageUrl($desc)
            )
        );
    }

    /**
     * @return string
     */
    public function getOverlayImage()
    {
        return Zend_Json::encode(
            $this->getSkinUrl('aw_pq2/image/ajax-loader.gif')
        );
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (Mage::registry('current_product')) {
            $product = Mage::registry('current_product');
        } else {
            $product = Mage::getModel('catalog/product')->load(Mage::helper('aw_pq2/request')->getRewriteProductId());
        }
        return Zend_Json::encode(
            $product instanceof Mage_Catalog_Model_Product ? $product->getId() : $product
        );
    }
}