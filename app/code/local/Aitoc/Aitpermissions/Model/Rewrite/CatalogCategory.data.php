<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogCategory.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ mpDjoCqeiIceIsjM('602866e2f5916d9af4f1403bb7033ff7'); ?><?php
class Aitoc_Aitpermissions_Model_Rewrite_CatalogCategory extends Mage_Catalog_Model_Category
{
    protected function _beforeSave()
    {
        if (!$this->getId() AND !Mage::registry('aitemails_category_is_new'))
        {
            Mage::register('aitemails_category_is_new', true);
        }
        return parent::_beforeSave();
    }
    
    protected function _afterSave()
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            if (Mage::helper('aitpermissions')->isScopeStore()) 
            {
                // adding this category to allowed if created by user with restricted permissions
            	$CurrentStoreviewId = Mage::app()->getRequest()->getParam('store');
            	$CurrentStoreId = Mage::getModel('core/store')->load($CurrentStoreviewId)->getGroupId();
                $RoleId = Mage::getSingleton('admin/session')->getUser()->getRole()->getId();
            
                $RoleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection()
                    ->addFieldToFilter('role_id', $RoleId)
                    ->addFieldToFilter('store_id', $CurrentStoreId)
                    ->load();
                foreach ($RoleCollection as $Role)
                {
                    $StoredCategories = explode(',', $Role->getData('category_ids'));
                    if (!in_array($this->getId(), $StoredCategories)) 
                    {
                    	$StoredCategories[] = $this->getId();
                    }
                    $Role->setData('category_ids', implode(',', $StoredCategories));
                    $Role->save();
                }
            }
            
            if (true === Mage::registry('aitemails_category_is_new'))
            {
                Mage::unregister('aitemails_category_is_new');
                $this->setStoreId(0);
                $this->setIsActive(false);
                $this->save();
            }
        }
        
        return parent::_afterSave();
    }
} } 