<?php

class Jewellery_Jewellery_Block_Customer_Breadcrumb extends Mage_Directory_Block_Data
{

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCurrentStep()
    {
        $jSession = Mage::getSingleton('jewellery/session');
        return $jSession->getRegistrationStep() ? $jSession->getRegistrationStep() : 0;
    }
}
