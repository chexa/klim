<?php

class Magenmagic_PromoBannerSlider_Model_Links extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('promobannerslider/links');
    }

    public function checkExists ($collection_id, $val)
    {
        $exist =$this->getCollection()->addFieldToFilter("gallery_id", $collection_id)->addFieldToFilter("photo_id", $val);
        return $exist->count() > 0;
    }

}