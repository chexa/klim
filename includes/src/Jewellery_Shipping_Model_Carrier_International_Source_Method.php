<?php
class Jewellery_Shipping_Model_Carrier_International_Source_Method
{
    public function toOptionArray()
    {
        $method = Mage::getSingleton('jeshipping/carrier_international');
        $arr = array();
        foreach ($method->getCode('international') as $k=>$v) {
            $arr[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        return $arr;
    }
}
