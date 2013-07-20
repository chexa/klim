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
 */class AW_Autorelated_Model_Source_Block_Category_Include extends AW_Autorelated_Model_Source_Abstract {
    const ALL = 1;
    const CURRENT_CATEGORY = 2;
    const CURRENT_CATEGORY_WITH_CHILDS = 3;

    const ALL_LABEL = 'All';
    const CURRENT_CATEGORY_LABEL = 'Current Category Only';
    const CURRENT_CATEGORY_WITH_CHILDS_LABEL = "Current category and its  subcategories";

    public function toOptionArray() {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::ALL, 'label' => $_helper->__(self::ALL_LABEL)),
            array('value' => self::CURRENT_CATEGORY, 'label' => $_helper->__(self::CURRENT_CATEGORY_LABEL)),
            array('value' => self::CURRENT_CATEGORY_WITH_CHILDS, 'label' => $_helper->__(self::CURRENT_CATEGORY_WITH_CHILDS_LABEL))
        );
    }

}
