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

class Ophirah_Qquoteadv_Model_Quote_Total_C2qtotal extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Totals collector for the cart2quote quotation totals
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $items = $this->_getAddressItems($address);
        $quote = $address->getQuote();

        if ($quote->getData('quote_id')){

            // Get custom quote prices for the products by quoteId
            $quoteCustomPrices = Mage::getModel('qquoteadv/qqadvproduct')->getQuoteCustomPrices($quote->getData('quote_id'));

            $optionCount = 0;
            $optionId = 0;
            $countMax = 0;

            // Clear original price information
            $orgFinalBasePrice = 0;
            $orgBasePrice = 0;
            $quoteFinalBasePrice = 0;
            $quoteCostPrice = 0;
            $calcOrgPrice = true;
            $totalSpecialPrice = 0;

            // Only Calculate Original Prices Once
            if ($quote->getData('orgFinalBasePrice') > 0) {
                $calcOrgPrice = false;
            }

            // AW_Afptc warning:
            if (Mage::helper('core')->isModuleEnabled('AW_Afptc')){
                //this can happen with "aheadWorks Add Free Product to Cart"/"AW_Afptc"
                $message = "Cart2Quote does not has support for 'aheadWorks - Add Free Product to Cart' / 'AW_Afptc' ";
                Mage::log('Warning: ' . $message , null, 'c2q.log');
            }

            Mage::register('requests_handeled', array()); //needed for dynamic bundles
            foreach ($items as $item) {
                if($item->getParentItem() == null){

                    // Counter for option products
                    if ($optionId != $item->getBuyRequest()->getData('product')) {
                        $countMax = Mage::getModel('qquoteadv/qqadvproduct')->getCountMax($item->getBuyRequest());
                    }
                    if ($optionCount == $countMax) {
                        $optionCount = $optionId = 0;
                    }
                    if ($optionId == $item->getBuyRequest()->getData('product') && $optionId != 0) {
                        $optionCount++;
                    }
                    $optionId = $item->getBuyRequest()->getData('product');

                    // Check if quote item has a custom price
                    $item = Mage::getModel('qquoteadv/qqadvproduct')->getCustomPriceCheck($quoteCustomPrices, $item, $optionCount);

                    // Reset Original Price
                    // And add new item original prices
                    $itemFinalPrice = 0;
                    $itemCostPrice = 0;
                    $itemBasePrice = 0;

                    if ($calcOrgPrice === true){
                        if (!$item->getData('parent_item_id')) {
                            if ($item->getProductType() == "bundle") {
                                $itemProductQty = $item->getProduct()->getQty();
                                if($itemProductQty === null) {
                                    $itemProductQty = $item->getQty();
                                }

                                if ($item->getData('quote_org_price') > 0 && $itemProductQty > 0) {
                                    // Item Original Price
                                    $itemFinalPrice = $item->getData('quote_org_price') * $itemProductQty;
                                    // Item Base Price
                                    $itemBasePrice = $itemFinalPrice;
                                }

                                $itemSpecialPrice = 0;
                            } else {
                                $store = Mage::app()->getStore($item->getStoreId());

                                //check if this product has options
                                if($item->getProduct()->getHasOptions() == 1){
                                    $baseItemFinalPrice = $item->getProduct()->getFinalPrice();
                                    $getFinalPrice = $item->getProduct()->getFinalPrice();

                                    $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                        ->setQuote($quote)
                                        ->addFieldToFilter('quote_id', $quote->getId())
                                        ->addFieldToFilter('product_id', $item->getProduct()->getId())
                                        ->addFieldToFilter('request_qty', $item->getQty());

                                    if($item->getQuoteadvProductId()){
                                        $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                    }

                                    $requestItem = $requestItemCollection->getFirstItem();
                                    if($requestItem){
                                        if($requestItem->getOriginalCurPrice() != 0){
                                            //$baseItemFinalPrice = $requestItem->getOriginalPrice();
                                            //base is different from Magento base here.
                                            $baseItemFinalPrice = $requestItem->getOriginalCurPrice();
                                            $getFinalPrice = $requestItem->getOriginalCurPrice();
                                        }
                                    }

                                    // bad compare method
                                    // Check if current item has a custom price.
                                    foreach ($quoteCustomPrices as $requestId => $quoteCustomPrice) {
                                        // Basic Compare
                                        $compareQuote = $quoteCustomPrice->getData('product_id');
                                        $compareItem = $item->getData('product_id');

                                        // For products with options and parent-child relations
                                        // Dynamic bundle options can have different object with the same product_id
                                        if (isset($product_id) && $product_id == $quoteCustomPrice->getData('product_id')) {
                                            // Custom Options
                                            if (isset($buyRequest['options'])) {
                                                if ($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                                                    $attribute = unserialize($quoteCustomPrice->getData('attribute'));
                                                    $compareQuote = $attribute['options'];
                                                    $compareItem = $buyRequest['options'];
                                                } else {
                                                    if ($item->getData('product_type') == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) {
                                                        $attribute = unserialize($quoteCustomPrice->getData('attribute'));
                                                        $compareQuote = $attribute['options'];
                                                        $compareItem = $buyRequest['options'];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } elseif (Mage::getModel('qquoteadv/qqadvproductdownloadable')->isDownloadable($item)){
                                    //$optionsinfo = $item->getBuyRequest();
                                    $item = Mage::getModel('qquoteadv/qqadvproductdownloadable')->prepareDownloadableProductFromBuyRequest($item);

                                    $getFinalPrice = $item->getProduct()->getFinalPrice();

                                    $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                        ->setQuote($quote)
                                        ->addFieldToFilter('quote_id', $quote->getId())
                                        ->addFieldToFilter('product_id', $item->getProduct()->getId())
                                        ->addFieldToFilter('request_qty', $item->getQty());

                                    if($item->getQuoteadvProductId()){
                                        $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                    }

                                    $requestItem = $requestItemCollection->getFirstItem();
                                    if($requestItem){
                                        if($requestItem->getOriginalCurPrice() != 0){
                                            $getFinalPrice = $requestItem->getOriginalCurPrice();
                                        }
                                    }
                                } else {
                                    //usually simple products
                                    $getFinalPrice = $item->getProduct()->getFinalPrice();

                                    $requestItemCollection = Mage::getModel('qquoteadv/requestitem')->getCollection()
                                        ->setQuote($quote)
                                        ->addFieldToFilter('quote_id', $quote->getId())
                                        ->addFieldToFilter('product_id', $item->getProduct()->getId())
                                        ->addFieldToFilter('request_qty', $item->getQty());

                                    if($item->getQuoteadvProductId()){
                                        $requestItemCollection->addFieldToFilter('quoteadv_product_id', $item->getQuoteadvProductId());
                                    }

                                    $requestItem = $requestItemCollection->getFirstItem();
                                    if($requestItem){
                                        if($requestItem->getOriginalCurPrice() != 0){
                                            $getFinalPrice = $requestItem->getOriginalCurPrice();
                                        }
                                    }
                                }

                                // Item Original Price
                                if(Mage::helper('tax')->priceIncludesTax($store->getStoreId())){
                                    //if price is filled including tax, get it excluding tax:
                                    $itemPriceExcludingTax = Mage::helper('tax')->getPrice($item->getProduct(), $getFinalPrice, false, $address, null, null, $item->getStoreId(), true, false);
//                                    //then get the tax rate for the current store
//                                    $taxCalculation = Mage::getModel('tax/calculation');
//                                    $request = $taxCalculation->getRateOriginRequest($store);
//                                    $taxClassId = $item->getProduct()->getTaxClassId();
//                                    $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));
//                                    $itemProductFinalPrice = ($itemPriceExcludingTax * (100+$percent))/100;
                                    $itemProductFinalPrice = $itemPriceExcludingTax;

                                    $itemSpecialPrice = $item->getProduct()->getSpecialPrice();
                                    if(isset($itemSpecialPrice) && !empty($itemSpecialPrice)){
                                        //if price is filled including tax, get it excluding tax:
                                        $itemSpecialPriceExcludingTax = Mage::helper('tax')->getPrice($item->getProduct(), $itemSpecialPrice, false, $address, null, null, $item->getStoreId(), true, false);
//                                        $itemSpecialPrice = ($itemSpecialPriceExcludingTax * (100+$percent))/100;
                                        $itemSpecialPrice = $itemSpecialPriceExcludingTax;
                                    }
                                } else {
                                    $itemProductFinalPrice = $getFinalPrice;
                                    $itemProductPrice = $item->getProduct()->getPrice();
                                    $itemSpecialPrice = $item->getProduct()->getSpecialPrice();
                                }

                                $itemProductQty = $item->getQty();
                                $itemFinalPrice = $itemProductFinalPrice * $itemProductQty;

                                if(!isset($itemProductPrice) || $itemProductPrice == 0){
                                    $itemBasePrice = $itemProductFinalPrice * $itemProductQty;
//                                    $itemSpecialPrice = 0;
                                } else {
                                    $itemBasePrice = $itemProductPrice * $itemProductQty;
//                                    $orgItemProductFinalPrice = $item->getProduct()->getFinalPrice();
//                                    if($orgItemProductFinalPrice != $itemProductPrice){
//                                        $itemSpecialPrice = ($itemProductPrice-$itemSpecialPrice) * $itemProductQty;
//                                    } else {
//                                        $itemSpecialPrice = 0;
//                                    }
                                }
                                // Item Cost Price
                            }

                            $itemProductQty = $item->getProduct()->getQty();
                            if($itemProductQty === null) {
                                $itemProductQty = $item->getQty();
                            }

                            // Store item cost price
                            $itemCostPrice = (float)$item->getData('quote_item_cost') * $itemProductQty;
                            if ($itemCostPrice > 0) {
                                $quoteCostPrice += $itemCostPrice;
                            }
                            // Store item original price
                            $orgFinalBasePrice += $itemFinalPrice;
                            $orgBasePrice += $itemBasePrice;
                            $totalSpecialPrice += $itemSpecialPrice;

                            // Store Original Total with quote
                            $quote->setData('orgFinalBasePrice', $orgFinalBasePrice);
                            $quote->setData('orgBasePrice', $orgBasePrice);
                            $quote->setData('orgSpecialPrices', $totalSpecialPrice);
                            $quote->setData('quoteBaseCostPrice', $quoteCostPrice);
                        }
                    }

                    // set custom price, if available
                    if ($item->getData('custom_base_price') != NULL && $item->getData('custom_base_price') > 0) {

                        // New custom Price
                        $rowTotal = $item->getData('custom_base_price');
                        $baseRowTotal = $item->getData('custom_base_price');

                        $itemProductQty = $item->getProduct()->getQty();
                        if($itemProductQty === null) {
                            $itemProductQty = $item->getQty();
                        }

                        // Store item custom price
                        $itemQuotePrice = $item->getData('custom_base_price') * $itemProductQty;
                        $quoteFinalBasePrice += $itemQuotePrice;
                        $quote->setData('quoteFinalBasePrice', $quoteFinalBasePrice);

                        // remove original item price from subtotal
                        $address->setTotalAmount(
                            'subtotal', $address->getSubtotal() - $item->getRowTotal()
                        );
                        $address->setBaseTotalAmount(
                            'subtotal', $address->getBaseSubtotal() - $item->getBaseRowTotal()
                        );

                        // Set custom price for the product
                        $item->setPrice($rowTotal)
                            ->setBaseOriginalPrice($baseRowTotal)
                            ->calcRowTotal();

                    }
                    $item->setQtyToAdd(0);
                } else {
                    // object is a child
                }
            }
            Mage::unregister('requests_handeled'); //needed for dynamic bundles
        }

        return $this;
    }

}
