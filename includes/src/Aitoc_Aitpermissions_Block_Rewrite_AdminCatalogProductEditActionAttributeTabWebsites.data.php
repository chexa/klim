<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogProductEditActionAttributeTabWebsites.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ mpDjoCqeiIceIsjM('f9f2143370fc885d9acda9f0597bf495'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductEditActionAttributeTabWebsites extends Mage_Adminhtml_Block_Catalog_Product_Edit_Action_Attribute_Tab_Websites
{
    public function getWebsiteCollection()
    {
        $websites = parent::getWebsiteCollection();

        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	$AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
        	foreach ($websites as $i => $website)
            {
            	if (!in_array($website->getId(), $AllowedWebsites))
            	{
            		unset($websites[$i]);
            	}
            }
        }
        return $websites;
    }
} } 