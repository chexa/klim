<?php

class Jewellery_Checkout_Block_Onepage_Payment extends Mage_Checkout_Block_Onepage_Payment
{
    public function isShow()
    {
        return false;
    }
}