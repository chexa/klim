<?php
abstract class Jewellery_Shipping_Model_Config_Source_Abstract
{
    abstract public function toOptionArray();

    public function toOptions()
    {
        $array = $this->toOptionArray();

        $options = array();

        foreach ($array as $option) {
            $options[$option['value']] = $option['label'];
        }

        return $options;
    }
}