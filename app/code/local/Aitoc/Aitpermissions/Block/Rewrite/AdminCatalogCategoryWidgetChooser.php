<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogCategoryWidgetChooser.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ mpDjoCqeiIceIsjM('ac2353b35112b75e50ed6c78de5cd39a'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogCategoryWidgetChooser extends Mage_Adminhtml_Block_Catalog_Category_Widget_Chooser
{
    public function getCategoryCollection()
    {
        $collection = parent::getCategoryCollection()->addAttributeToSelect('url_key')->addAttributeToSelect('is_anchor');
        
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $AllowedCategories = Mage::helper('aitpermissions')->getAllowedCategories();
            $collection->addIdFilter($AllowedCategories);
        }
        return $collection;
    }
} } 