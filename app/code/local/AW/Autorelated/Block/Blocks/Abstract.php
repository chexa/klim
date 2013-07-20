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
 */abstract class AW_Autorelated_Block_Blocks_Abstract extends Mage_Core_Block_Template {

    protected $_collection = null;

    protected function _initCollection() {
        if ($this->_collection === null) {
            $this->_collection = Mage::getModel('awautorelated/product_collection');
            $this->_collection->addAttributeToSelect('*');
            $_visibility = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
            );
            $this->_collection->addAttributeToFilter('visibility', $_visibility)
                    ->addAttributeToFilter('status', array("in" => Mage::getSingleton("catalog/product_status")->getVisibleStatusIds()));
            if (!$this->_getShowOutOfStock())
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_collection);
            $this->_collection
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->groupByAttribute('entity_id');
        }
        return $this->_collection;
    }

    protected function _initCollectionForIds(array $ids) {
        unset($this->_collection);
        $this->_collection = Mage::getModel('awautorelated/product_collection');
        $this->_collection->addAttributeToSelect('*');
        $this->_collection->addFilterByIds($ids);
        return $this->_collection;
    }

    protected function _getShowOutOfStock() {
        $_show = true;
        if (($_ciHelper = Mage::helper('cataloginventory')) && method_exists($_ciHelper, 'isShowOutOfStock')) {
            $_show = $_ciHelper->isShowOutOfStock();
        }
        return $_show;
    }

    public function getCollection() {
        if ($this->canShow()) {
            if ($this->_collection === null) {
                $this->_initCollection();
                $this->_renderRelatedProductsFilters();
                $this->_postProcessCollection();
            }
            return $this->_collection;
        }
        return null;
    }

    protected function _postProcessCollection() {
        if ($this->_collection instanceof AW_Autorelated_Model_Product_Collection) {
            $this->_collection->addUrlRewrites()
                    ->addMinimalPrice()
                    ->groupByAttribute('entity_id');
        }
        return $this;
    }

    public function getBlockPosition() {
        return $this->getParent() && $this->getParent()->getBlockPosition() ? $this->getParent()->getBlockPosition() : null;
    }

    protected function _getCurrentlyViewed() {
        return $this->getData('currently_viewed') ? $this->getData('currently_viewed') : null;
    }

    protected function _getRelatedProducts() {
        return $this->getData('related_products') ? $this->getData('related_products') : null;
    }

    protected function _beforeToHtml() {
        $this->_setTemplate();
        return parent::_beforeToHtml();
    }

    abstract protected function _setTemplate();

    abstract protected function _renderRelatedProductsFilters();

    abstract public function canShow();
}
