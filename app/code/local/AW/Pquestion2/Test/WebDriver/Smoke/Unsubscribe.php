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


class AW_Pquestion2_Test_WebDriver_Smoke_Unsubscribe extends EcomDev_PHPUnit_Test_WebdriverCase
{

    public function testGlobalOption()
    {
        EcomDev_PHPUnit_Test_WebDriver_Snippet::loginToAdminArea($this->_webDriver);
        $this->_setAutomaticallySubscriptionTo(true);
        $this->_goToCustomerEditPageInBackend();
        $optionsList = $this->_webDriver->findElements(
            WebDriverBy::cssSelector('select[name="subscribe_to[]"] option')
        );
        foreach ($optionsList as $option) {
            /** @var WebDriverElement $option */
            $this->assertTrue(!!$option->getAttribute('selected'), 'Option must be selected');
        }

        $this->_setAutomaticallySubscriptionTo(false);
        $this->_goToCustomerEditPageInBackend();
        $optionsList = $this->_webDriver->findElements(
            WebDriverBy::cssSelector('select[name="subscribe_to[]"] option')
        );
        foreach ($optionsList as $option) {
            /** @var WebDriverElement $option */
            $this->assertFalse(!!$option->getAttribute('selected'), 'Option must be unselected');
        }
    }

    public function testUnsubscribeFromCustomerEditInAdminArea()
    {
        EcomDev_PHPUnit_Test_WebDriver_Snippet::loginToAdminArea($this->_webDriver);
        $this->_setAutomaticallySubscriptionTo(true);
        EcomDev_PHPUnit_Test_WebDriver_Snippet::createNewCustomerFromFrontend($this->_webDriver);
        $this->_goToCustomerEditPageInBackend();
        $multiSelectContainer = $this->_webDriver->findElement(
            WebDriverBy::cssSelector('select[name="subscribe_to[]"]')
        );
        $multiSelect = new WebDriverSelect($multiSelectContainer);
        $multiSelect->deselectAll();
        $saveButton = $this->_webDriver->findElement(
            WebDriverBy::cssSelector('button[title="Save and Continue Edit"]')
        );
        $saveButton->click();
        $this->_webDriver->wait(5);//wait for js initialization
        $multiSelectContainer = $this->_webDriver->findElement(
            WebDriverBy::cssSelector('select[name="subscribe_to[]"]')
        );
        $multiSelect = new WebDriverSelect($multiSelectContainer);
        $this->assertCount(0, $multiSelect->getAllSelectedOptions(), 'Customer must be unsubscribed');
    }

    public function testUnsubscribeFromCustomerAccountInFrontend()
    {
        EcomDev_PHPUnit_Test_WebDriver_Snippet::loginToAdminArea($this->_webDriver);
        $this->_enableExtension();
        $this->_setAutomaticallySubscriptionTo(true);
        $loginData = EcomDev_PHPUnit_Test_WebDriver_Snippet::createNewCustomerFromFrontend($this->_webDriver);
        $this->_goToCustomerEditPageInFrontend($loginData['login'], $loginData['password']);
        $checkboxList = $this->_webDriver->findElements(
            WebDriverBy::cssSelector('input[name="aw_pq2_customer_subscribe_to[]"]')
        );
        foreach ($checkboxList as $checkbox) {
            /** @var WebDriverElement $checkbox*/
            $checkbox->click();
        }
        $this->_webDriver->findElement(
            WebDriverBy::cssSelector('button[title="Save"]')
        )->click();
        $checkboxList = $this->_webDriver->findElements(
            WebDriverBy::cssSelector('input[name="aw_pq2_customer_subscribe_to[]"]')
        );
        foreach ($checkboxList as $checkbox) {
            $this->assertFalse(!!$checkbox->getAttribute('checked'), 'Checkbox must be unchecked');
        }
    }

    protected function _enableExtension()
    {
        $this->_webDriver->get(
            EcomDev_PHPUnit_Test_WebDriver_Helper::getUrlByRoute(
                'adminhtml/system_config/edit', array('section' => 'aw_pq2')
            )
        );
        $fieldset = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general-head'));
        if ($fieldset->getAttribute('class') !== 'open') {
            $fieldset->click();
        }
        $selectContainer = $this->_webDriver->findElement(
            WebDriverBy::id('aw_pq2_general_is_enabled')
        );
        $select = new WebDriverSelect($selectContainer);
        $select->selectByValue("1");//set 'Enable Product Questions 2' to yes
        $saveButton = $this->_webDriver->findElement(
            WebDriverBy::cssSelector(".form-buttons button")
        );
        $saveButton->click();
    }

    protected function _setAutomaticallySubscriptionTo($yes = true)
    {
        $this->_webDriver->get(
            EcomDev_PHPUnit_Test_WebDriver_Helper::getUrlByRoute(
                'adminhtml/system_config/edit', array('section' => 'aw_pq2')
            )
        );
        $fieldset = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general-head'));
        if ($fieldset->getAttribute('class') !== 'open') {
            $fieldset->click();
        }
        $selectContainer = $this->_webDriver->findElement(
            WebDriverBy::id('aw_pq2_general_allow_subscribe_to_notification_automatically')
        );
        $select = new WebDriverSelect($selectContainer);
        $select->selectByValue($yes?"1":"0");
        $saveButton = $this->_webDriver->findElement(
            WebDriverBy::cssSelector(".form-buttons button")
        );
        $saveButton->click();
    }

    protected function _goToCustomerEditPageInBackend()
    {
        $this->_webDriver->get(
            EcomDev_PHPUnit_Test_WebDriver_Helper::getUrlByRoute('adminhtml/customer/index')
        );
        $linkToEditPageList = $this->_webDriver->findElements(
            WebDriverBy::cssSelector(".grid table tr")
        );
        /** @var WebDriverElement $linkToEditPage */
        $linkToEditPage = end($linkToEditPageList);
        $linkToEditPage->findElement(WebDriverBy::tagName('a'))->click();//go to edit page
        //go to pq2 tab
        $this->_webDriver->get($this->_webDriver->getCurrentURL() . 'tab/customer_edit_tab_aw_pq2/');
        $this->_webDriver->wait(5);//wait for js initialization
    }

    protected function _goToCustomerEditPageInFrontend($login, $password)
    {
        EcomDev_PHPUnit_Test_WebDriver_Snippet::loginToCustomerArea($this->_webDriver, $login, $password);
        $this->_webDriver->get(
            EcomDev_PHPUnit_Test_WebDriver_Helper::getUrlByRoute('aw_pq2/customer/index')
        );
    }
}