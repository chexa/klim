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
 */class AW_Autorelated_Helper_Data extends Mage_Core_Helper_Abstract {
    const REGISTRY_ABSTRACT_BLOCK = 'awautorelated_product_abstract_block';
    const REGISTRYSTORAGE_FILES = 'awarp_blocks_storage_files';
    const FILE_ADDED = 'added';
    const FILE_USED = 'used';

    /**
     * Compare param $version with magento version
     * @param string $version Version to compare
     * @return boolean
     */
    public function checkVersion($version, $operator = '>=') {
        return version_compare(Mage::getVersion(), $version, $operator);
    }

    public function removeEmptyItems($var) {
        return!empty($var);
    }

    public function prepareArray($var) {
        if (is_string($var))
            $var = @explode(',', $var);
        if (is_array($var)) {
            $var = array_unique($var);
            $var = array_filter($var, array($this, 'removeEmptyItems'));
            $var = @implode(',', $var);
        }
        return $var;
    }

    public function convertFlatToRecursive(array $rule, $keys) {
        $arr = array();
        foreach ($rule as $key => $value) {
            if (in_array($key, $keys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = array();
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * convert dates into Zend_Date
                 */
                if (in_array($key, array('from_date', 'to_date')) && $value) {
                    $value = Mage::app()->getLocale()->date(
                            $value, Varien_Date::DATE_INTERNAL_FORMAT, null, false
                    );
                }
            }
        }
        return $arr;
    }

    public function isEditAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/awautorelated/new');
    }

    public function isViewAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/awautorelated/manage');
    }

    public function getCurrentUserGroup() {
        return Mage::getSingleton('customer/session')->getCustomerGroupId();
    }

//    public function addCss($file) {
//        $_storage = $this->getRegistryStorage();
//        if (!$_storage->getData($file))
//            $_storage->setData($file, self::FILE_ADDED);
//        return $this;
//    }
//
//    public function getCss() {
//        $_storage = $this->getRegistryStorage();
//        $result = array();
//        foreach ($_storage->getData() as $file => $status) {
//            if ($status == self::FILE_ADDED)
//                $result[] = $file;
//            $_storage->setData($file, self::FILE_USED);
//        }
//        return $result;
//    }
//
//    protected function getRegistryStorage() {
//        $_storage = Mage::registry(self::REGISTRYSTORAGE_FILES);
//        if (!$_storage) {
//            $_storage = new Varien_Object();
//            Mage::register(self::REGISTRYSTORAGE_FILES, $_storage);
//        }
//        return $_storage;
//    }

    public function getAbstractProductBlock() {
        $_abstractBlock = Mage::registry(self::REGISTRY_ABSTRACT_BLOCK);
        if (!$_abstractBlock) {
            $_abstractBlock = Mage::getSingleton('core/layout')->createBlock('catalog/product_list');
            $_abstractBlock->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
            Mage::register(self::REGISTRY_ABSTRACT_BLOCK, $_abstractBlock);
        }
        return $_abstractBlock;
    }

    public function updateChild($array, $from, $to) {
        foreach ($array as $k => $rule) {
            foreach ($rule as $name => $param) {
                if ($name == 'type' && $param == $from)
                    $array[$k][$name] = $to;
            }
        }
        return $array;
    }

    public function getExtDisabled() {
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_Autorelated');
    }

    /**
     * 
     * @return Array of all productIds in current customer's wishlist
     * 
     * 
     */
    public function getWishlistProductsIds() {
        $ids = array();
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $wishlistTable = $resource->getTableName('wishlist');
                $wishlistItemTable = $resource->getTableName('wishlist_item');

                $db = $resource->getConnection('core_read');
                $query = $db->select()
                        ->from(array('w' => $wishlistTable), array('wishlist_id'))
                        ->where('customer_id = ?', $session->getCustomer()->getId());
                $query2 = $db->select()
                        ->from(array('i' => $wishlistItemTable), array('product_id'))
                        ->where('i.store_id = ?', Mage::app()->getStore()->getId())
                        ->join(array('w' => $query), 'w.wishlist_id = i.wishlist_id', array());
                $ids = $db->fetchCol($query2);
            } catch (Exception $e) {
                
            }
        }
        return $ids;
    }

}
