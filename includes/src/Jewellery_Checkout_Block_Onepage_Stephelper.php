<?php

class Jewellery_Checkout_Block_Onepage_Stephelper extends Mage_Core_Block_Template
{
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('checkout/onepage/payment/info.phtml');
    }


}