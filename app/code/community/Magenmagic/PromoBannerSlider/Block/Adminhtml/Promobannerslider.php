<?php
class Magenmagic_PromoBannerSlider_Block_Adminhtml_Promobannerslider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_promobannerslider';
    $this->_blockGroup = 'promobannerslider';
    $this->_headerText = Mage::helper('promobannerslider')->__('Categories Manager');
    $this->_addButtonLabel = Mage::helper('promobannerslider')->__('Add Category');
    parent::__construct();
  }
}