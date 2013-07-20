<?php
/**
 * @copyright 
 * @author 
 */

if (Mage::getConfig()->getNode('modules/Clever_Cms')) {
    class Jewellery_Jewellery_Block_Rewrite_CatalogNavigation_Abstract extends Clever_Catalog_Block_Navigation {}
} else {
    class Jewellery_Jewellery_Block_Rewrite_CatalogNavigation_Abstract extends Mage_Catalog_Block_Navigation {}
}

class Jewellery_Jewellery_Block_Rewrite_CatalogNavigation extends Jewellery_Jewellery_Block_Rewrite_CatalogNavigation_Abstract {

    public function getTopChildCategories()
    {
        $layer = Mage::getSingleton('catalog/layer');
        $category   = Mage::helper('jewellery')->getTopCategory(); // $layer->getCurrentCategory();
        /* @var $category Mage_Catalog_Model_Category */
        $categories = $category->getChildrenCategories();
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($categories);
        return $categories;
    }

    public function getCategoryChildCategories($category)
    {
        $layer = Mage::getSingleton('catalog/layer');
        //$category   = $layer->getCurrentCategory();
        /* @var $category Mage_Catalog_Model_Category */
        $categories = $category->getChildrenCategories();
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($categories);
        return $categories;
    }
}
