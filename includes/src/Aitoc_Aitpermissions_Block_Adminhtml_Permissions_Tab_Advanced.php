<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Adminhtml/Permissions/Tab/Advanced.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ joEkcRprwNQrNskD('c2d4d76e20432c1263dd48b9c2396c74'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Adminhtml_Permissions_Tab_Advanced extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    // private $_currentRole = null;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitpermissions/permissions_advanced.phtml');
        $this->_withProductCount = false;
    }

    protected function _prepareLayout()
    {
        $this->setChild('stores', $this->getLayout()->createBlock('aitpermissions/adminhtml_store_switcher'));
        
        $WebsiteIds = array();
        $RoleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection()->loadByRoleId($this->getRequest()->getParam('rid'));
        if ($RoleCollection->getItems()) 
        {
            foreach ($RoleCollection->getItems() as $role)
            {
                if ($role->getWebsiteId())
                {
                    $WebsiteIds[] = $role->getWebsiteId();
                }
            }
        }
        
        $this->setChild('websites', $this->getLayout()->createBlock('aitpermissions/adminhtml_website_select')
        ->setCurrentWebsiteIds($WebsiteIds));
        return $this;
    }
    
    public function getScope()
    {
        $RoleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection()->loadByRoleId($this->getRequest()->getParam('rid'));
        if ($RoleCollection->getItems()) 
        {
            foreach ($RoleCollection->getItems() as $role)
            {
                if ($role->getStoreId())
                {
                    return 'store';
                }
            }
            return 'website';
        }
        return 'disabled';
    }
    
    public function isReadonly()
    {
        return false;
    }

    public function getStores()
    {
        $stores = Mage::app()->getStores(true);
        return $stores;
    }
    
     /**
     * Get websites
     *
     * @return array
     */
    public function getWebsitesGroups()
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
        $groups = array();
        foreach ($websites as $website)
        {
            foreach ($website->getGroups() as $group)
            {
                $groups[] = $group;
            }
        }
        return $groups;
    }
} } 