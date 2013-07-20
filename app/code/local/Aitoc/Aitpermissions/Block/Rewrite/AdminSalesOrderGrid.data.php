<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminSalesOrderGrid.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ MqZDpchjoUIjUsDg('4a1e2dfeb0d57b0a72eaee3b169127a2'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminSalesOrderGrid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
			$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
    		if (count($AllowedStoreviews) <=1 && isset($this->_columns['store_id']))
    		{
    		    unset($this->_columns['store_id']);
    		}
		}
		return $this;
	}
} } 