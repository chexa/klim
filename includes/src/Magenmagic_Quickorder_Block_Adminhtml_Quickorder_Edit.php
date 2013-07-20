<?php

class Magenmagic_Quickorder_Block_Adminhtml_Quickorder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'quickorder';
        $this->_controller = 'adminhtml_quickorder';
        
        $this->_updateButton('save', 'label', Mage::helper('quickorder')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('quickorder')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('quickorder_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'quickorder_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'quickorder_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('quickorder_data') && Mage::registry('quickorder_data')->getId() ) {
            return Mage::helper('quickorder')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('quickorder_data')->getTitle()));
        } else {
            return Mage::helper('quickorder')->__('Add Item');
        }
    }
}