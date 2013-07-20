<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogCategoryEditForm.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ eikrCToahQRaQsrj('aa4122dde503aa4933db387251e610d9'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogCategoryEditForm extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
    public function _prepareLayout()
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
        	if ((Mage::helper('aitpermissions')->isScopeStore() && !Mage::getStoreConfig('admin/general/allowdelete')) 
             || (Mage::helper('aitpermissions')->isScopeWebsite() && !Mage::getStoreConfig('admin/general/allowdelete_perwebsite')))
            {
                $category = $this->getCategory()->setIsDeleteable(false);
                Mage::unregister('category');
                Mage::register('category', $category);
            }
        }
        return parent::_prepareLayout();
    }
} } 