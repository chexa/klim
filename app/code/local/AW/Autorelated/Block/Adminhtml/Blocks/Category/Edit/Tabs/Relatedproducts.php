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
 */class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_Relatedproducts extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::helper('awautorelated/forms')->getFormData($this->getRequest()->getParam('id'));
        if (is_array($_data)) {
            $_data = Mage::getModel('awautorelated/blocks')->setData($_data);
        } else if (!is_object($_data)) {
            $_data = Mage::getModel('awautorelated/blocks');
        }

        if ($_data->getRelatedProducts()) {
            $_data->setData('related_products_include', $_data->getData('related_products')->getData('include'));
            $_data->setData('related_products_count', $_data->getData('related_products')->getData('count'));
        }

        $_fieldset = $_form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General')
                ));

        $_fieldset->addField('related_products_include', 'select', array(
            'name' => 'related_products[include]',
            'label' => $this->__('Include'),
            'values' => Mage::getModel('awautorelated/source_block_category_include')->toOptionArray()
        ));

        if ($_data->getData('related_products_count') === null)
            $_data->setData('related_products_count', Mage::helper('awautorelated/config')->getNumberOfProducts());

        $_fieldset->addField('related_products_count', 'text', array(
            'name' => 'related_products[count]',
            'title' => $this->__('Number of products'),
            'label' => $this->__('Number of products'),
            'required' => true
        ));
        
        $_fieldset->addField('randomize', 'select', array(
            'label' => $this->__('Random Order'),
            'title' => $this->__('Random Order'),
            'name' => 'randomize',
            'values' => array(
                array('value' => 1, 'label' => $this->__('Yes')),
                array('value' => 0, 'label' => $this->__('No'))
            )
        ));
        
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('promo/fieldset.phtml')
                ->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
                    'form' => 'conditions_fieldset',
                    'prefix' => 'related',
                    'rule' => base64_encode('awautorelated/blocks_category_rule')
                )));

        $_fieldset = $_form->addFieldset('conditions_fieldset', array(
                    'legend' => $this->__('Conditions (leave blank for all products)')
                ))->setRenderer($renderer);

        /* Setup of the rule control */
        $model = Mage::getModel('awautorelated/blocks_category_rule');
        $model->setForm($_fieldset);
        $model->getConditions()->setJsFormObject('conditions_fieldset');
        if ($_data->getData('related_products') && is_array($_data->getData('related_products')->getData('conditions'))) {
            $conditions = $_data->getData('related_products')->getData('conditions');
            $model->getConditions()->loadArray($conditions, 'related');
            $model->getConditions()->setJsFormObject('conditions_fieldset');
        }

        $_fieldset->addField('conditions', 'text', array(
            'name' => 'related_conditions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $_form->setValues($_data);
    }

}
