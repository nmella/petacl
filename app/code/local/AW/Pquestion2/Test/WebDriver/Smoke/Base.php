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


class AW_Pquestion2_Test_WebDriver_Smoke_Base extends EcomDev_PHPUnit_Test_WebdriverCase
{
    public function testModuleInstallation()
    {
        EcomDev_PHPUnit_Test_WebDriver_Snippet::loginToAdminArea($this->_webDriver);
        $this->_webDriver->get(
            EcomDev_PHPUnit_Test_WebDriver_Helper::getUrlByRoute(
                'adminhtml/system_config/edit', array('section' => 'aw_pq2')
            )
        );
        //check on logged in
        $this->assertContains(
            'Configuration / System / Magento Admin', $this->_webDriver->getTitle(),
            'Admin is not logged in'
        );
        //check on install
        $this->assertContains('Configuration / System / Magento Admin', $this->_webDriver->getTitle());
        $elements = $this->_webDriver->findElements(WebDriverBy::id('aw_pq2_general-head'));
        $this->assertGreaterThan(0, count($elements), 'Can not find #aw_pq2_general-head on system configuration');
        $this->_checkDefaultSettings();
    }

    protected function _checkDefaultSettings()
    {
        //GENERAL SETTINGS
        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general_is_enabled'));
        $this->assertEquals("0", $element->getAttribute('value'), "'Enable Product Questions 2' must be 'No'");

        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general_allow_guest_to_ask_question'));
        $this->assertEquals(
            "1", $element->getAttribute('value'), "'Who Can Ask Questions from Product Page' must be 'Anyone'"
        );

        $element = $this->_webDriver->findElement(
            WebDriverBy::id('aw_pq2_general_allow_customer_to_add_answer_from_product_page')
        );
        $this->assertEquals(
            "1", $element->getAttribute('value'), "'Who Can Answer Questions from Product Page' must be 'Nobody'"
        );

        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general_require_moderate_customer_answer'));
        $this->assertEquals("0", $element->getAttribute('value'), "\"Approve Answers Automatically\" must be 'No'");

        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general_bought_product_days_ago'));
        $this->assertEquals(
            "0", $element->getAttribute('value'),
            "'Do not send \"Ask Customers\" emails to the customers who bought product more than X days ago' must be '0'"
        );

        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_general_allow_guest_rate_helpfulness'));
        $this->assertEquals("1", $element->getAttribute('value'), "'Guests can rate helpfulness' must be 'Yes'");

        $element = $this->_webDriver->findElement(
            WebDriverBy::id('aw_pq2_general_allow_subscribe_to_notification_automatically')
        );
        $this->assertEquals(
            "1", $element->getAttribute('value'),
            "'Subscribe customers to Product Questions emails automatically' must be 'Yes'"
        );

        $element = $this->_webDriver->findElement(
            WebDriverBy::id('aw_pq2_general_allow_customer_defined_question_visibility')
        );
        $this->assertEquals(
            "1", $element->getAttribute('value'),
            "'Customer-defined question visibility (Private or Public)' must be 'Yes'"
        );

        //INTERFACE SETTINGS
        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_interface_number_answers_to_display'));
        $this->assertEquals("5", $element->getAttribute('value'), "'Number of answers to display' must be '5'");

        $element = $this->_webDriver->findElement(WebDriverBy::id('aw_pq2_interface_allow_display_url_as_link'));
        $this->assertEquals("1", $element->getAttribute('value'), "'Display URLs as links' must be 'Yes'");

        //NOTIFICATION SETTINGS
        //TODO: do this after changes
    }
}