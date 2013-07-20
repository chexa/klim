<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminSalesOrderCreateStoreSelect.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ BwrahUiZqRTZRsae('35911c49ac20870d6165f325614026c5'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminSalesOrderCreateStoreSelect extends Mage_Adminhtml_Block_Sales_Order_Create_Store_Select
{
    public function getStoreCollection($group)
    {
        $stores = parent::getStoreCollection($group);
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
        	$stores->addIdFilter($AllowedStoreviews);
        }
        return $stores;
    }
} } 