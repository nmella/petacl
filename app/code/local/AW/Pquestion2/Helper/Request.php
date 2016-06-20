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


class AW_Pquestion2_Helper_Request extends Mage_Core_Helper_Abstract
{
    const URL_PARAM_KEY = 'aw_pq2_answer';

    public function getPopupData()
    {
        if (!$requestParamValue = $this->getParamValue()) {
            return array();
        }
        return $this->decode($requestParamValue);
    }

    public function generateUrlParam($questionId, $customerName, $customerEmail, $customerId = null)
    {
        $params = array(
            'question_id'    => $questionId,
            'customer_name'  => $customerName,
            'customer_email' => $customerEmail,
        );
        if ($customerId !== null) {
            $params['customer_id'] = $customerId;
        }
        return $this->encode($params);
    }

    public function encode($data)
    {
        return base64_encode(Mage::helper('core')->encrypt(Mage::helper('core')->jsonEncode($data)));
    }

    public function decode($data)
    {
        return Mage::helper('core')->jsonDecode(Mage::helper('core')->decrypt(base64_decode($data)));
    }

    public function getParamValue()
    {
        return Mage::app()->getRequest()->getParam(self::URL_PARAM_KEY, false);
    }


    public function getEmailProductUrl($question, $customerName, $customerEmail, $customerId = null)
    {
        $_key = Mage::helper('aw_pq2/request')->generateUrlParam(
            $question->getId(), $customerName, $customerEmail, $customerId
        );
        $_productUrl = $question->getProduct()->getProductUrl();
        if (strpos($_productUrl, '?')) {
            $_productUrl .= '&' . AW_Pquestion2_Helper_Request::URL_PARAM_KEY . '=' . $_key;
        } else {
            $_productUrl .= '?' . AW_Pquestion2_Helper_Request::URL_PARAM_KEY . '=' . $_key;
        }
        return $_productUrl;
    }

    public function getRewriteProductId()
    {
        $pathInfo = trim(Mage::app()->getRequest()->getOriginalPathInfo(), '/');
        if (class_exists('Enterprise_UrlRewrite_Model_Url_Rewrite_Request')) {
            $requestModel = Mage::getModel('enterprise_urlrewrite/url_rewrite_request');
            if (method_exists($requestModel, 'getSystemPaths')) {
                $paths = $requestModel->getSystemPaths($pathInfo);
            } else {
                $paths = $this->_getEnterpriseRewriteSystemPaths($pathInfo);
            }
            $rewriteModel = Mage::getModel('enterprise_urlrewrite/url_rewrite')
                ->setStoreId(Mage::app()->getStore()->getId())
            ;
            $rewriteModel->loadByRequestPath($paths);
            if ($rewriteModel->getUrlRewriteId()) {
                $productId = $this->_getEnterpriseProductIdByRewrite(
                    $rewriteModel->getUrlRewriteId(),
                    Mage::app()->getStore()->getId()
                );
                if ($productId) {
                    return $productId;
                }
            }
        } else {
            $rewriteModel = Mage::getModel('core/url_rewrite')->setStoreId(Mage::app()->getStore()->getId());
            $rewriteModel->loadByRequestPath($pathInfo);
            if ($rewriteModel->getProductId()) {
                return $rewriteModel->getProductId();
            }
        }
        return -1;
    }

    protected function _getEnterpriseRewriteSystemPaths($pathInfo)
    {
        $systemPath = explode('/', $pathInfo);
        $suffixPart = array_pop($systemPath);
        if (false !== strrpos($suffixPart, '.')) {
            $suffixPart = substr($suffixPart, 0, strrpos($suffixPart, '.'));
        }
        $systemPath[] = $suffixPart;
        return $systemPath;
    }

    protected function _getEnterpriseProductIdByRewrite($rewriteId, $storeId)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $select = $connection->select()
            ->from(
                array('prw' => $resource->getTableName('enterprise_catalog/product')),
                array('prw.product_id')
            )
            ->where('prw.url_rewrite_id = ?', $rewriteId)
            ->where('prw.store_id = 0 OR prw.store_id = ?', $storeId);

        return $connection->fetchOne($select);
    }
}