<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */class AW_Autorelated_Block_Blocks_Product extends AW_Autorelated_Block_Blocks_Abstract {

    protected $_canShow = null;
    protected $_currentProduct = null;
    protected $_joinedAttributes;
    protected $_nativeBlock = null;

    public function canShow() {
        if ($this->_canShow === null) {

            $currentProduct = $this->_getCurrentProduct();
            $model = Mage::getModel('awautorelated/blocks_product_ruleviewed');
            $model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
            $conditions = $this->getCurrentlyViewed()->getConditions();

            if (isset($conditions['viewed'])) {

                $model->getConditions()->loadArray($conditions, 'viewed');
                $match = $model->getMatchingProductIds();
                if (in_array($currentProduct->getId(), $match))
                    $this->_canShow = true;
                else
                    $this->_canShow = false;
            }
            else
                $this->_canShow = true;
        }
        return $this->_canShow;
    }

    protected function _getCurrentProduct() {
        if ($this->_currentProduct === null) {
            $productId = ($id = $this->getParent()->getData('product_id')) ? substr($id, strpos($id, '/') + 1) : $this->getRequest()->getParam('id');
            $product = Mage::getModel('catalog/product')->load($productId);
            $this->_currentProduct = $product;
        }
        return $this->_currentProduct;
    }

    protected function _setTemplate() {
        if (!$this->getTemplate()) {
            if (!Mage::helper('awautorelated')->checkVersion('1.9')) {
                switch ($this->getBlockPosition()) {
                    case AW_Autorelated_Model_Source_Position::INSTEAD_NATIVE_RELATED_BLOCK:
                    case AW_Autorelated_Model_Source_Position::UNDER_NATIVE_RELATED_BLOCK:
                        $this->setTemplate('aw_autorelated/blocks/product/product-sidebar.phtml');
                        break;
                    default:
                        $this->setTemplate('aw_autorelated/blocks/product/product.phtml');
                }
            } else {
                $this->setTemplate('aw_autorelated/blocks/product/product.phtml');
            }
        }
        // Mage::helper('awautorelated')->addCss('aw_autorelated/css/product.css');
        return $this;
    }

    protected function _renderRelatedProductsFilters() {

        $currentProduct = $this->_getCurrentProduct();
        $model = Mage::getModel('awautorelated/blocks_product_rulerelated');
        $model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
        $conditions = $this->getRelatedProducts()->getRelated();
        $mIds = array();
        $gCondition = $this->getRelatedProducts()->getGeneral();
        $limit = $this->getRelatedProducts()->getProductQty();

        if (isset($conditions['conditions']['related'])) {
            $model->getConditions()->loadArray($conditions['conditions'], 'related');
            $mIds = $model->getMatchingProductIds();

            if (empty($mIds)) {
                unset($this->_collection);
                return $this;
            } else {
                $mIds = array_diff($mIds, array($currentProduct->getId()));
            }
        }

        if (!empty($gCondition)) {
            $filteredIds = $this->filterByAtts($currentProduct, $gCondition, $mIds);
        } elseif (!empty($mIds)) {
            $filteredIds = $mIds;
        } else {
            $filteredIds = $this->_collection->getAllIds();
        }

        if (!empty($filteredIds)) {

            $filteredIds = array_diff($filteredIds, array($currentProduct->getId()));

            $filteredIds = array_diff($filteredIds, Mage::helper('awautorelated')->getWishlistProductsIds());

            $filteredIds = array_diff($filteredIds, Mage::getSingleton('checkout/cart')->getProductIds());

            $filteredIds = array_intersect($filteredIds, $this->_collection->getAllIds());

            $itemsCount = count($filteredIds);

            if (!$itemsCount) {
                unset($this->_collection);
                return $this;
            }

            if ($itemsCount > $limit) {
                if ($this->getData('randomize')) {
                    $new_arr = array();
                    $random_positions = array_rand($filteredIds, $limit);
                    foreach ($random_positions as $value) {
                        $new_arr[] = $filteredIds[$value];
                    }
                    $filteredIds = $new_arr;
                } else {
                    $filteredIds = array_slice($filteredIds, 0, $limit);
                }
            }
            $this->_initCollectionForIds($filteredIds);

            if ($this->getData('randomize')) {
                $this->_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            }

            $this->_collection->setPageSize($limit);
            $this->_collection->setCurPage(1);
        } else {
            unset($this->_collection);
        }

        return $this;
    }

    /*
     * 
     * filter product by attributes valuesd
     * Mage_Catalog_Model_Product $currentProduct -main product
     * Array $atts - atts list for filter  
     * Array $ids - products id for filter
     */

    public function filterByAtts(Mage_Catalog_Model_Product $currentProduct, $atts, $ids = null) {

        $this->_joinedAttributes = array();
        $collection = $this->_collection;

        $rule = new AW_Autorelated_Model_Blocks_Rule();

        foreach ($atts as $at) {
            if ($at['att'] == 'category_ids') {
                $value = ($currentProduct->getCategory()) ? $currentProduct->getCategory()->getId() : null;

                /* no current category - get all categories */
                if (!$value) {
                    $categoriesIds = $currentProduct->getCategoryIds();
                    $value = (count($categoriesIds)) ? implode(', ', $categoriesIds) : null;
                }
            } else {
                $value = $currentProduct->getData($at['att']);
            }

            if (!$value) {
                $collection = NULL;
                return false;
            }

            $sql = $rule->prepareSqlForAtt($at['att'], $this->_joinedAttributes, $collection, $at['condition'], $value);

            if ($sql) {
                $collection->getSelect()->where($sql);
            }
        }
        if ($ids) {
            $collection->getSelect()->where('e.entity_id IN(' . implode(',', $ids) . ')');
        }

        $collection->getSelect()->group('e.entity_id');

        return $collection->getColumnValues('entity_id');
    }

    public function showNativeBlock() {
        return Mage::getSingleton('awautorelated/blocks_product')->showNativeBlock();
    }

    public function iterateBlock() {
        Mage::getSingleton('awautorelated/blocks_product')->iterateBlock();
    }

    public function markAsShowed() {
        Mage::getSingleton('awautorelated/blocks_product')->markAsShowed();
    }

}
