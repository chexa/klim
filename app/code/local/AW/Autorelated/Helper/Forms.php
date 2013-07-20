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
 */class AW_Autorelated_Helper_Forms extends Mage_Core_Helper_Abstract {
    const AW_APR_FORM_DATA_KEY = 'awautorelated_formdata';

    public function setFormData($data) {
        if (!($data instanceof Varien_Object))
            $data = new Varien_Object($data);
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_APR_FORM_DATA_KEY);
        if (!is_array($_formData))
            $_formData = array();
        $_formData[$data->getId() ? $data->getId() : -1] = $data;
        Mage::getSingleton('adminhtml/session')->setData(self::AW_APR_FORM_DATA_KEY, $_formData);
    }

    public function getFormData($id = null) {
        if (!$id)
            $id = -1;
        $_formData = Mage::getSingleton('adminhtml/session')->getData(self::AW_APR_FORM_DATA_KEY);
        return $_formData && isset($_formData[$id]) ? $_formData[$id] : null;
    }

}
