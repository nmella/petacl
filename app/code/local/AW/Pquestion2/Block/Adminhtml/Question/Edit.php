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


class AW_Pquestion2_Block_Adminhtml_Question_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'aw_pq2';
        $this->_controller = 'adminhtml_question';
        $this->_formScripts[] = "
            var AnswerForm = new PQ2AjaxForm;
            function saveAndContinueEdit(url) {
               editForm.submit(
                    url.replace(/{{tab_id}}/, aw_pq2_info_tabsJsTabs.activeTab.id)
                );
            }
            document.observe('dom:loaded', function() {
                if ($('aw_pq2_info_tabs_sharing_tab')) {
                    $('aw_pq2_info_tabs_sharing_tab').observe('click', function() {
                        if ($$('textarea[name=\"content\"]')
                            && $$('#_info_sharing label[for=\"_info_content\"]').length > 0
                        ) {
                            var el = $$('#_info_sharing label[for=\"_info_content\"]')[0].up().next('.value');
                            el.innerHTML = $$('textarea[name=\"content\"]')[0].value.stripTags();
                        }
                    });
                }
            });
        ";
        parent::__construct();
    }

    public function getHeaderText()
    {
        $title = $this->__('New Question');
        $questionModel = Mage::registry('current_question');
        if (null !== $questionModel->getId()) {
            $title = $this->__(
                'Edit Question #%s from %s &lt;%s&gt;',
                $questionModel->getId(), $this->escapeHtml($questionModel->getAuthorName()),  $this->escapeHtml($questionModel->getAuthorEmail())
            );
        }
        return $title;
    }

    protected function _prepareLayout()
    {
        $this->_addButton(
            'save_and_continue',
            array(
                'label'   => $this->__('Save and Continue Edit'),
                'onclick' => 'saveAndContinueEdit(\'' . $this->_getSaveAndContinueUrl() . '\')',
                'class'   => 'save'
            ), 10
        );
        $questionModel = Mage::registry('current_question');
        if ($questionModel && null !== $questionModel->getId()) {
            $_customersWhoBoughtProduct = Mage::helper('aw_pq2')->getCustomerEmailListWhoBoughtProductFewDaysAgo(
                $questionModel->getProductId(), $questionModel->getStoreId()
            );
            $this->_addButton(
                'ask_customers',
                array(
                     'label'   => $this->__(
                         'Ask Customers(%s)',
                         count($_customersWhoBoughtProduct)
                     ),
                     'onclick' => 'setLocation(\'' . $this->getUrl(
                         '*/*/askcustomers',
                         array('id' => $questionModel->getId())
                     ) . '\')',
                ),
                0
            );
        }
        parent::_prepareLayout();
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            array(
                '_current' => true,
                'back'     => 'edit',
                'tab'      => '{{tab_id}}'
            )
        );
    }
}