<?php
class Jewellery_Shipping_Block_Adminhtml_Method_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('methodsGrid');
        $this->setDefaultSort('delivery_type');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('jeshipping/method')
            ->getResourceCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('jeshipping');

        $this->addColumn('id', array(
            'header'    => $hlp->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('name', array(
             'header'    => $hlp->__('Method Name'),
             'align'     =>'left',
             'index'     => 'name',
         ));

        $deliveryType = new Jewellery_Shipping_Model_Config_Source_Deliverytype();

        $this->addColumn('delivery_type', array(
            'header'    => $hlp->__('Delivery Type'),
            'index'     => 'delivery_type',
            'type' => 'options',
            'options' => $deliveryType->toOptions()
        ));

        $this->addColumn('price', array(
            'header'    => $hlp->__('Price'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'price',
        ));

        $this->addColumn('ensurance', array(
            'header'    => $hlp->__('Ensurance'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'ensurance',
        ));

        $deliveryTime = new Jewellery_Shipping_Model_Config_Source_Deliverytime();

        $this->addColumn('delivery_time', array(
            'header'    => $hlp->__('Delivery Time'),
            'index'     => 'delivery_time',
            'type' => 'options',
            'options' => $deliveryTime->toOptions()
        ));
 
        $this->addColumn('display_order', array(
            'header'    => $hlp->__('Display Order'),
            'index'     => 'display_order',
            'width'     => '50px',
            'align'     =>'right',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}