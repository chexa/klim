<?php

class Magenmagic_PromoBannerSlider_Model_Mysql4_BannersImages_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('promobannerslider/bannersimages');
    }


}