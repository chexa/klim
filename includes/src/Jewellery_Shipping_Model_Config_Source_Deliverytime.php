<?php
class Jewellery_Shipping_Model_Config_Source_Deliverytime extends Jewellery_Shipping_Model_Config_Source_Abstract
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'oneday',  'label'=>Mage::helper('jeshipping')->__('Approx. 24 h.')),
            array('value' => 'twodays', 'label'=>Mage::helper('jeshipping')->__('Approx. 48 h.')),
            array('value' => 'to9am',   'label'=>Mage::helper('jeshipping')->__('To 9.00 AM')),
            array('value' => 'to10am',  'label'=>Mage::helper('jeshipping')->__('To 10.00 AM')),
            array('value' => 'to12am',  'label'=>Mage::helper('jeshipping')->__('To 12.00 AM')),
        );
    }



}