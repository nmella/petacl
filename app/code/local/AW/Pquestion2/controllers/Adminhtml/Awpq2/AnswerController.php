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


class AW_Pquestion2_Adminhtml_Awpq2_AnswerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/aw_pq2')
        ;
        $this
            ->_title($this->__('Catalog'))
            ->_title($this->__('Product Questions'))
            ->_title($this->__('Edit Question'))
        ;
        return $this;
    }

    protected function _initAnswer()
    {
        $answerModel = Mage::getModel('aw_pq2/answer');
        $answerId  = (int) $this->getRequest()->getParam('id', 0);
        $customerId  = (int) $this->getRequest()->getParam('customer_id', 0);
        $customerGroup  = (int) $this->getRequest()->getParam('customer_group', null);
        if ($answerId) {
            try {
                $answerModel->load($answerId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            if ($customerGroup == AW_Pquestion2_Model_Source_Answer_CustomerGroup::ADMIN_VALUE) {
                $customerModel = Mage::getModel('customer/customer');
                $customerModel
                    ->setFirstname(Mage::getSingleton('admin/session')->getUser()->getFirstname())
                    ->setLastname(Mage::getSingleton('admin/session')->getUser()->getLastname())
                    ->setEmail(Mage::getSingleton('admin/session')->getUser()->getEmail())
                ;
                $answerModel->setIsAdmin(true);
            } else {
                $customerModel = Mage::getModel('customer/customer')->load($customerId);
            }
            $currentDate = new Zend_Date;
            $answerModel
                ->setAuthorName(
                    trim($customerModel->getFirstname() . ' ' . $customerModel->getLastname())
                )
                ->setStatus(AW_Pquestion2_Model_Source_Question_Status::APPROVED_VALUE)
                ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
                ->setAuthorEmail($customerModel->getEmail())
                ->setCustomerId($customerModel->getId())
                ->setHelpfulness(0)
            ;
        }
        if (null !== Mage::getSingleton('adminhtml/session')->getPQAnswerFormData()
            && is_array(Mage::getSingleton('adminhtml/session')->getPQAnswerFormData())
        ) {
            $answerModel->addData(Mage::getSingleton('adminhtml/session')->getPQAnswerFormData());
            Mage::getSingleton('adminhtml/session')->setPQAnswerFormData(null);
        }
        Mage::register('current_answer', $answerModel, true);
        return $answerModel;
    }

    public function editAction()
    {
        $answer = $this->_initAnswer();
        $this->_initAction();
        $this->_title($this->__('Manage Question'));

        $breadcrumbTitle = $breadcrumbLabel = $this->__('New Answer');
        if ($answer->getId()) {
            $breadcrumbTitle = $breadcrumbLabel = $this->__('Edit Answer');
        }
        $this
            ->_title($breadcrumbTitle)
            ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
            ->renderLayout()
        ;
    }

    public function newAction()
    {
        $customerId = $this->getRequest()->getParam('customer_id', null);
        $customerGroup = $this->getRequest()->getPost('customer_group', null);
        if (null === $customerId
            && (
                null === $customerGroup
                || $customerGroup == AW_Pquestion2_Model_Source_Answer_CustomerGroup::CUSTOMER_VALUE
            )
        ) {
            $this->_initAction();
            $this->_title($this->__('New Answer'));
            $this->renderLayout();
            return $this;
        }
        $this->_forward(
            'edit', null, null,
            array(
                'customer_group' => $this->getRequest()->getPost('customer_group', null),
                'customer_id' => $this->getRequest()->getParam('customer_id', null),
                'question_id' => $this->getRequest()->getParam('question_id', null)
            )
        );
        return $this;
    }

    public function saveAction()
    {
        if ($formData = array_filter($this->getRequest()->getPost())) {
            $answerModel = $this->_initAnswer();
            $questionId = $this->getRequest()->getParam('question_id', null);
            $questionModel = Mage::getModel('aw_pq2/question')->load($questionId);
            $format = Mage::app()->getLocale()->getDateTimeFormat(
                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
            );
            if (!array_key_exists('created_at', $formData)) {
                $formData['created_at'] = null;
            }
            $date = Mage::app()->getLocale()->date($formData['created_at'], $format);
            $time = $date->getTimestamp();
            $formData['created_at'] = Mage::getModel('core/date')->gmtDate(null, $time);
            try {
                $answerModel->addData($formData);
                if (null === $answerModel->getId()) {
                    $questionModel->addAnswer($answerModel);
                } else {
                    $answerModel->save();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Answer saved successfully'));
                Mage::getSingleton('adminhtml/session')->setPQAnswerFormData(null);
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPQAnswerFormData($formData);
                $this->_redirect(
                    '*/*/edit',
                    array(
                        'id' => $answerModel->getId(),
                    )
                );
                return;
            }
        }
        $this->_redirect('*/*/new');
    }

    public function customergridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massStatusAction()
    {
        $answersIds = $this->getRequest()->getParam('answer_id', null);
        $status = $this->getRequest()->getParam('answer_status', null);
        try {
            if (!is_array($answersIds)) {
                throw new Mage_Core_Exception($this->__('Invalid answer ids'));
            }

            if (null === $status) {
                throw new Mage_Core_Exception($this->__('Invalid status value'));
            }
            foreach ($answersIds as $id) {
                Mage::getSingleton('aw_pq2/answer')
                    ->load($id)
                    ->setStatus($status)
                    ->save()
                ;
            }
            if (count($answersIds) == 1) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d answer has been updated successfully', count($answersIds))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('%d answers have been updated successfully', count($answersIds))
                );
            }

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        return $this;
    }

    public function deleteAction()
    {
        $answerModel = $this->_initAnswer();
        try {
            $answerModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Answer has been deleted successfully')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        return $this;
    }

    public function changeStatusAction()
    {
        $answerModel = $this->_initAnswer();
        $status = $this->getRequest()->getParam('status', null);
        if (null === $status) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Invalid status value')
            );
            return $this;
        }
        try {
            $answerModel->setStatus($status)->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(
                $this->__('Answer status has been changed successfully')
            );
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/aw_pq2/all_questions');
    }
}