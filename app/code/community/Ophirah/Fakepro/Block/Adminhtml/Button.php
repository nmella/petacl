<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2016] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Cart2Quote
 * @package     Fakepro
 * @copyright   Copyright (c) 2016 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 * @version     1.0.5
 */

/**
 * Class Ophirah_Fakepro_Block_Adminhtml_Button
 * @since 1.0.5
 */
class Ophirah_Fakepro_Block_Adminhtml_Button extends Mage_Core_Block_Template
{
    /**
     * Get fake product button.
     * @param $label
     * @return Mage_Adminhtml_Block_Widget_Button
     * @since 1.0.5
     */
    public function getButton($label){
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setLabel($this->__($label))
            ->setClass('add')
            ->setOnclick($this->getOnClick());
        return $button;
    }

    /**
     * Returns the on click event for the button.
     * @return string
     * @since 1.0.5
     */
    public function getOnClick(){
        $fakeProduct = $this->_getFakeProduct();
        $onclickFake = "event.preventDefault(); newFakeProRow(this, '".$fakeProduct->getName()."')";
        return $onclickFake;
    }

    /**
     * Get the fake pro create product action
     * @return mixed
     * @throws Exception
     * @since 1.0.5
     */
    public function getAction(){
        $quoteId = $this->getRequest()->getParam('id');
        return Mage::helper("adminhtml")
            ->getUrl('adminhtml/fakepro/add', array('quoteadv_id' => $quoteId));

    }

    /**
     * Get Fake Product HTML.
     * @return string
     * @since 1.0.5
     */
    public function getNewFakeProductHtml(){
        $product = $this->_getFakeProduct();
        $this->getChild('product_options_wrapper')->getChild('product_options')->setProduct($product);
        $html = $this->getChildHtml('product_options_wrapper');
        return $html;
    }

    /**
     * Get fake product.
     * @return Mage_Core_Model_Abstract
     * @since 1.0.5
     */
    protected function _getFakeProduct()
    {
        $fakeProduct = Mage::registry('fake_product');
        if(!isset($fakeProduct)){
            $fakeProduct = Mage::helper('fakepro')->getFakeProduct();
            $fakeProduct = Mage::getModel('catalog/product')->load($fakeProduct->getId());
            Mage::register('fake_product', $fakeProduct);
        }

        return $fakeProduct;
    }
}
