<?php

class Magenmagic_PromoBannerSlider_Model_Mysql4_BannersImages extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the promobannerslider_id refers to the key field in your database table.
        $this->_init('promobannerslider/bannersimages', 'bannersimages_id');
    }
}