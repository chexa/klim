<?php

class Magenmagic_PromoBannerSlider_Block_Adminhtml_Promobannerslider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'promobannerslider';
        $this->_controller = 'adminhtml_promobannerslider';
        
        $this->_updateButton('save', 'label', Mage::helper('promobannerslider')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('promobannerslider')->__('Delete Item'));
		
      


    }

    public function getHeaderText()
    {
        if( Mage::registry('promobannerslider_data') && Mage::registry('promobannerslider_data')->getId() ) {
            return Mage::helper('promobannerslider')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('promobannerslider_data')->getTitle()));
        } else {
            return Mage::helper('promobannerslider')->__('Add Item');
        }
    }
}