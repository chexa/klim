<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CoreWebsiteCollection.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ eikrCToahQRaQsrj('8a98e1e3aed691a6e0d86309a5a8192e'); ?><?php

class Aitoc_Aitpermissions_Model_Rewrite_CoreWebsiteCollection extends Mage_Core_Model_Mysql4_Website_Collection
{
    public function toOptionHash()
    {
        /* @var $helper Aitoc_Aitpermissions_Helper_Data */
        $helper = Mage::helper('aitpermissions');
        if ($helper->isPermissionsEnabled())
        {
            $this->addFieldToFilter('website_id', array('in' => $helper->getAllowedWebsites()));
        }

        return parent::toOptionHash();
    }
} } 