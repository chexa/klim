<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCustomerGrid.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ZqWmUNhEoufyAsPa('586b9907c393cb5b1896cc1344f3aa60'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCustomerGrid extends Mage_Adminhtml_Block_Customer_Grid
{
	protected function _prepareColumns()
	{
		parent::_prepareColumns();
		
		if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
		{
            if (!Mage::getStoreConfig('admin/general/showallcustomers') && isset($this->_columns['website_id']))
            {
                unset($this->_columns['website_id']);
                $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
                
                if (count($AllowedWebsites) > 1)
                {
                    $WebsiteFilter = array();
                    foreach ($AllowedWebsites as $AllowedWebsite) 
                    {
                    	$Website = Mage::getModel('core/website')->load($AllowedWebsite);
                    	$WebsiteFilter[$AllowedWebsite] = $Website->getData('name');
                    }
                    
                	$this->addColumn('website_id', array(
                    'header'    => Mage::helper('customer')->__('Website'),
                    'align'     => 'center',
                    'width'     => '80px',
                    'type'      => 'options',
                    'options'   => $WebsiteFilter,
                    'index'     => 'website_id',
                    ));
                }
            }
		}
        return $this;
	}
} } 