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
 */class AW_Autorelated_Adminhtml_AbstractblockController extends Mage_Adminhtml_Controller_Action {

    public function newConditionHtmlAction() {
        if ($this->_validateFormKey()) {
            $id = $this->getRequest()->getParam('id');
            $typeArr = $this->getRequest()->getParam('type') ? $this->getRequest()->getParam('type') : 'awautorelated-rule_condition_combine';
            $typeArr = explode('|', str_replace('-', '/', $typeArr));
            $type = $typeArr[0];
            $prefix = ($this->getRequest()->getParam('prefix')) ? $this->getRequest()->getParam('prefix') : 'conditions';
            $rule = ($this->getRequest()->getParam('rule')) ? base64_decode($this->getRequest()->getParam('rule')) : 'awautorelated/blocks';

            $model = Mage::getModel($type)
                    ->setId($id)
                    ->setType($type)
                    ->setRule(Mage::getModel($rule))
                    ->setPrefix($prefix);
            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
                $model->setJsFormObject($this->getRequest()->getParam('form'));
                $html = $model->asHtmlRecursive();
            } else {
                $html = '';
            }
            $this->getResponse()->setBody($html);
        }
    }

    /**
     * Returns true when admin session contain error messages
     */
    protected function _hasErrors() {
        return (bool) count($this->_getSession()->getMessages()->getItemsByType('error'));
    }

    /**
     * Set title of page
     *
     * @return AW_Autorelated_Adminhtml_AbstractblockController
     */
    protected function _setTitle($action) {
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Automatic Related Products 2'))->_title($this->__($action));
        }
        return $this;
    }

}
