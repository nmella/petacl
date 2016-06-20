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
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2016 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

class Ophirah_Qquoteadv_Block_Sidebar extends Mage_Checkout_Block_Cart_Abstract
{
    /**
     * Function that returns the total qty of products on the quote
     *
     * @return mixed
     */
    public function getQuoteQty()
    {
        return Mage::helper('qquoteadv')->getTotalQty();
    }

    /**
     * Get the quote using the helper
     *
     * @return mixed
     */
    public function getQuote()
    {
        return Mage::helper('qquoteadv')->getQuote();
    }

    /**
     * Get a product based on its Id
     *
     * @param $productId
     * @return mixed
     */
    public function getProduct($productId)
    {
        return Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Render block HTML if allowed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::getStoreConfig('qquoteadv_general/quotations/enabled') || !Mage::getStoreConfig('qquoteadv_general/quotations/active_c2q_tmpl')) {
            return '';
        }

        return parent::_toHtml();
    }
}
