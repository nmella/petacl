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


class AW_Pquestion2_Block_Adminhtml_Question_Edit_Tab_Answers_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('answersGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $questionModel = Mage::registry('current_question');
        $this->setCollection($questionModel->getAnswerCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'answer_author_name',
            array(
                'header'   => $this->__('Author Name'),
                'index'    => 'author_name',
                'renderer' => 'aw_pq2/adminhtml_question_edit_tab_answers_grid_renderer_name'
            )
        );
        $this->addColumn(
            'answer_content',
            array(
                'header'   => $this->__('Answer Text'),
                'index'    => 'content',
                'renderer' => 'aw_pq2/adminhtml_question_edit_tab_answers_grid_renderer_content'
            )
        );
        $this->addColumn(
            'answer_created_at',
            array(
                'header' => Mage::helper('sales')->__('Created At'),
                'index'  => 'created_at',
                'type'   => 'datetime',
                'width'  => '100px',
                'time'   => true
            )
        );
        $this->addColumn(
            'answer_helpfulness',
            array(
                'header' => $this->__('Helpfulness'),
                'width'  => '50px',
                'type'   => 'number',
                'index'  => 'helpfulness',
            )
        );
        $this->addColumn(
            'answer_status',
            array(
                'header'   => $this->__('Status'),
                'width'    => '60px',
                'index'    => 'status',
                'type'     => 'options',
                'options'  => Mage::getSingleton('aw_pq2/source_question_status')->toOptionArray(),
                'renderer' => 'aw_pq2/adminhtml_question_edit_tab_answers_grid_renderer_status'
            )
        );
        $this->addColumn(
            'answer_is_admin',
            array(
                'header'  => $this->__('Is Admin'),
                'width'   => '60px',
                'index'   => 'is_admin',
                'type'    => 'options',
                'options' => array(
                    0 => $this->__('No'),
                    1 => $this->__('Yes'),
                ),
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'   => $this->__('Action'),
                'index'    => 'action',
                'sorted'   => false,
                'filter'   => false,
                'no_link'  => true,
                'width'	   => '170px',
                'renderer' => 'aw_pq2/adminhtml_question_edit_tab_answers_grid_renderer_action'
            )
        );
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/answersGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return '';
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()
            ->setUseAjax(true)
            ->setFormFieldName('answer_id')
        ;
        $statuses = Mage::getSingleton('aw_pq2/source_question_status')->toOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'=> $this->__('Change status'),
                'url'  => $this->getUrl('*/awpq2_answer/massStatus', array('_current' => true)),
                'complete' => 'this.grid.doFilter()',
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'answer_status',
                        'type'   => 'select',
                        'label'  => $this->__('Status'),
                        'values' => $statuses
                    )
                )
            )
        );
        return $this;
    }

    protected function _prepareLayout()
    {
        $_result = parent::_prepareLayout();
        $_buttonData = array(
            'label'   => $this->__('Add New'),
            'onclick' => 'AnswerForm.open(\''
                . $this->getUrl(
                    '*/awpq2_answer/new',
                    array('question_id' =>  Mage::app()->getRequest()->getParam('id'))
                ) . '\',\'\',\'' . Mage::helper('aw_pq2')->escapeHtml($this->__('New Answer')) . '\', \'\', 1100)',
            'class'      => 'add-new-answer',
            'after_html' => '<div style="clear:both;"></div>'
        );
        $_buttonBlock =  $this->getLayout()->createBlock('adminhtml/widget_button')->setData($_buttonData);
        $this->setChild('add_answer_button', $_buttonBlock);
        return $_result;
    }

    public function getResetFilterButtonHtml()
    {
        return $this->getChildHtml('add_answer_button') . $this->getChildHtml('reset_filter_button');
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->getAfterHtml();
    }

    public function getAfterHtml()
    {
        if (Mage::helper('aw_pq2')->checkMageVersion()) {
            $script = "
                var container = $(answersGrid_massactionJsObject.containerId);
                var formHiddens = $(answersGrid_massactionJsObject.containerId + '-form-hiddens');
                var formAdditional = $(answersGrid_massactionJsObject.containerId + '-form-additional');

                var form = $(answersGrid_massactionJsObject.containerId + '-form'), formPlace = null,
                    formElement = formHiddens || formAdditional;

                if (!formElement) {
                    formElement = container.getElementsByTagName('button')[0];
                }
                if (!form && formElement) {
                    /* fix problem with rendering form in FF through innerHTML property */
                    form = document.createElement('form');
                    form.setAttribute('method', 'post');
                    form.setAttribute('action', '');
                    form.id = answersGrid_massactionJsObject.containerId + '-form';
                    formPlace = formElement.parentNode.parentNode;
                    formPlace.parentNode.appendChild(form);
                    form.appendChild(formPlace);
                }

                answersGrid_massactionJsObject.form = form;
            ";
            return "<script type='text/javascript'>" . $script . "</script>";
        } else {
            return '';
        }
    }
}