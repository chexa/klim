<?php
class Magenmagic_Quickorder_Block_Adminhtml_Quickorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_quickorder';
    $this->_blockGroup = 'quickorder';
    $this->_headerText = Mage::helper('quickorder')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('quickorder')->__('Add Item');
    parent::__construct();
  }
}