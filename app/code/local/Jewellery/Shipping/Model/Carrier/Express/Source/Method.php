<?php
class Jewellery_Shipping_Model_Carrier_Express_Source_Method
{
    public function toOptionArray()
    {
        $method = Mage::getSingleton('jeshipping/carrier_express');
        $arr = array();
        foreach ($method->getCode('express') as $k=>$v) {
            $arr[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        return $arr;
    }
}
