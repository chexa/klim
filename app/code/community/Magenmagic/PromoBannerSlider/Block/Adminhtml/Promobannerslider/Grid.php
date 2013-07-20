<?php

class Magenmagic_PromoBannerSlider_Block_Adminhtml_Promobannerslider_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('promobannersliderGrid');
      $this->setDefaultSort('promobannerslider_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('promobannerslider/promobannerslider')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('promobannerslider_id', array(
          'header'    => Mage::helper('promobannerslider')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'promobannerslider_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('promobannerslider')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

	  /*
      $this->addColumn('content', array(
			'header'    => Mage::helper('promobannerslider')->__('Item Content'),
			'width'     => '150px',
			'index'     => 'content',
      ));
	  */
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('promobannerslider')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('promobannerslider')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('promobannerslider')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('promobannerslider')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('promobannerslider_id');
        $this->getMassactionBlock()->setFormFieldName('promobannerslider');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('promobannerslider')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('promobannerslider')->__('Are you sure?')
        ));


        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}