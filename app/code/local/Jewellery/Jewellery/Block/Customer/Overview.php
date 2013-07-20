<?php

class Jewellery_Jewellery_Block_Customer_Overview extends Mage_Directory_Block_Data
{

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCustomer()
    {
        return Mage::getSingleton('jewellery/session')->getCustomer();
    }

    public function getLicenseFilename()
    {
        $jSession = Mage::getSingleton('jewellery/session');
        if ($jSession->getCustomerLicenseUploadType() == 'upload') {
            return $jSession->getCustomerLicenseFilename();
        }

        return '';
    }


    public function getBackUrl()
    {
        $url = $this->getData('back_url');
        if (is_null($url)) {
            $url = $this->helper('jewellery')->getLicenseUrl();
        }
        return $url;
    }
}
