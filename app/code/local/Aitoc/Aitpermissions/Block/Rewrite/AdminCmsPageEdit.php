<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCmsPageEdit.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ joEkcRprwNQrNskD('7f2e10efd6a4e050cd2c11666244b92f'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCmsPageEdit extends Mage_Adminhtml_Block_Cms_Page_Edit
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $allowDelete = true;
            $pageModel = Mage::registry('cms_page');
            
            // if page is assigned to store views of allowed website only, will allow to delete it
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();

            if (is_array($pageModel->getStoreId()) && $pageModel->getStoreId()) 
            {
                foreach ($pageModel->getStoreId() as $pageStoreId)
                {
                    if (!in_array($pageStoreId, $AllowedStoreviews))
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