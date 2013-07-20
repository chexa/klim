<?php
class Magenmagic_PromoBannerSlider_Block_Promobannerslider extends Mage_Core_Block_Template
{

	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getHomeBanners()     
     { 
        if (!$this->hasData('promobannerslider')) {
            $this->setData('promobannerslider', Mage::registry('promobannerslider'));
        }
        return $this->getData('promobannerslider');
        
    }
}