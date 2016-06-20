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


class AW_Pquestion2_Block_Adminhtml_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('pq2QuestionGrid');
        $this->setDefaultSort('entity_id', 'desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection
            ->joinPendingAnswerCount()
            ->joinTotalAnswerCount()
            ->joinProductTitle()
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => $this->__('ID'),
                'index'  => 'main_table.entity_id',
                'type'   => 'number',
                'width'  => 10
            )
        );
        $this->addColumn(
            'author_name',
            array(
                 'header' => $this->__('Author Name'),
                 'index'  => 'author_name',
                 'type'   => 'text',
                 'width'  => 250,
                 'escape' => true
            )
        );
        $this->addColumn(
            'author_email',
            array(
                 'header' => $this->__('Author Email'),
                 'index'  => 'author_email',
                 'type'   => 'text',
                 'width'  => 250
            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header' => $this->__('Created At'),
                'width'  => 120,
                'type'   => 'datetime',
                'index'  => 'created_at',
                'time'   => true
            )
        );
        $this->addColumn(
            'content',
            array(
                'header' => $this->__('Question text'),
                'width'  => 250,
                'type'   => 'text',
                'index'  => 'content',
                'renderer' => 'aw_pq2/adminhtml_question_grid_renderer_content'
            )
        );
        $this->addColumn(
            'question_product_name',
            array(
                'index'        => 'question_product_name',
                'filter_index' => 'cpev.value',
                'header'       => $this->__('Product title'),
                'width'        => '250px',
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                    'type'            => 'store',
                    'header'          => $this->__('Asked from'),
                    'align'           => 'left',
                    'width'           => '100px',
                    'index'           => 'main_table.store_id',
                    'renderer'        => 'aw_pq2/adminhtml_question_grid_renderer_store',
                    'store_view'      => true,
                    'display_deleted' => true,
                )
            );
            $this->addColumn(
                'show_in_store_ids',
                array(
                    'type'                      => 'store',
                    'header'                    => $this->__('Show in stores'),
                    'width'                     => '100px',
                    'index'                     => 'main_table.show_in_store_ids',
                    'sortable'                  => false,
                    'renderer'                  => 'aw_pq2/adminhtml_question_grid_renderer_multiStores',
                    'filter_condition_callback' => array($this, 'filterStore'),
                )
            );
        }
        $this->addColumn(
            'status',
            array(
                'header'  => $this->__('Status'),
                'width'   => 100,
                'align'   => 'center',
                'index'   => 'status',
                'type'    => 'options',
                'options' => Mage::getModel('aw_pq2/source_question_status')->toOptionArray(),
            )
        );
        $this->addColumn(
            'visibility',
            array(
                 'header'  => $this->__('Visibility'),
                 'width'   => 100,
                 'align'   => 'center',
                 'index'   => 'visibility',
                 'type'    => 'options',
                 'options' => Mage::getModel('aw_pq2/source_question_visibility')->toOptionArray(),
            )
        );
        $this->addColumn(
            'sharing_type',
            array(
                'header'  => $this->__('Sharing Type'),
                'width'   => 100,
                'align'   => 'center',
                'index'   => 'sharing_type',
                'type'    => 'options',
                'options' => Mage::getModel('aw_pq2/source_question_sharing_type')->toOptionArray(),
            )
        );
        $this->addColumn(
            'total_answers',
            array(
                'header'       => $this->__('Total answers'),
                'width'        => 80,
                'align'        => 'center',
                'index'        => 'total_answers',
                'filter_index' => 't_2.total_answers',
                'type'         => 'number',
                'filter_condition_callback' => array($this, 'filterTotalAnswers'),
            )
        );
        $this->addColumn(
            'pending_answers',
            array(
                'header'       => $this->__('Pending answers'),
                'width'        => 80,
                'align'        => 'center',
                'index'        => 'pending_answers',
                'filter_index' => 't.pending_answers',
                'type'         => 'number',
                'filter_condition_callback' => array($this, 'filterPendingAnswers'),
            )
        );
        $this->addColumn('action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array(
                            'base' => '*/*/edit'
                        ),
                        'field' => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            )
        );
        $this->addExportType('*/*/exportCsv', $this->__('CSV'));
        $this->addExportType('*/*/exportXml', $this->__('XML'));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getEntityId()));
    }

    protected function filterStore($collection, $column)
    {
        $collection->addShowInStoresFilter($column->getFilter()->getValue());
        return $this;
    }

    protected function filterPendingAnswers($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $from = null;
        if (array_key_exists('from', $value)) {
            $from = $value['from'];
        }
        $to = null;
        if (array_key_exists('to', $value)) {
            $to = $value['to'];
        }
        $collection->addPendingAnswerFilter($from, $to);
        return $this;
    }

    protected function filterTotalAnswers($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $from = null;
        if (array_key_exists('from', $value)) {
            $from = $value['from'];
        }
        $to = null;
        if (array_key_exists('to', $value)) {
            $to = $value['to'];
        }
        $collection->addTotalAnswerFilter($from, $to);
        return $this;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->setMassactionIdFilter('main_table.entity_id');
        $this->getMassactionBlock()->setFormFieldName('id');
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label' => $this->__('Change status'),
                'url'   => $this->getUrl('*/*/massStatus', array('_current' => true)),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Status'),
                        'values' => Mage::getSingleton('aw_pq2/source_question_status')->toOptionArray()
                    )
                )
            )
        );
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'   => $this->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => $this->__('Are you sure?')
            )
        );
    }
}