<?php
class Jewellery_Shipping_Block_Adminhtml_Method_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'jeshipping';
        $this->_controller = 'adminhtml_method';

        $this->_removeButton('delete');
    }

    public function getHeaderText()
    {
        return Mage::helper('jeshipping')->__('Deutsche Post Shipping Method');
    }
}