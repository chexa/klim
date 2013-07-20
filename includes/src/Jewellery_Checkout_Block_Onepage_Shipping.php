<?php

class Jewellery_Checkout_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    public function isShow()
    {
        return false;
    }
}