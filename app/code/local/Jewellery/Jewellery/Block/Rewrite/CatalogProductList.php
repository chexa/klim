<?php
/**
 * @copyright 
 * @author 
 */


class Jewellery_Jewellery_Block_Rewrite_CatalogProductList extends Mage_Catalog_Block_Product_List {

    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $layer = $this->getLayer();
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId(Mage::app()->getStore()->getRootCategoryId());
            }

            // if this is a product view page
            if (Mage::registry('product')) {
                // get collection of categories this product is associated with
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $this->_productCollection = $layer->getProductCollection();
			$count = (int) Mage::getStoreConfig('catalog/frontend/grid_per_page')
				? (int) Mage::getStoreConfig('catalog/frontend/grid_per_page') : 64;
            $this->_productCollection->setPageSize($count);

            /**
             * Jewellery: add material filter to collection
             */
            if (Mage::helper('jewellery')->getSelectedFilterValue() != 'all') {
                $this->_productCollection->addAttributeToFilter(Mage::helper('jewellery')->getMaterialAttributeCode(), Mage::helper('jewellery')->getSelectedFilterValue());
            }


            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            //echo $this->_productCollection->getSelect()->__toString(); exit;

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
        }

        return $this->_productCollection;
    }
}