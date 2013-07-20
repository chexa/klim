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
 */class AW_Autorelated_Model_Source_Position extends AW_Autorelated_Model_Source_Abstract {
    const INSIDE_PRODUCT_PAGE = 1;
    const INSTEAD_NATIVE_RELATED_BLOCK = 2;
    const UNDER_NATIVE_RELATED_BLOCK = 3;
    const BEFORE_CONTENT = 4;
    const CUSTOM = 0;

    const INSIDE_PRODUCT_PAGE_LABEL = 'Inside product page';
    const INSTEAD_NATIVE_RELATED_BLOCK_LABEL = 'Instead native related block';
    const UNDER_NATIVE_RELATED_BLOCK_LABEL = 'Under native related block';
    const BEFORE_CONTENT_LABEL = 'Before content';
    const CUSTOM_LABEL = 'Custom';

    const INSIDE_PRODUCT_PAGE_SHORT_LABEL = 'Inside product';
    const INSTEAD_NATIVE_RELATED_BLOCK_SHORT_LABEL = 'Instead native';
    const UNDER_NATIVE_RELATED_BLOCK_SHORT_LABEL = 'Under native';
    const BEFORE_CONTENT_SHORT_LABEL = 'Before content';
    const CUSTOM_SHORT_LABEL = 'Custom';

    public function toOptionArray($categoryBlock = false) {
        $_helper = $this->_getHelper();
        $result = array(array('value' => self::BEFORE_CONTENT, 'label' => $_helper->__(self::BEFORE_CONTENT_LABEL)));
        if (!$categoryBlock) {
            array_push(
                    $result, array('value' => self::INSTEAD_NATIVE_RELATED_BLOCK, 'label' => $_helper->__(self::INSTEAD_NATIVE_RELATED_BLOCK_LABEL)), array('value' => self::UNDER_NATIVE_RELATED_BLOCK, 'label' => $_helper->__(self::UNDER_NATIVE_RELATED_BLOCK_LABEL)), array('value' => self::INSIDE_PRODUCT_PAGE, 'label' => $_helper->__(self::INSIDE_PRODUCT_PAGE_LABEL))
            );
        }
        $result[] = array('value' => self::CUSTOM, 'label' => $_helper->__(self::CUSTOM_LABEL));
        return $result;
    }

    public function getOptionArray() {
        $_helper = $this->_getHelper();
        return array(
            self::BEFORE_CONTENT => $_helper->__(self::BEFORE_CONTENT_SHORT_LABEL),
            self::INSIDE_PRODUCT_PAGE => $_helper->__(self::INSIDE_PRODUCT_PAGE_SHORT_LABEL),
            self::INSTEAD_NATIVE_RELATED_BLOCK => $_helper->__(self::INSTEAD_NATIVE_RELATED_BLOCK_SHORT_LABEL),
            self::UNDER_NATIVE_RELATED_BLOCK => $_helper->__(self::UNDER_NATIVE_RELATED_BLOCK_SHORT_LABEL),
            self::CUSTOM => $_helper->__(self::CUSTOM_SHORT_LABEL)
        );
    }

}
