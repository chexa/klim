<?php
class Jewellery_Shipping_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCarrierDescription($carrierCode) {
        if ($description = Mage::getStoreConfig('carriers/'.$carrierCode.'/description')) {
            return $description;
        }
        return '';
    }
}
