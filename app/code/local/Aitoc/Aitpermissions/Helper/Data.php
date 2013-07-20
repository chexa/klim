<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Helper/Data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ BwrahUiZqRTZRsae('b45186f038b4424df9278c7e41c8e1e5'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SCOPE_WEBSITE = 'website';
    const SCOPE_STORE   = 'store';

    private static $_storeIds;
    private static $_websiteIds;
    private static $_storeviewIds;
    private static $_categoryIds;
    private static $_scope;
    private static $_currentRoles;
    
    private static $_AllowedWebsites    = null;
    private static $_AllowedStores      = null;
    private static $_AllowedStoreviews  = null;
    private static $_AllowedCategories  = null;
 
    public function getCurrentRoles()
    {
        if (null === self::$_currentRoles && Mage::getSingleton('admin/session')->getUser()) 
        {
        	self::$_currentRoles = Mage::getModel('aitpermissions/advancedrole')->getCollection()
        	    ->loadByRoleId(Mage::getSingleton('admin/session')->getUser()->getRole()->getId());
        }
        return self::$_currentRoles;
    }
    
    public function isPermissionsEnabled()
    {
        if ($this->getCurrentRoles()) 
        {
        	return (boolean)$this->getCurrentRoles()->getSize();
        }
        return false;
    }
    
    public function getScope()
    {
        if (null === self::$_scope)
        {
            self::$_scope = self::SCOPE_WEBSITE;
            foreach ($this->getCurrentRoles() as $role)
            {
                if ($role->getStoreId())
                {
                    self::$_scope = self::SCOPE_STORE;
                    break;
                }
            }
        }
        return self::$_scope;
    }

    public function isScopeStore()
    {
        return (self::SCOPE_STORE == $this->getScope());
    }
   
    public function isScopeWebsite()
    {
        return (self::SCOPE_WEBSITE == $this->getScope());
    }
    
    // stored
    
    public function getWebsiteIds()
    {
        if (null === self::$_websiteIds)
        {
            self::$_websiteIds = array();
            foreach ($this->getCurrentRoles() as $role)
            {
                if ($role->getWebsiteId())
                {
                    self::$_websiteIds[] = $role->getWebsiteId();
                }
            }
        }
        return self::$_websiteIds;
    }

    public function getStoreIds() 
    {
        if (null === self::$_storeIds)
        {
            self::$_storeIds = array();
            foreach ($this->getCurrentRoles() as $role)
            {
                self::$_storeIds[] = $role->getStoreId();
            }
        }
        return self::$_storeIds;
    }

    public function getStoreviewIds()
    {
        if (null === self::$_storeviewIds)
        {
            self::$_storeviewIds = array();
            foreach ($this->getCurrentRoles() as $role)
            {
                self::$_storeviewIds = array_merge(self::$_storeviewIds, $role->getStoreviewIdsArray());
            }
        }
        return self::$_storeviewIds;
    }
    
    public function getCategoryIds()
    {
        if (null === self::$_categoryIds) 
        {
            self::$_categoryIds = array();
            foreach ($this->getCurrentRoles() as $role)
            {
                self::$_categoryIds = array_unique(array_merge(self::$_categoryIds, $role->getCategoryIdsArray()));
            }
        }
        return self::$_categoryIds;
    }

    // allowed
    
    public function getAllowedWebsites()
    {
        if (null === self::$_AllowedWebsites) 
        {
        	self::$_AllowedWebsites = array();

            if ($this->isScopeStore()) 
        	{
        	    $storeIds        = $this->getStoreIds();
            	$storeCollection = Mage::getModel('core/store_group')->getCollection()
                    ->addFieldToFilter('group_id', array('in' => $storeIds))
                    ->load();
                foreach ($storeCollection as $store)
                {
                    self::$_AllowedWebsites[] = $store->getWebsiteId();
                }

                self::$_AllowedWebsites = array_unique(self::$_AllowedWebsites);
        	}

        	if ($this->isScopeWebsite())
        	{
                self::$_AllowedWebsites = $this->getWebsiteIds();
        	}
        }

        return self::$_AllowedWebsites;
    }
   
    public function getAllowedStores()
    {
        if (null === self::$_AllowedStores) 
        {
            if ($this->isScopeStore()) 
        	{
        	    self::$_AllowedStores = $this->getStoreIds();
        	}        	
        	if ($this->isScopeWebsite()) 
        	{
                self::$_AllowedStores = array();
                $storeCollection = Mage::getModel('core/store_group')->getCollection()->addWebsiteFilter($this->getWebsiteIds());
                foreach ($storeCollection as $store)
                {
                    self::$_AllowedStores[] = $store->getStoreId();
                }
        	}
        }
        return self::$_AllowedStores;
    }

    public function getAllowedStoreviews()
    {
        if (null === self::$_AllowedStoreviews) 
        {
            if ($this->isScopeStore()) 
        	{
        	    self::$_AllowedStoreviews = $this->getStoreviewIds();
        	}        	
        	if ($this->isScopeWebsite()) 
        	{
                self::$_AllowedStoreviews = array();
                $StoreviewCollection = Mage::getModel('core/store')->getCollection()->addWebsiteFilter($this->getWebsiteIds());
                foreach ($StoreviewCollection as $Storeview)
                {
                    self::$_AllowedStoreviews[] = $Storeview->getStoreId();
                }
        	}
        }
        return self::$_AllowedStoreviews;
    }

    public function getAllowedCategories()
    {
        if (null === self::$_AllowedCategories) 
        {
            if ($this->isScopeStore()) 
            {
                self::$_AllowedCategories = $this->getCategoryIds();
            }
            if ($this->isScopeWebsite())
            {
                self::$_AllowedCategories = array();
                foreach ($this->getCurrentRoles() as $role)
                {
                    self::$_AllowedCategories = array_merge(self::$_AllowedCategories, $role->getWebsiteCategories());
                }
            }
        }
        return self::$_AllowedCategories;
    }
} } 