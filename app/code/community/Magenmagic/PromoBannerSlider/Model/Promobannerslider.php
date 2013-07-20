<?php

class Magenmagic_PromoBannerSlider_Model_Promobannerslider extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('promobannerslider/promobannerslider');
    }

    public function toOptionArray ()
    {

        $collection = $this->getCollection()->setOrder("title", "DESC");

        $arrayToReturn = array();

        foreach ( $collection as $item )
        {
            $arrayToReturn[] = array(
                "value" => $item->getId(),
                "label" => $item->getTitle()
            );
        }

        return $arrayToReturn;
    }

}