<?php
/**
 * MGT-Commerce GmbH
 * http://www.mgt-commerce.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mgt-commerce.com so we can send you a copy immediately.
 *
 * @category    Mgt
 * @package     Mgt_ReviewReminder
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2012 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mgt_ReviewReminder_ReviewController extends Mage_Core_Controller_Front_Action
{
    public function writeAction()
    {
        $code = trim($this->getRequest()->getParam('co', null));
        $this->loadLayout();
        if ($code) {
            $reminder = Mage::getModel('mgt_review_reminder/reminder')->load($code, 'code');
            if ($reminder->getId()) {
                $items = $reminder->getItems();
                $this->getLayout()->getBlock('item_list')->setItems($items);
            }
        }
        $this->renderLayout();
    }
    
    public function postAction()
    {
        $code = trim($this->getRequest()->getParam('co', null));
        $id = trim($this->getRequest()->getParam('id', null));
        $rating = $this->getRequest()->getParam('ratings', array());
        $data = $this->getRequest()->getPost();
        
        if ($code && $data) {
            $reminder = Mage::getModel('mgt_review_reminder/reminder')->load($code, 'code');
            $item = Mage::getModel('mgt_review_reminder/reminder_item')->load($id);
            $session    = Mage::getSingleton('core/session');
            
            if (!$item->getIsReviewed() && $item->getId() && $reminder->getId()) {
                $review  = Mage::getModel('review/review')->setData($data);
                $validate = $review->validate();
                if ($validate === true) {
                    try {
                        $productId = $item->getProductId();
                        $product = Mage::getModel('catalog/product')->load($productId);
                        
                        if ($product->getId()) {
                            
                            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
                                $configurableProduct = Mage::getModel('catalog/product_type_configurable');
                                $parentIds = $configurableProduct->getParentIdsByChild($product->getId());
                                if (isset($parentIds) && count($parentIds)) {
                                    $productId = array_shift($parentIds);
                                }
                            }
                            
                            $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                              ->setEntityPkValue($productId)
                              ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                              ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                              ->setStoreId(Mage::app()->getStore()->getId())
                              ->setStores(array(Mage::app()->getStore()->getId()))
                              ->save();
    
                            foreach ($rating as $ratingId => $optionId) {
                                Mage::getModel('rating/rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                                ->addOptionVote($optionId, $productId);
                            }
    
                            $review->aggregate();
                        }
  
                        $item->setIsReviewed(1);
                        $item->save();
                        $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                    }
                    catch (Exception $e) {
                        $session->setFormData($data);
                        $session->addError($this->__('Unable to post the review.'));
                    }
                }
            }
        }
        
        $params = array('co' => $code);
        return $this->_redirect('mgt/review/write', $params);
    }
    
    static public function clean(&$value)
    {
        if (is_array($value)) {
            return $value;
        }
        $value = strip_tags(trim($value));
        return $value;
    }
}