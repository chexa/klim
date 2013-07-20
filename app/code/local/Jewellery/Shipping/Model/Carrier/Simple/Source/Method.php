<?php
class Jewellery_Shipping_Model_Carrier_Simple_Source_Method
{
    public function toOptionArray()
    {
        $method = Mage::getSingleton('jeshipping/carrier_simple');
        $arr = array();
        foreach ($method->getCode('simple') as $k=>$v) {
            $arr[] = array(
                'value' => $k,
                'label' => $v
            );
        }
        return $arr;
    }
}
