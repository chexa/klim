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
 */class AW_Autorelated_Model_Source_Customer_Groups extends AW_Autorelated_Model_Source_Abstract {

    public function toOptionArray() {
        $_helper = $this->_getHelper();
        $res = Mage::helper('customer')->getGroups()->toOptionArray();
        $found = false;
        foreach ($res as $group)
            if ($group['value'] == 0) {
                $found = true;
                break;
            }
        if (!$found)
            array_unshift($res, array(
                'value' => Mage_Customer_Model_Group::NOT_LOGGED_IN_ID,
                'label' => $_helper->__('Not registered')
                    )
            );

        array_unshift($res, array(
            'value' => Mage_Customer_Model_Group::CUST_GROUP_ALL,
            'label' => $_helper->__('All groups')
                )
        );

        return $res;
    }

}
