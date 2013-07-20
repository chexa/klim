<?php
class Jewellery_Jewellery_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('jewellery');
    }

    public function clear() 
    {
    	$this->setRegistrationStep(null);
    	$this->setCustomerFormData(null);
    	$this->setCustomerLicenseFilename(null);
    	$this->setCustomerLicenseUploadType(null);
    	$this->setCustomer(null);

    	return $this;
    }
}