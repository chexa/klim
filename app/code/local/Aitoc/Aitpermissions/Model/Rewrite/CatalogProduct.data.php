<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogProduct.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ yojeihpBwcCBcsem('fd4023b0bf566412c303952fc13fce21'); ?><?php
class Aitoc_Aitpermissions_Model_Rewrite_CatalogProduct extends Mage_Catalog_Model_Product
{
    protected function _beforeSave()
    {
        parent::_beforeSave();
        
        if (Mage::app()->getRequest()->getPost('simple_product') 
        &&  Mage::app()->getRequest()->getQuery('isAjax')
        &&  Mage::helper('aitpermissions')->isScopeStore())
        {
            $configurableProduct = Mage::getModel('catalog/product')
                ->setStoreId(0)
                ->load(Mage::app()->getRequest()->getParam('product'));
                
            if (!$configurableProduct->isConfigurable()) {
                return $this;
            }
        
            if (!$this->getData('category_ids'))
            {
                $this->setData('category_ids', $configurableProduct->getData('category_ids'));
            }
        }
        return $this;
    }
    
    protected function _beforeDelete()
    {
        parent::_beforeDelete();
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $bAllow = false;
            $product = Mage::getModel('catalog/product')->load(Mage::app()->getRequest()->getParam('id'));
    
            if (Mage::helper('aitpermissions')->isScopeWebsite() && Mage::getStoreConfig('admin/general/allowdelete_perwebsite')) 
            {
                $WebsiteIds = Mage::helper('aitpermissions')->getWebsiteIds();
                $productWebsiteIds = $product->getWebsiteIds();
                if (!empty($productWebsiteIds)) 
                {
                	foreach ($WebsiteIds as $WebsiteId)
                	{
                	    if (in_array($WebsiteId, $productWebsiteIds)) 
                	    {
                	    	$bAllow = true;
                	    	break;
                	    }
                	}
                }
            }
            
            if (Mage::helper('aitpermissions')->isScopeStore() && Mage::getStoreConfig('admin/general/allowdelete')) 
            {
            	$CategoryIds = Mage::helper('aitpermissions')->getCategoryIds();
                $productCategoryIds = $product->getCategoryIds();
                if (!empty($CategoryIds) && !empty($productCategoryIds)) 
                {
                	foreach ($CategoryIds as $CategoryId)
                	{
                	    if (in_array($CategoryId, $productCategoryIds)) 
                	    {
                	    	$bAllow = true;
                	    	break;
                	    }
                	}
                }
            }
            
            if ($bAllow == false) 
            {
                Mage::throwException(Mage::helper('aitpermissions')->__('Sorry, you have no permissions to delete this product. For more details please contact site administrator.'));
            }
        }
        return $this;
    }
    
    protected function _afterLoad()
    {
        parent::_afterLoad();
        return $this;
    }
} } 