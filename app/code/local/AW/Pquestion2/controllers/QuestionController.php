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


class AW_Pquestion2_QuestionController extends Mage_Core_Controller_Front_Action
{
    protected function _initQuestion()
    {
        /** @var AW_Pquestion2_Model_Question $questionModel */
        $questionModel = Mage::getModel('aw_pq2/question');
        $productId = (int)$this->getRequest()->getParam('product_id', 0);
        $content = $this->getRequest()->getParam('content', '');
        $isPrivate = $this->getRequest()->getParam('is_private', false);

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

        $visibility = AW_Pquestion2_Model_Source_Question_Visibility::PUBLIC_VALUE;
        if ($isPrivate) {
            $visibility = AW_Pquestion2_Model_Source_Question_Visibility::PRIVATE_VALUE;
        }

        $questionModel
            ->setAuthorName($authorName)
            ->setAuthorEmail($authorEmail)
            ->setCustomerId($customerId)
            ->setContent($content)
            ->setVisibility($visibility)
            ->setStatus(AW_Pquestion2_Model_Source_Question_Status::PENDING_VALUE)
            ->setSharingType(AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE)
            ->setProductId($productId)
            ->setSharingValue(array($productId))
            ->setHelpfulness(0)
            ->setShowInStoreIds(Mage::app()->getStore()->getId()) //Current Store
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setCreatedAt(Mage::getModel('core/date')->gmtDate())
        ;

        $this->_validate($questionModel);

        Mage::register('current_question', $questionModel, true);
        return $questionModel;
    }

    protected function _validate($questionModel)
    {
        $authorName = $questionModel->getAuthorName();
        if (!is_string($authorName) || strlen($authorName) <= 0) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Author name not specified")
            );
        }

        $authorEmail = $questionModel->getAuthorEmail();
        if (!is_string($authorEmail) || strlen($authorEmail) <= 0) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Author email not specified")
            );
        }

        $content = $questionModel->getContent();
        if (!is_string($content) || strlen($content) <= 0) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Question not specified")
            );
        }

        $productModel = Mage::getModel('catalog/product')->load($questionModel->getProductId());
        if (!$productModel->getId()) {
            throw new Exception(
                Mage::helper('aw_pq2')->__("Product not found")
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
	protected function _isCustomerCanVoteQuestion()
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
            $questionModel = $this->_initQuestion();
            $questionModel->save();
        } catch(Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $_isSubscribed = Mage::helper('aw_pq2/notification')->isCanNotifyCustomer(
            $questionModel->getAuthorEmail(), AW_Pquestion2_Model_Source_Notification_Type::QUESTION_AUTO_RESPONDER
        );
        if (!$_isSubscribed) {
            if ($questionModel->getCustomerId()) {
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Your question has been received. You can track all your questions and '
                        . ' the answers given <a href="%s">here</a>',
                        Mage::getUrl('aw_pq2/customer/index',
                            array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure())
                        )
                    )
                );
            } else {
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Your question has been received.'
                    )
                );
            }
        } else {
            if ($questionModel->getCustomerId()) {
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Your question has been received. A notification will be sent once the answer is published.'
                        . ' You can also see all your questions and the answers given <a href="%s">here</a>',
                        Mage::getUrl('aw_pq2/customer/index',
                            array('_secure' => Mage::app()->getStore(true)->isCurrentlySecure())
                        )
                    )
                );
            } else {
                $this->_getSession()->addSuccess(
                    $this->__(
                        'Your question has been received. A notification will be sent once the answer is published.'
                    )
                );
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
			if ($this->_isCustomerCanVoteQuestion()) {
				$questionId = (int)$this->getRequest()->getParam('question_id', 0);
				$questionModel = Mage::getModel('aw_pq2/question')->load($questionId);
				if ($questionModel->getId()) {
					if (Mage::getSingleton('customer/session')->isLoggedIn()) {
						$customer = Mage::getSingleton('customer/session')->getCustomer();
					} else {
						$customer = Mage::getSingleton('log/visitor');
					}
					$value = $this->getRequest()->getParam('value', 1);
					try {
						$questionModel->addHelpful($customer, $value);
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
			if ($this->_isCustomerCanVoteQuestion()) {
				$questionId = (int)$this->getRequest()->getParam('question_id', 0);
				$questionModel = Mage::getModel('aw_pq2/question')->load($questionId);
				if ($questionModel->getId()) {
					if (Mage::getSingleton('customer/session')->isLoggedIn()) {
						$customer = Mage::getSingleton('customer/session')->getCustomer();
					} else {
						$customer = Mage::getSingleton('log/visitor');
					}
					$value = $this->getRequest()->getParam('value', -1);
					try {
						$questionModel->addHelpful($customer, $value);
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

    public function listAction()
    {
        $result = array(
            'success'  => true,
            'messages' => array(),
        );
        if (Mage::helper('aw_pq2/config')->getIsEnabled()) {
            Mage::register(
                'current_product',
                Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'))
            );
            $this->loadLayout();
            $result['block'] = $this->getLayout()->getBlock('aw_pq2_question_list')->toHtml();
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