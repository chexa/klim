<?php
class Jewellery_Shipping_Block_Adminhtml_Method extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_method';
        $this->_headerText = Mage::helper('jeshipping')->__('Manage Deutsche Post Shipping Methods');
        $this->_addButtonLabel = Mage::helper('jeshipping')->__('Add New Shipping Method');
        $this->_blockGroup = 'jeshipping';
    }
}