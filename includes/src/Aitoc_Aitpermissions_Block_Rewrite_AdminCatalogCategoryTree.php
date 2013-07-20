<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogCategoryTree.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ kieBwqoghChgCsBy('958bf383109d53947083297fd991a808'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogCategoryTree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        
        if (is_null($collection)) 
        {
            $collection = Mage::getModel('catalog/category')->getCollection();

            /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
                
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active')
                ->setProductStoreId($storeId)
                ->setLoadProductCount($this->_withProductCount)
                ->setStoreId($storeId);
                
            if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
            {
            	$AllowedCategories = Mage::helper('aitpermissions')->getAllowedCategories();
                if (!empty($AllowedCategories)) 
                {
                	$collection->addIdFilter($AllowedCategories);
                }
            }
            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    public function getMoveUrl()
    {
        if ($this->getRequest()->getPost('store'))
        {
            return $this->getUrl('*/catalog_category/move', array('store' => $this->getRequest()->getPost('store')));
        }

        return $this->getUrl('*/catalog_category/move', array('store' => $this->getRequest()->getParam('store')));
    }

    public function getMoveUrlPattern()
    {
        return $this->getUrl('*/catalog_category/move', array('store' => ':store'));
    }
} } 