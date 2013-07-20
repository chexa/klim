<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminhtmlWidgetInstanceEdit.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ghaZqIwDpTUDTsZB('fd7f1ae63df655243fd35b0aba0b27e9'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminhtmlWidgetInstanceEdit extends Mage_Widget_Block_Adminhtml_Widget_Instance_Edit
{
    protected function _preparelayout()
    {
        parent::_prepareLayout();

        $helper = Mage::helper('aitpermissions');
        /* @var $helper Aitoc_Aitpermissions_Helper_Data */

        if ($helper->isPermissionsEnabled())
        {
            $widgetInstance = Mage::registry('current_widget_instance');

            // checking if we have permissions to edit this widget
            if ($widgetInstance->getId() && is_array($widgetInstance->getStoreIds()) && !array_intersect($widgetInstance->getStoreIds(), $helper->getAllowedStoreviews()))
            {
                Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
            }

            if (!$widgetInstance->getStoreIds() || array_diff($widgetInstance->getStoreIds(), $helper->getAllowedStoreviews()))
            {
                $this->_removeButton('delete');
            }
        }
        
        return $this;
    }
} } 