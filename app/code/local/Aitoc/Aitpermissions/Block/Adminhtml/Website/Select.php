<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Adminhtml/Website/Select.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ eikrCToahQRaQsrj('b81d02049c5dfbb30f9dd396cff155d4'); ?><?php
/**
* @copyright  Copyright (c) 2010 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Adminhtml_Website_Select extends Mage_Core_Block_Template
{
    protected $_websiteIds = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitpermissions/website_select.phtml');
    }
    
    public function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) 
        {
            foreach ($websites as $websiteId => $website) 
            {
                if (!in_array($websiteId, $websiteIds)) 
                {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }
    
    public function setCurrentWebsiteIds($websiteIds)
    {
        $this->_websiteIds = $websiteIds;
        return $this;
    }
    
    public function getCurrentWebsiteIds()
    {
        return $this->_websiteIds;
    }
} } 