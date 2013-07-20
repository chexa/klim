<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCmsBlockEdit.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ghaZqIwDpTUDTsZB('0c235e181f91c14c39ed17fc4ea6272e'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCmsBlockEdit extends Mage_Adminhtml_Block_Cms_Block_Edit
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $allowDelete = true;
            // if page is not assigned to any store views but permitted, will allow to delete and disable it
            
            $blockModel = Mage::registry('cms_block');

            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if ($blockModel->getStoreId() && is_array($blockModel->getStoreId()))
            {
                foreach ($blockModel->getStoreId() as $blockStoreId)
                {
                    if (!in_array($blockStoreId, $AllowedStoreviews))
                    {
                        $allowDelete = false;
                        break 1;
                    }
                }
            }
            
            if (!$allowDelete)
            {
                $this->_removeButton('delete');
            }
        }
        return $this;
    }
} } 