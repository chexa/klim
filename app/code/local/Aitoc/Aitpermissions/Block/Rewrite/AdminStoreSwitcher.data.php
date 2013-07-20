<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminStoreSwitcher.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ZqWmUNhEoufyAsPa('4b9b09a1be19787838e945551724138f'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminStoreSwitcher extends Mage_Adminhtml_Block_Store_Switcher
{
	public function __construct()
	{
		parent::__construct();

		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
			if (Mage::helper('aitpermissions')->isScopeStore())
            {
    		    $this->hasDefaultOption(false);
    	    }
            
            if (Mage::helper('aitpermissions')->isScopeWebsite())
            {
                $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
                if (!empty($AllowedWebsites)) 
                {
                    $this->setWebsiteIds($AllowedWebsites);
                }
            }
		}
	}
	
	public function getStores($group)
	{
        $stores = parent::getStores($group);
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if (!empty($AllowedStoreviews))
            {
                foreach ($stores as $storeId => $store) 
                {
                    if (!in_array($storeId, $AllowedStoreviews)) 
                    {
                        unset($stores[$storeId]);
                    }
                }
            }
        }
        return $stores;
	}
	
	public function getStoreCollection($group)
	{
		$stores = parent::getStoreCollection($group);
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if (!empty($AllowedStoreviews))
            {
            	$stores->addIdFilter($AllowedStoreviews);
            }
        }
        return $stores;
	}
    
    public function getWebsiteCollection()
    {
        $websiteCollection = parent::getWebsiteCollection();
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $Allowedwebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
            if (!empty($Allowedwebsites))
            {
                $websiteCollection->addIdFilter($Allowedwebsites);
            }
        }
        return $websiteCollection;
    }
    
    protected function _toHtmlReports()
    {
        $sHtml = parent::_toHtml();
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $bAllow = false;
            
            $CurrentWebsite  = Mage::app()->getRequest()->getParam('website');
            $CurrentStore    = Mage::app()->getRequest()->getParam('group');
            if (Mage::app()->getRequest()->getParam('store_ids'))
            {
                $CurrentStoreviews = explode(',', Mage::app()->getRequest()->getParam('store_ids'));
            } 
            else 
            {
                $CurrentStoreviews = array();
                if (Mage::app()->getRequest()->getParam('store'))
                {
                    $CurrentStoreviews = array(Mage::app()->getRequest()->getParam('store'));
                }
            }
            
            $AllowedWebsites =      Mage::helper('aitpermissions')->getAllowedWebsites();
            $AllowedStores =        Mage::helper('aitpermissions')->getAllowedStores();
            $AllowedStoreviews =    Mage::helper('aitpermissions')->getAllowedStoreviews();
            
            if ($AllowedWebsites && $AllowedStores && $AllowedStoreviews) 
            {
            	if (in_array($CurrentWebsite, $AllowedWebsites) || in_array($CurrentStore, $AllowedStores) || array_intersect($CurrentStoreviews, $AllowedStoreviews)) 
            	{
            		$bAllow = true;
            	}
            }
            
            if (!$bAllow) 
            {
                $url = Mage::getModel('adminhtml/url');
                Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('_current'=>false, 'store' => $AllowedStoreviews[0])));
            }
        
            // removing <option value="">All Store Views</option> option if have limited access
            $sHtml = preg_replace('@<option value="">(.*)</option>@', '', $sHtml);
            
            // if no stores selected, need to select allowed
            if (!$CurrentWebsite && !$CurrentStore && !$CurrentStoreviews)
            {
                // enhanced switcher is used on categories page
                if (preg_match('@varienStoreSwitcher@', $sHtml))
                {
                    $sHtml .= '
                    <script type="text/javascript">
                    try
                    {
                        Event.observe(window, "load", varienStoreSwitcher.optionOnChange);
                    } catch (err) {}
                    </script>
                    ';
                } 
                else
                {
                    $sHtml .= '
                    <script type="text/javascript">
                    permissionsSwitchStore = function()
                    {
                        switchStore($("store_switcher"));
                    }
                    
                    try
                    {
                        Event.observe(window, "load", permissionsSwitchStore);
                    } catch (err) {}
                    </script>
                    ';
                }
            }
        }
        return $sHtml;
    }
    
	protected function _toHtml()
    {
        if (strpos(Mage::app()->getRequest()->getControllerName(), 'report') !== false)
        {
            // ... and 1.3 (other) versions
            return $this->_toHtmlReports();
        }

        // the next code will work for all store selectors except reports
        $sHtml = parent::_toHtml();
        $helper = Mage::helper('aitpermissions');
        /* @var $helper Aitoc_Aitpermissions_Helper_Data */
        
        if ($helper->isPermissionsEnabled())
        {
        	$AllowedStoreviews = $helper->getAllowedStoreviews();
            if (!empty($AllowedStoreviews)) 
            {
            	if (!in_array(Mage::app()->getRequest()->getParam('store'), $AllowedStoreviews))
                {
                    $url = Mage::getModel('adminhtml/url');
                    Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('_current'=>true, 'store'=>$AllowedStoreviews[0])));
                }
            }
            
            // removing <option value="">All Store Views</option> option if have limited access
            $sHtml = preg_replace('@<option value="">(.*)</option>@', '', $sHtml);
        }
        
        // enhanced switcher is used on categories page
        if (preg_match('@varienStoreSwitcher@', $sHtml))
        {
            $sHtml .= '
            <script type="text/javascript">
            try
            {
                Event.observe(window, "load", varienStoreSwitcher.optionOnChange);
            } catch (err) {}
            </script>
            ';
        }
        
        return $sHtml;
    }
} } 