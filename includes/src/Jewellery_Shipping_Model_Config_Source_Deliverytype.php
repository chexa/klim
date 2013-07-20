<?php
class Jewellery_Shipping_Model_Config_Source_Deliverytype extends Jewellery_Shipping_Model_Config_Source_Abstract
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'simple',  'label'=>Mage::helper('jeshipping')->__('Simple')),
            array('value' => 'express', 'label'=>Mage::helper('jeshipping')->__('Express')),
            array('value' => 'international', 'label'=>Mage::helper('jeshipping')->__('International')),
         );
    }

}