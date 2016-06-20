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


class AW_Pquestion2_Test_Controller_Adminhtml_QuestionController extends EcomDev_PHPUnit_Test_Case_Controller
{
    const FAKE_USER_ID = 999999999;

    public function setUp()
    {
        $this->_fakeLogin();
        parent::setUp();
    }

    public function tearDown()
    {
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->unsetAll();
        $adminSession->getCookie()->delete($adminSession->getSessionName());
        Mage::unregister('current_question');
        parent::tearDown();
    }

    /**
     * Logged in to Magento with fake user to test an adminhtml controllers
     */
    protected function _fakeLogin()
    {
        $this->_registerUserMock();
        Mage::getSingleton('adminhtml/url')->turnOffSecretKey();
        $session = Mage::getSingleton('admin/session');
        $session->login('fakeuser', 'fakeuser_pass');
    }
    /**
     * Creates a mock object for admin/user Magento Model
     *
     * @return AW_Pquestion2_Test_Controller_Adminhtml_QuestionController
     */
    protected function _registerUserMock()
    {
        $user = $this->getModelMock('admin/user');
        $user->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::FAKE_USER_ID))
        ;
        $this->replaceByMock('model', 'admin/user', $user);
        return $this;
    }

    /**
     * Test whether fake user successfully logged in
     * @test
     */
    public function testLoggedIn()
    {
        $this->assertTrue(Mage::getSingleton('admin/session')->isLoggedIn());
        $this->assertEquals(Mage::getSingleton('admin/session')->getUser()->getId(), self::FAKE_USER_ID);
    }

    /**
     * Test _initQuestion && editAction
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function editAction($questionId, $productId, $customerId, $sessionPQFormData, $expectation)
    {
        $params = array(
            'id'          => $questionId,
            'product_id'  => $productId,
            'customer_id' => $customerId,
        );
        if (null !== $sessionPQFormData) {
            Mage::getSingleton('adminhtml/session')->setPQFormData($sessionPQFormData);
        }
        $this->dispatch('adminhtml/awpq2_question/edit', $params);

        if ($expectation['redirect']) {
            $this->assertRedirect();
            return;
        }

        $question = Mage::registry('current_question');
        $this->assertInstanceOf('AW_Pquestion2_Model_Question', $question);
        $this->assertEquals(
            $expectation['question_content'],
            $question->getContent(),
            'Check question content'
        );

        $this->assertEquals(
            $expectation['product_name'],
            $question->getProductName(),
            'Check product name'
        );

        $this->assertEquals(
            $expectation['author_name'],
            $question->getAuthorName(),
            'Check author\'s name'
        );

        $title = Mage::app()->getLayout()->getBlock('head')->getTitle();
        $this->assertContains(
            $expectation['page_title'],
            $title,
            'Check page title'
        );
    }
}