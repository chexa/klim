<?php

class Magenmagic_Quickorder_Block_Adminhtml_Quickorder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('quickorder_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('quickorder')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('quickorder')->__('Item Information'),
          'title'     => Mage::helper('quickorder')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('quickorder/adminhtml_quickorder_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}