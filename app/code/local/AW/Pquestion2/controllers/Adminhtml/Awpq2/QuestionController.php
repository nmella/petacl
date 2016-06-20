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


class AW_Pquestion2_Adminhtml_Awpq2_QuestionController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/aw_pq2');

        $this
            ->_title($this->__('Catalog'))
            ->_title($this->__('Product Questions'))
        ;
        return $this;
    }

    protected function _initQuestion()
    {
        $questionModel = Mage::getModel('aw_pq2/question');
        $questionId  = (int) $this->getRequest()->getParam('id', 0);
        $productId   = (int) $this->getRequest()->getParam('product_id', 0);
        $customerId  = (int) $this->getRequest()->getParam('customer_id', 0);
        if ($questionId) {
            try {
                $questionModel->load($questionId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            $productModel = Mage::getModel('catalog/product')->load($productId);
            if (!$productModel->getId()) {
                return false;
            }
            $customerModel = Mage::getModel('customer/customer')->load($customerId);
            $currentDate = new Zend_Date;
            $questionModel
                ->setAuthorName(
                    trim($customerModel->getFirstname() . ' ' . $customerModel->getLastname())
                )
                ->setAuthorEmail($customerModel->getEmail())
                ->setCustomerId($customerModel->getId())
                ->setSharingType(AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE)
                ->setProductId($productModel->getId())
                ->setSharingValue(array($productModel->getId()))
                ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
                ->setHelpfulness(0)
                ->setShowInStoreIds(0) //All Store Views
            ;
        }

        if (!isset($productModel)) {
            $productModel = Mage::getModel('catalog/product')->load($questionModel->getProductId());
        }
        $questionModel
            ->setProductName(Mage::helper('aw_pq2')->stripTags($productModel->getName()))
            ->setStoreLabel(Mage::helper('aw_pq2')->getStoreLabel($questionModel->getStoreId()))
        ;
        if (null !== Mage::getSingleton('adminhtml/session')->getPQFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getPQFormData())
        ) {
            $questionModel->addData(Mage::getSingleton('adminhtml/session')->getPQFormData());
            Mage::getSingleton('adminhtml/session')->setPQFormData(null);
        }
        Mage::register('current_question', $questionModel, true);
        return $questionModel;
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $this->_initAction();
        $this->_title($this->__('Manage Questions'));
        $this->renderLayout();
    }

    public function listPendingAction()
    {
        $this->_initAction();
        $this->_title($this->__('Manage Questions'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $productId = $this->getRequest()->getParam('product_id', null);
        if (null === $productId) {
            $_productBlock = $this->getLayout()->createBlock('aw_pq2/adminhtml_question_new_product_grid');
            $this->_getPreparedNewQuestionBlock()->setChild('grid', $_productBlock);
            $this->renderLayout();
            return $this;
        }

        $customerId = $this->getRequest()->getParam('customer_id', null);
        if (null === $customerId) {
            $_customerBlock = $this->getLayout()->createBlock('aw_pq2/adminhtml_question_new_customer_grid');
            $this->_getPreparedNewQuestionBlock()->setChild('grid', $_customerBlock);
            $this->renderLayout();
            return $this;
        }
        $this->_forward(
            'edit', null, null,
            array(
                'product_id'  => $this->getRequest()->getParam('product_id', null),
                'customer_id' => $this->getRequest()->getParam('customer_id', null)
            )
        );
        return $this;
    }

    protected function _getPreparedNewQuestionBlock()
    {
        $this->_initAction();
        $this->_title($this->__('New Question'));
        $newQuestionBlock = $this->getLayout()->getBlock('aw_pq2.question.new');
        return $newQuestionBlock;
    }

    public function editAction()
    {
        if (!$question = $this->_initQuestion()) {
            Mage::getSingleton('adminhtml/session')->addNotice($this->__('Please choose the product'));
            $this->_redirect('*/*/new');
            return;
        }
        $this->_initAction();
        $this->_title($this->__('Manage Question'));

        $breadcrumbTitle = $breadcrumbLabel = $this->__('New Question');
        if ($question->getId()) {
            $breadcrumbTitle = $breadcrumbLabel = $this->__('Edit Question');
        }
        $this
            ->_title($breadcrumbTitle)
            ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
            ->renderLayout()
        ;
    }

    public function saveAction()
    {
        if ($formData = array_filter($this->getRequest()->getPost())) {
            $questionModel = $this->_initQuestion();
            if ($formData['sharing_type'] == AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE) {
                $formData['sharing_value'] =
                    isset($formData['sharing_products']) ? $formData['sharing_products'] : array()
                ;
            }
            if ($formData['sharing_type'] == AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE) {
                $formData['sharing_value'] = (array)$formData['sharing_value_'
                . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE];
            }
            if ($formData['sharing_type'] == AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE) {
                $formData['sharing_value'] = (array)$formData['sharing_value_'
                . AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE];
            }
            $format = Mage::app()->getLocale()->getDateTimeFormat(
                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
            );
            if (!array_key_exists('created_at', $formData)) {
                $formData['created_at'] = null;
            }
            $date = Mage::app()->getLocale()->date($formData['created_at'], $format);
            $time = $date->getTimestamp();
            $formData['created_at'] = Mage::getModel('core/date')->gmtDate(null, $time);
            unset($formData['entity_id']);
            try {
                $questionModel
                    ->addData($formData)
                    ->save()
                ;
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Question saved successfully'));
                Mage::getSingleton('adminhtml/session')->setPQFormData(null);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit',
                        array(
                             'id'  => $questionModel->getId(),
                             'tab' => $this->getRequest()->getParam('tab', null)
                        )
                    );
                    return;
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPQFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                         'id'          => $this->getRequest()->getParam('id', null),
                         'customer_id' => $this->getRequest()->getParam('customer_id', null),
                         'product_id'  => $this->getRequest()->getParam('product_id', null),
                         'tab'         => $this->getRequest()->getParam('tab', null)
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function productgridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function customergridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function sharingproductgridAction()
    {
        $this->_initQuestion();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function answersgridAction()
    {
        $this->_initQuestion();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $questionModel = $this->_initQuestion();
        try {
            $questionModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Question has been deleted successfully')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/list');
    }

    public function exportCsvAction()
    {
        $fileName = 'product_question.csv';
        $content = $this->getLayout()->createBlock('aw_pq2/adminhtml_question_grid')
            ->getCsvFile()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'product_question.xml';
        $content = $this->getLayout()->createBlock('aw_pq2/adminhtml_question_grid')
            ->getExcelFile()
        ;
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massStatusAction()
    {
        $questionIds = $this->getRequest()->getParam('id', null);
        $status = $this->getRequest()->getParam('status', null);
        try {
            if (!is_array($questionIds)) {
                throw new Mage_Core_Exception($this->__('Invalid question id(s)'));
            }

            if (null === $status) {
                throw new Mage_Core_Exception($this->__('Invalid status value'));
            }
            foreach ($questionIds as $id) {
                Mage::getSingleton('aw_pq2/question')
                    ->load($id)
                    ->setStatus($status)
                    ->save()
                ;
            }
            if (count($questionIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d question has been updated successfully', count($questionIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d questions have been updated successfully', count($questionIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    public function massDeleteAction()
    {
        $questionIds = $this->getRequest()->getParam('id', null);
        try {
            if (!is_array($questionIds)) {
                throw new Mage_Core_Exception($this->__('Invalid question id(s)'));
            }

            foreach ($questionIds as $id) {
                Mage::getSingleton('aw_pq2/question')
                    ->load($id)
                    ->delete()
                ;
            }
            if (count($questionIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d question has been deleted successfully', count($questionIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d questions have been deleted successfully', count($questionIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    public function askCustomersAction()
    {
        $questionModel = $this->_initQuestion();
        $salesCollection = Mage::helper('aw_pq2')->getCustomerCollectionWhoBoughtProductFewDaysAgo(
            $questionModel->getProduct(), $questionModel->getStoreId()
        );
        try {
            foreach ($salesCollection as $order) {

                //ask customers notification
                Mage::getModel('aw_pq2/notification')->addToQueue(
                    $order->getCustomerName(),
                    $order->getCustomerEmail(),
                    AW_Pquestion2_Model_Source_Notification_Type::ASK_CUSTOMER,
                    array_merge(
                        array(
                            'customer_name' => $order->getCustomerName(),
                            'product_name'  => Mage::helper('aw_pq2')->stripTags(
                                $questionModel->getProduct()->getName()
                            ),
                            'product_url'   => $questionModel->getProduct()->getProductUrl(),
                            'product_answer_please_url' => Mage::helper('aw_pq2/notification')->getAutoLoginUrl(
                                $order->getCustomerEmail(),
                                Mage::helper('aw_pq2/request')->getEmailProductUrl(
                                    $questionModel, $order->getCustomerName(),
                                    $order->getCustomerEmail(),
                                    $order->getCustomerId()
                                ),
                                $questionModel->getStoreId()
                            ),
                            'question_text' => $questionModel->getContent(),
                            'is_registered' => !$order->getCustomerIsGuest(),
                            'is_guest'      => (bool)$order->getCustomerIsGuest(),
                            'question_initiator_name' => $questionModel->getAuthorName(),
                        ), Mage::helper('aw_pq2')->getPointsEmailVariables()
                    ),
                    $questionModel->getStoreId()
                );
            }
            if (count($salesCollection->getItems()) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d customer has been successfully notified', count($salesCollection->getItems()))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d customers have been successfully notified', count($salesCollection->getItems()))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirectReferer();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aw_pq2/all_questions');
    }
}