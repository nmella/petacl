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


class AW_Pquestion2_AnswerController extends Mage_Core_Controller_Front_Action
{
    protected function _initAnswer()
    {
        /** @var AW_Pquestion2_Model_Answer $answerModel */
        $answerModel = Mage::getModel('aw_pq2/answer');
        $questionId = (int)$this->getRequest()->getParam('question_id', 0);
        $content = $this->getRequest()->getParam('content', null);

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerModel = Mage::getSingleton('customer/session')->getCustomer();
            $authorName = $this->getRequest()->getParam('author_name', $customerModel->getName());
            $authorEmail = $customerModel->getEmail();
            $customerId = $customerModel->getId();
        } else {
            $authorName = $this->getRequest()->getParam('author_name', null);
            $authorEmail = $this->getRequest()->getParam('author_email', null);
            $customerId = 0;
        }

        $answerModel
            ->setQuestionId($questionId)
            ->setAuthorName($authorName)
            ->setAuthorEmail($authorEmail)
            ->setCustomerId($customerId)
            ->setContent($content)
            ->setStatus(AW_Pquestion2_Model_Source_Question_Status::PENDING_VALUE)
            ->setHelpfulness(0)
            ->setIsAdmin(0)
            ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
        ;
        if (!Mage::helper('aw_pq2/config')->getRequireModerateCustomerAnswer()) {
            $answerModel->setStatus(AW_Pquestion2_Model_Source_Question_Status::APPROVED_VALUE);
        }

        $this->_validate($answerModel);

        Mage::register('current_answer', $answerModel, true);
        return $answerModel;
    }

    protected function _validate($answerModel)
    {
        $authorName = $answerModel->getAuthorName();
        if (!is_string($authorName) || strlen($authorName) <= 0) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Author name not specified")
            );
        }

        $authorEmail = $answerModel->getAuthorEmail();
        if (!is_string($authorEmail) || strlen($authorEmail) <= 0) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Author email not specified")
            );
        }

        $content = $answerModel->getContent();
        if (!is_string($content) || strlen($content) <= 0) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Answer not specified")
            );
        }

        $questionModel = Mage::getModel('aw_pq2/question')->load($answerModel->getQuestionId());
        if (!$questionModel->getId()) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Question not found")
            );
        }
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

	/**
	 * Retrieve whether customer can vote
	 *
     * @return bool
     */
	protected function _isCustomerCanVoteAnswer()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::helper('aw_pq2/config')->isAllowGuestRateHelpfulness()
        ;
    }

    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        if (!Mage::helper('aw_pq2/config')->getIsEnabled()) {
            $this->_getSession()->addError('Product Questions 2 disabled');
            return $this->_redirectUrl($this->_getRefererUrl());
        }
        try {
            $answerModel = $this->_initAnswer();
            $answerModel->save();
        } catch(Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $isSubscribed = Mage::helper('aw_pq2/notification')->isCanNotifyCustomer(
            $answerModel->getAuthorEmail(), AW_Pquestion2_Model_Source_Notification_Type::ANSWER_AUTO_RESPONDER
        );
        if (Mage::helper('aw_pq2/config')->getRequireModerateCustomerAnswer()) {
            if ($isSubscribed) {
                if ($answerModel->getCustomerId()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Your answer has been received. You will be notified on the answer status change.'
                            . ' You can track all your questions and the answers given <a href="%s">here</a>',
                            Mage::getUrl('aw_pq2/customer/index',
                                array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure())
                            )
                        )
                    );
                }else {
                    $this->_getSession()->addSuccess(
                        $this->__(
                            'Your answer has been received. You will be notified on the answer status change.'
                        )
                    );
                }
            } else {
                if ($answerModel->getCustomerId()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Your answer has been received.'
                            . ' You can track all your questions and the answers given <a href="%s">here</a>',
                            Mage::getUrl('aw_pq2/customer/index',
                                array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure())
                            )
                        )
                    );
                } else {
                    $this->_getSession()->addSuccess($this->__('Your answer has been received.'));
                }
            }
        } else {
            if ($answerModel->getCustomerId()) {
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Answer added successfully.'
                        . ' You can track all your questions and the answers given <a href="%s">here</a>',
                        Mage::getUrl('aw_pq2/customer/index',
                            array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure())
                        )
                    )
                );
            } else {
                $this->_getSession()->addSuccess($this->__('Answer added successfully'));
            }
        }
        return $this->_redirectUrl($this->_getRefererUrl());
    }

    public function likeAction()
    {
        $result = array(
            'success'  => true,
            'messages' => array(),
        );

        if (Mage::helper('aw_pq2/config')->getIsEnabled()) {
            if ($this->_isCustomerCanVoteAnswer()) {
                $answerId = (int)$this->getRequest()->getParam('answer_id', 0);
                $answerModel = Mage::getModel('aw_pq2/answer')->load($answerId);
                if ($answerModel->getId()) {
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                    } else {
                        $customer = Mage::getSingleton('log/visitor');
                    }
                    $value = $this->getRequest()->getParam('value', 1);
                    try {
                        $answerModel->addHelpful($customer, $value);
                    } catch (Exception $e) {
                        $result['success'] = false;
                        $result['messages'][] = Mage::helper('aw_pq2')->__($e->getMessage());
                    }
                } else {
                    $result['success'] = false;
                    $result['messages'][] = Mage::helper('aw_pq2')->__("Question not found");
                }
            } else {
                $result['success'] = false;
                $result['messages'][] = Mage::helper('aw_pq2')->__('Only registered customers can rate helpfulness');
            }
        } else {
            $result['success'] = false;
            $result['messages'][] = Mage::helper('aw_pq2')->__('Product Questions 2 disabled');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function dislikeAction()
    {
        $result = array(
            'success'  => true,
            'messages' => array(),
        );

        if (Mage::helper('aw_pq2/config')->getIsEnabled()) {
            if ($this->_isCustomerCanVoteAnswer()) {
                $answerId = (int)$this->getRequest()->getParam('answer_id', 0);
                $answerModel = Mage::getModel('aw_pq2/answer')->load($answerId);
                if ($answerModel->getId()) {
                    if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $customer = Mage::getSingleton('customer/session')->getCustomer();
                    } else {
                        $customer = Mage::getSingleton('log/visitor');
                    }
                    $value = $this->getRequest()->getParam('value', -1);
                    try {
                        $answerModel->addHelpful($customer, $value);
                    } catch (Exception $e) {
                        $result['success'] = false;
                        $result['messages'][] = Mage::helper('aw_pq2')->__($e->getMessage());
                    }
                } else {
                    $result['success'] = false;
                    $result['messages'][] = Mage::helper('aw_pq2')->__("Question not found");
                }
            } else {
                $result['success'] = false;
                $result['messages'][] = Mage::helper('aw_pq2')->__('Only registered customers can rate helpfulness');
            }
        } else {
            $result['success'] = false;
            $result['messages'][] = Mage::helper('aw_pq2')->__('Product Questions 2 disabled');
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    /**
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        $formKeyFromRequest = $this->getRequest()->getParam('form_key', null);
        $formKeyFromSession = $this->_getSession()->getFormKey();
        if (!$formKeyFromRequest || $formKeyFromRequest != $formKeyFromSession) {
            return false;
        }
        return true;
    }
}