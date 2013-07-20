<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/AdminSystemStore.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ MqZDpchjoUIjUsDg('bfc4ce2f0225719eac0d193a0cb264b5'); ?><?php
class Aitoc_Aitpermissions_Model_Rewrite_AdminSystemStore extends Mage_Adminhtml_Model_System_Store
{
    public function __construct()
    {
        parent::__construct();
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $this->setIsAdminScopeAllowed(false);
        }
    }
    
    protected function _loadWebsiteCollection()
    {
        $this->_websiteCollection = Mage::app()->getWebsites();
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
            foreach ($this->_websiteCollection as $id => $website)
            {
                if (!in_array($id, $AllowedWebsites))
                {
                    unset($this->_websiteCollection[$id]);
                }
            }
        }
        return $this;
    }
    
    protected function _loadStoreCollection()
    {
        $this->_storeCollection = Mage::app()->getStores();
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            foreach ($this->_storeCollection as $id => $store)
            {
                if (!in_array($id, $AllowedStoreviews))
                {
                    unset($this->_storeCollection[$id]);
                }
            }
        }
    }
} } 