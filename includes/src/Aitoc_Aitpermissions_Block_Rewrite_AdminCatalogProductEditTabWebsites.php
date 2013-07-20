<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogProductEditTabWebsites.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ DpPyIQqkifNkfsEZ('9ad28521ebd13bf340bfef1af1f715ce'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductEditTabWebsites extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Websites
{
    public function getWebsiteCollection()
    {
    	$collection = Mage::getModel('core/website')->getResourceCollection();
    	if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
            $collection->addIdFilter($AllowedWebsites);
        }
        return $collection->load();
    }
} } 