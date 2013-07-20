<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminSystemConfigSwitcher.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ BwrahUiZqRTZRsae('9455a65f2d34c152aed8a6ddd90d92f6'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminSystemConfigSwitcher extends Mage_Adminhtml_Block_System_Config_Switcher
{
    public function getStoreSelectOptions()
    {
        $options = parent::getStoreSelectOptions();

        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            if (Mage::helper('aitpermissions')->isScopeStore())
            {
                $currentStore        = Mage::getModel('core/store')->load($this->getRequest()->getParam('store'), 'code')->getId();
                $allowedStoreviewIds = Mage::helper('aitpermissions')->getStoreviewIds();
        	    if (!in_array($currentStore, $allowedStoreviewIds))
                {
                    // redirecting to first allowed store
                    $url         = Mage::getModel('adminhtml/url');
                    $storeViewId = current($allowedStoreviewIds);
                    $storeView   = Mage::getModel('core/store')->load($storeViewId);
                    $website     = Mage::getModel('core/website')->load($storeView->getWebsiteId());

                    Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('store' => $storeView->getCode(), 'website' => $website->getCode())));
                }
            }

            if (Mage::helper('aitpermissions')->isScopeWebsite())
            {
                $currentWebsite  = Mage::getModel('core/website')->load($this->getRequest()->getParam('website'), 'code')->getId();
                $allowedWebsites = Mage::helper('aitpermissions')->getWebsiteIds();

            	if (!in_array($currentWebsite, $allowedWebsites)) 
            	{
                	// redirecting to first allowed website
                    $url     = Mage::getModel('adminhtml/url');
                    $website = Mage::getModel('core/website')->load(current($allowedWebsites));
                    Mage::app()->getResponse()->setRedirect($url->getUrl('*/*/*', array('website' => $website->getCode())));
            	}
            }

            unset($options['default']);
        }

        return $options;
    }
    
    protected function _afterToHtml($html)
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if (count($AllowedStoreviews) <= 1)
            {
                return '';
            }
        }
        return $html;
    }
} } 