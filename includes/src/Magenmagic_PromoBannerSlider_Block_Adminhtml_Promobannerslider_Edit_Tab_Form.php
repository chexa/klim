<?php

class Magenmagic_PromoBannerSlider_Block_Adminhtml_Promobannerslider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('promobannerslider_form', array('legend'=>Mage::helper('promobannerslider')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('promobannerslider')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));


      if ( Mage::getSingleton('adminhtml/session')->getHomeBannersData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getHomeBannersData());
          Mage::getSingleton('adminhtml/session')->setHomeBannersData(null);
      } elseif ( Mage::registry('promobannerslider_data') ) {
          $form->setValues(Mage::registry('promobannerslider_data')->getData());
      }
      return parent::_prepareForm();
  }
}