<?php

class AW_Autorelated_Block_Adminhtml_Blocks_Product_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        //$model = Mage::registry('productblock_data');
        $model = Mage::getModel('awautorelated/blocks')->load((int) $this->getRequest()->getParam('id'));
        //$model = Mage::getModel('sale/rule');
        $form = new Varien_Data_Form();
        //$form->setHtmlIdPrefix('viewed_');
        $helper = Mage::helper('awautorelated');

        $genearal_fieldset = $form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General')
                ));

        if ($model->getData('related_products'))
            $general_options = $model->getData('related_products')->getData('general');
        else
            $general_options = array();

        $genearal_fieldset->addField('general_options', 'text', array(
            'name' => 'general_options',
            'label' => $this->__('Number of products'),
            'title' => $this->__('Number of products')
        ))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate('aw_autorelated/render/attfield.phtml')->setValues($general_options));


        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('promo/fieldset.phtml')
                ->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
                    'form' => 'related_conditions_fieldset',
                    'prefix' => 'related',
                    'rule' => base64_encode('awautorelated/blocks_product_rulerelated'))));

        $fieldset = $form->addFieldset('related_conditions_fieldset', array(
                    'legend' => $this->__('Conditions (leave blank for all products)')
                ))->setRenderer($renderer);



        $rule = Mage::getModel('awautorelated/blocks_product_rulerelated');
        $rule->getConditions()->setJsFormObject('related_conditions_fieldset');
        $rule->getConditions()->setId('related_conditions_fieldset');

        $rule->setForm($fieldset);
        if ($model->getData('related_products') && is_array($model->getData('related_products')->getData('related'))) {
            $conditions = $model->getData('related_products')->getData('related');
            $conditions = $conditions['conditions'];
            $rule->getConditions()->loadArray($conditions, 'related');
            $rule->getConditions()->setJsFormObject('related_conditions_fieldset');
        }


        $fieldset->addField('related_conditions', 'text', array(
            'name' => 'related_conditions',
            'label' => $this->__('Apply To'),
            'title' => $this->__('Apply To'),
            'required' => true,
        ))->setRule($rule)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        if ($model->getData('related_products'))
            $pQty = $model->getData('related_products')->getData('product_qty');
        else
            $pQty = Mage::helper('awautorelated/config')->getNumberOfProducts();

        $model->setData('product_qty', $pQty);

        $other = $form->addFieldset('other', array(
            'legend' => $this->__('Other')
                ));

        $other->addField('product_qty', 'text', array(
            'name' => 'product_qty',
            'label' => $this->__('Number of products'),
            'title' => $this->__('Number of products'),
            'class' => 'validate-digits validate-greater-than-zero',
            'required' => true,
        ));
        
        $other->addField('randomize', 'select', array(
            'label' => $this->__('Random Order'),
            'title' => $this->__('Random Order'),
            'name' => 'randomize',
            'values' => array(
                array('value' => 1, 'label' => $this->__('Yes')),
                array('value' => 0, 'label' => $this->__('No'))
            )
        ));

        
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}