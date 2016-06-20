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

abstract class Ophirah_Qquoteadv_Block_Form_Abstract extends Mage_Checkout_Block_Cart_Abstract
{
    /**
     * Function that (re-)sets the customer data on the customer (from session) object
     *
     * @return mixed
     */
    public function setCustomer(){
        // get customer from session
        $customer = $this->getCustomerSession()->getCustomer();

        // Retrieve Customer Request data from session
//        $client_request = "";
        $quoteData = $this->getCustomerSession()->getQuoteData();
        if($quoteData){
//            if(isset($quoteData['customer']['client_request'])){
//                $client_request = $quoteData['customer']['client_request'];
//            }
            $customer->addData($quoteData['customer']);
        }
        // Add customer information after shipping estimate request
        $quoteCustomer = Mage::getSingleton('customer/session')->getData('quoteCustomer');
        if ($quoteCustomer) {
            $customer->addData($quoteCustomer->getData());
        }
        return $customer;
    }

    /**
     * Get customer session data
     * @return session data
     */
    public function getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get customer email from the customer on the customer session
     *
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getCustomerSession()->getCustomer()->getEmail();
    }

    /**
     * Check if customer is logged in
     *
     * @return mixed
     */
    public function isCustomerLoggedIn()
    {
        return $this->getCustomerSession()->isLoggedIn();
    }

    /**
     * Getter for the state settings
     *
     * @return mixed
     */
    public function getStateSettings(){
        if(!isset($this->_stateSettings)){
            return $this->_stateSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/address_details/state');
        }else{
            return $this->_stateSettings;
        }
    }

    /**
     * Getter for the phone settings
     *
     * @return mixed
     */
    public function getPhoneSettings(){
        if(!isset($this->_phoneSettings)){
            return $this->_phoneSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/telephone');
        }else{
            return $this->_phoneSettings;
        }
    }

    /**
     * Getter for the company settings
     *
     * @return mixed
     */
    public function getCompanySettings(){
        if(!isset($this->_companySettings)){
            return $this->_companySettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/company');
        }else{
            return $this->_companySettings;
        }
    }

    /**
     * Getter for the vattax settings
     *
     * @return mixed
     */
    public function getVatTaxSettings(){
        if(!isset($this->_vatTaxSettings)){
            return $this->_vatTaxSettings = Mage::getStoreConfig('qquoteadv_quote_form_builder/account_details/vattaxid');
        }else{
            return $this->_vatTaxSettings;
        }
    }
}
