<?php

class Magenmagic_Quickorder_Block_Adminhtml_Quickorder_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('quickorder_form', array('legend'=>Mage::helper('quickorder')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('quickorder')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('quickorder')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('quickorder')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('quickorder')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('quickorder')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('quickorder')->__('Content'),
          'title'     => Mage::helper('quickorder')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getQuickorderData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getQuickorderData());
          Mage::getSingleton('adminhtml/session')->setQuickorderData(null);
      } elseif ( Mage::registry('quickorder_data') ) {
          $form->setValues(Mage::registry('quickorder_data')->getData());
      }
      return parent::_prepareForm();
  }
}