<?php

class Jewellery_Shipping_Model_Rewrite_ShippingRateResult extends Mage_Shipping_Model_Rate_Result
{
    public function sortRatesByPrice ()
    {
        // disable sorting by price
        return $this;
    }
}