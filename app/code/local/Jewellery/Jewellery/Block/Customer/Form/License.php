<?php

class Jewellery_Jewellery_Block_Customer_Form_License extends Mage_Directory_Block_Data
{

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Trade Certificate'));
        return parent::_prepareLayout();
    }

    public function getBackUrl()
    {
        $url = $this->getData('back_url');
        if (is_null($url)) {
            $url = $this->helper('customer')->getRegisterUrl();
        }
        return $url;
    }

    public function getLicenseFilename()
    {
        return Mage::helper('jewellery')->getLicenseFilename();
    }
}
