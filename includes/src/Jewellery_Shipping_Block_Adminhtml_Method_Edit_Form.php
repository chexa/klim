<?php

class Jewellery_Shipping_Block_Adminhtml_Method_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
        //create form structure
        $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
          'method' => 'post')
         );
        
        $form->setUseContainer(true);
        $this->setForm($form);
        $hlp = Mage::helper('jeshipping');
        
        $fldInfo = $form->addFieldset('jeshipping', array('legend'=> $hlp->__('Method Data')));

        $fldInfo->addField('name', 'text', array(
          'label'     => $hlp->__('Method Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
         ));

      $deliveryType = new Jewellery_Shipping_Model_Config_Source_Deliverytype();
         
        $fldInfo->addField('delivery_type', 'select', array(
          'label'     => $hlp->__('Delivery Type'),
          'name'      => 'delivery_type',
          'required'  => true,
          'values'    => $deliveryType->toOptionArray()
        ));
        
        $fldInfo->addField('price', 'text', array(
          'label'     => $hlp->__('Price'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'price',
        ));

        $fldInfo->addField('ensurance', 'text', array(
          'label'     => $hlp->__('Ensurance'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'ensurance',
        ));

      $deliveryTime = new Jewellery_Shipping_Model_Config_Source_Deliverytime();

        $fldInfo->addField('delivery_time', 'select', array(
          'label'     => $hlp->__('Delivery Time'),
          'name'      => 'delivery_time',
          'required'  => true,
          'values'    => $deliveryTime->toOptionArray()
        ));

      $fldInfo->addField('display_order', 'text', array(
          'label'     => $hlp->__('Display Order'),
          'name'      => 'display_order',
        ));

        //set form values
        $data = Mage::getSingleton('adminhtml/session')->getFormData();
        $model = Mage::registry('jeshipping_method');
        if ($data) {
            $form->setValues($data);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }
        elseif ($model) {
            $form->setValues($model->getData());
        }

        return parent::_prepareForm();
  }

}