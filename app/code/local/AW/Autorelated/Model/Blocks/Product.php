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
 */class AW_Autorelated_Model_Blocks_Product extends Mage_Core_Model_Abstract {

    protected $_block = null;
    protected $_show = false;

    public function showNativeBlock() {
        $count = $this->getBlockCount(AW_Autorelated_Model_Source_Position::INSTEAD_NATIVE_RELATED_BLOCK);

        if ((($count == $this->_block || $count == 0) && !$this->_show) || Mage::helper('awautorelated')->getExtDisabled())
            return true;
        else
            return false;
    }

    public function getBlockCount($position) {

        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->addStoreFilter()
                ->addPositionFilter($position)
                ->addStatusFilter()
                ->addCustomerGroupFilter(Mage::helper('awautorelated')->getCurrentUserGroup())
                ->addDateFilter()
        ;

        return $collection->getSize();
    }

    public function iterateBlock() {

        if ($this->_block)
            $this->_block++;
        else
            $this->_block = 1;
    }

    public function markAsShowed() {
        $this->_show = true;
    }

}
