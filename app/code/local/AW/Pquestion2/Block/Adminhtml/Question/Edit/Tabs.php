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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('aw_pq2_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Question'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'general_tab',
            array(
                'label'   => $this->__('General'),
                'content' => $this->getLayout()
                    ->createBlock('aw_pq2/adminhtml_question_edit_tab_general')
                    ->initForm()
                    ->toHtml(),
                'active'  => true
            )
        );

        $state = false;
        $activeTab = $this->getRequest()->getParam('tab', null);
        $questionModel = Mage::registry('current_question');
        if (null !== $questionModel->getId()) {
            if ($activeTab == 'aw_pq2_info_tabs_answers_tab') {
                $state = true;
            }
            $this->addTab(
                'answers_tab',
                array(
                    'label'   => $this->__('Manage Answers'),
                    'content' => $this->getLayout()
                        ->createBlock('aw_pq2/adminhtml_question_edit_tab_answers')
                        ->initForm()
                        ->toHtml(),
                    'active'  => $state
                )
            );
        }
        $state = false;
        if ($activeTab == 'aw_pq2_info_tabs_sharing_tab') {
            $state = true;
        }
        $this->addTab(
            'sharing_tab',
            array(
                 'label'   => $this->__('Sharing Question'),
                 'content' => $this->getLayout()
                     ->createBlock('aw_pq2/adminhtml_question_edit_tab_sharing')
                     ->initForm()
                     ->toHtml(),
                 'active'  => $state
            )
        );
        return parent::_beforeToHtml();
    }
}