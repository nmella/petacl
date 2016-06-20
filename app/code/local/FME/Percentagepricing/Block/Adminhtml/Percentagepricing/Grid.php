<?php

class FME_Percentagepricing_Block_Adminhtml_Percentagepricing_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('percentagepricingGrid');
      $this->setDefaultSort('percentagepricing_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('percentagepricing_id', array(
          'header'    => Mage::helper('percentagepricing')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'percentagepricing_id',
      ));

      $this->addColumn('name', array(
          'header'    => Mage::helper('percentagepricing')->__('Name Of Rule'),
          'align'     =>'left',
          'index'     => 'name',
      ));
      
      $this->addColumn('priorty', array(
          'header'    => Mage::helper('percentagepricing')->__('Priorty'),
          'align'     =>'left',
          'index'     => 'priorty',
      ));
	  $this->addColumn('apply', array(
          'header'    => Mage::helper('percentagepricing')->__('Action By'),
          'align'     => 'left',
          'width'     => '180px',
          'index'     => 'apply',
          'type'      => 'options',
          'options'   => array(
			  0 => '',
              1 => 'Percentage',
              2 => 'Fixed',
          ),
      ));
	  $this->addColumn('amount', array(
          'header'    => Mage::helper('percentagepricing')->__('Amount'),
          'align'     =>'left',
          'index'     => 'amount',
      ));
	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('percentagepricing')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */

      $this->addColumn('status', array(
          'header'    => Mage::helper('percentagepricing')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('percentagepricing')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('percentagepricing')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('percentagepricing')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('percentagepricing')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('percentagepricing_id');
        $this->getMassactionBlock()->setFormFieldName('percentagepricing');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('percentagepricing')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('percentagepricing')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('percentagepricing/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('percentagepricing')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('percentagepricing')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
