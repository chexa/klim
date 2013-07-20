<?php

class Magenmagic_PromoBannerSlider_Block_Adminhtml_Promobannerslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('promobannerslider_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('promobannerslider')->__('Item Information'));

  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('promobannerslider')->__('Item Information'),
          'title'     => Mage::helper('promobannerslider')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_edit_tab_form')->toHtml(),
      ));

      //get images from controller
      $images            = $this->getData("images");

      $imagesBlock = $this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_edit_tab_images');
      $imagesBlock->setData("images", $images);

      $action = Mage::app()->getRequest()->getActionName();

      if ( $action != "new" )
      {
          $this->addTab('image_section', array(
                'label'     => Mage::helper('promobannerslider')->__('Images'),
                'title'     => Mage::helper('promobannerslider')->__('Images'),
                'content'   => $imagesBlock->toHtml(),
            ));
      }

      return parent::_beforeToHtml();
  }
}