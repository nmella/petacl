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
class Ophirah_Qquoteadv_Helper_Catalog_Product_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getUrlAdd2QquoteadvList(Mage_Catalog_Model_Product $product, $additional = array())
    {
        $quoteAdvUrlPath = 'qquoteadv/index';
        //check if there are no required options
        $hasRequiredOptions = false;
        $request = new Varien_Object(array('qty' => 1));
        $resultPrepare = $product->getTypeInstance(true)->prepareForCartAdvanced($request, $product, null);
        if (is_string($resultPrepare)) $hasRequiredOptions = true;

        if ($product->getTypeInstance(true)->hasRequiredOptions($product) || $hasRequiredOptions) {
            $url = $product->getProductUrl();
            $link = (strpos($url, '?') !== false) ? '&' : '?';
            return $url . $link . 'options=cart&c2qredirect=1';
        }
        return $this->getUrlAdd2QquoteadvById($product->getId());
    }

    /**
     * Get add to quote url for a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return mixed
     */
    public function getUrlAdd2Qquoteadv(Mage_Catalog_Model_Product $product, $additional = array())
    {
        return $this->getUrlAdd2QquoteadvById($product->getId());
    }

    /**
     * Get the add to quote url by product id
     *
     * @param $productId
     * @return mixed
     */
    public function getUrlAdd2QquoteadvById($productId)
    {
        $quoteAdvUrlPath = 'qquoteadv/index';
        $url = "addItem";
        if (Mage::getStoreConfig('qquoteadv_quote_frontend/catalog/ajax_add') && Mage::helper('qquoteadv')->checkQuickQuote() != "1") $url = "addItemAjax";
        return Mage::getUrl($quoteAdvUrlPath . '/' . $url . '/', array("product" => $productId, '_secure' => Mage::app()->getStore()->isCurrentlySecure()));
    }

    /**
     * Get the onclick action for the add to quote button
     *
     * @param $productId
     * @return string
     */
    public function getAddToQuoteAction($productId)
    {
        $isAjax = Mage::getStoreConfig('qquoteadv_quote_frontend/catalog/ajax_add');
        $url = $this->getUrlAdd2QquoteadvById($productId);
        $actionQuote = "addQuote('" . $url . "', $isAjax );";

        if (Mage::helper('qquoteadv')->checkQuickQuote()) {
            // Set Quick Quote Action
            $actionQuote =
                "getProductInfo('".
                Mage::helper('qquoteadv/catalog_product_data')->getQuickQuoteProductUrl($productId).
                "'); ";
        }

        return $actionQuote;
    }

    /**
     * Function that can compare bundles based on the same product
     *
     * @param $product_id
     * @param $options1
     * @param $options2
     * @return bool
     */
    public function compareBundles($product_id, $options1, $options2)
    {
        $product = Mage::getModel('catalog/product')->load($product_id);
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product2 = clone $product;

            $product->getTypeInstance()->prepareForCartAdvanced(new Varien_Object(unserialize($options1)), $product);
            $product2->getTypeInstance()->prepareForCartAdvanced(new Varien_Object(unserialize($options2)), $product2);

            $identity1 = $product->getCustomOption('bundle_identity');
            $identity2 = $product2->getCustomOption('bundle_identity');

            if ($identity2 != null) {
                if (($identity1->getValue()) == ($identity2->getValue())) {
                    return true;
                }
            }

        }

        return false;
    }

    /**
     * Function that can compare configurables based on the same product
     *
     * @param $product_id
     * @param $options1
     * @param $options2
     * @return bool
     */
    public function compareConfigurable($product_id, $options1, $options2)
    {
        $product = Mage::getModel('catalog/product')->load($product_id);
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product2 = clone $product;

            $product->getTypeInstance()->prepareForCartAdvanced(new Varien_Object(unserialize($options1)), $product);
            $product2->getTypeInstance()->prepareForCartAdvanced(new Varien_Object(unserialize($options2)), $product2);

            $identity1 = $product->getCustomOption('attributes');
            $identity2 = $product2->getCustomOption('attributes');

            if ($identity1 instanceof Mage_Catalog_Model_Product_Configuration_Item_Option &&
                $identity2 instanceof Mage_Catalog_Model_Product_Configuration_Item_Option
            ) {
                if ($identity1->getValue() == $identity2->getValue()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Calculate new image sizes from original ratio
     * Supports both Mage image object as image files
     *
     * @param  $image
     * @param null $width
     * @param null $height
     * @return array
     */
    public function getItemPictureDimensions($image, $width = null, $height = null)
    {
        // Define variables
        $return = array();
        $newRatio = null;

        // Original image size
        // Mage image object
        if (is_object($image) && ($image instanceof Mage_Catalog_Helper_Image)) {
            $orgWidth = (int)$image->getOriginalWidth();
            $orgHeight = (int)$image->getOriginalHeight();
        }

        // Zend PDF image object
        if (is_object($image) && ($image instanceof Zend_Pdf_Resource_Image)) {
            $orgWidth = (int)$image->getPixelWidth();
            $orgHeight = (int)$image->getPixelHeight();
        }

        // File
        if (is_file($image)) {
            list($orgWidth, $orgHeight, $type, $attr) = getimagesize($image);
        }


        if (isset($orgWidth) && isset($orgHeight)) {
            // Calculate original ratio
            $originalRatio = $orgWidth / $orgHeight;

            $newWidth = $orgWidth;
            $newHeight = $orgHeight;

            // Width is largest size
            if ($originalRatio > 1) {
                if (!$width == null && (int)$width > 0) {
                    $newWidth = $width;
                    $newHeight = $width / $originalRatio;
                } elseif (!$height == null && (int)$height > 0) {
                    $newWidth = $height;
                    $newHeight = $height / $originalRatio;
                }
                // Height is largest size
            } else {
                if (!$height == null && (int)$height > 0) {
                    $newWidth = $height * $originalRatio;
                    $newHeight = $height;
                } elseif (!$width == null && (int)$width > 0) {
                    $newWidth = $width * $originalRatio;
                    $newHeight = $width;
                }
            }

            $return['width'] = (int)$newWidth;
            $return['height'] = (int)$newHeight;
        }

        return $return;

    }

    /**
     * Get the add to quote url by product id
     *
     * @param $productId
     * @return mixed
     */
    public function getQuickQuoteProductUrl($productId)
    {
        return Mage::getUrl(
            'qquoteadv/index/quickquoteview',
            array(
                "product" => $productId,
                '_secure'=> Mage::app()->getStore()->isCurrentlySecure()
            )
        );
    }
}