<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCustomerOnlineGrid.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ghaZqIwDpTUDTsZB('53d8507255e86b2abfc6937d4daeb736'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCustomerOnlineGrid extends Mage_Adminhtml_Block_Customer_Online_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('log/visitor_online')
            ->prepare()
            ->getCollection();
        /* @var $collection Mage_Log_Model_Mysql4_Visitor_Online_Collection */
        $collection->addCustomerData();
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();
            $collection->getSelect()->where('`customer_email`.website_id IN ('.implode(',', $AllowedWebsites).')');
        }

        $this->setCollection($collection);
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        
        return $this;
    }
} } 