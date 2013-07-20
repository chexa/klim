<?php
class Magenmagic_Quickorder_Block_Quickorder extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
	

     public function getQuickorder()     
     { 
        if (!$this->hasData('quickorder')) {
            $this->setData('quickorder', Mage::registry('quickorder'));
        }
        return $this->getData('quickorder');
        
    }
}