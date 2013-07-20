<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminPollGrid.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ mpDjoCqeiIceIsjM('89caf74b15f5020fdc894d992d6fc978'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminPollGrid extends Mage_Adminhtml_Block_Poll_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('poll/poll')->getCollection();
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $collection->addStoreFilter(Mage::helper('aitpermissions')->getAllowedStoreviews());
        }

        $this->setCollection($collection);
        Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();

        if (!Mage::app()->isSingleStoreMode()) {
            $this->getCollection()->addStoreData();
        }
        
        return $this;
    }
} } 