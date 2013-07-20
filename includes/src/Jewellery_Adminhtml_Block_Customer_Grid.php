<?php

class Jewellery_Adminhtml_Block_Customer_Grid extends Netzarbeiter_CustomerActivation_Block_Adminhtml_Customer_Grid //Mage_Adminhtml_Block_Customer_Grid
{
    public function __construct()
    {
        parent::__construct();
        
        $this->setDefaultSort('customer_number');
    }

    public function setCollection($collection)
    {
        $collection->addAttributeToSelect('customer_number');

        return parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $this->addColumnAfter('customer_number', array(
            'header'    => Mage::helper('jewellery')->__('Customer Number'),
            'width'     => '50px',
            'index'     => 'customer_number',
            'type'  => 'text',
        ), 'entity_id');

        return parent::_prepareColumns();
    }

}
