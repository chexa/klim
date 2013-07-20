<?php

class OrganicInternet_SimpleConfigurableProducts_Sales_Model_Order_Item extends Mage_Sales_Model_Order_Item
{
    protected $_usedAttributes = null;

    public function getName()
    {
        $_request = $this->getProductOptionByCode('info_buyRequest');
        if (!$_request['cpid']) {
            return parent::getName();
        }

        $configurable = Mage::getModel('catalog/product')->load($_request['cpid']);

        return $configurable->getName();
    }

    public function getProductOptions()
    {
        $options = parent::getProductOptions();

        $_request = $options['info_buyRequest'];

        if (!$_request['cpid']) {
            return $options;
        }

        $configurable = Mage::getModel('catalog/product')->load($_request['cpid']);
        $simple = Mage::getModel('catalog/product')->load($this->getProductId());

        if (!$this->_usedAttributes) {
            $this->_usedAttributes = array();

            $attributes = $configurable->getTypeInstance()->getUsedProductAttributes();

            foreach($attributes as $attribute) {
                $this->_usedAttributes[] = array(
                    'label' => $attribute->getFrontendLabel() ,
                    'value' => $simple->getAttributeText($attribute->getName()),
                );
            }

            if (($vpe = Mage::helper('catalog/output')->productAttribute($simple, $simple->getVerpackungseinheitVe(), 'verpackungseinheit_ve')) > 1)  {
                $this->_usedAttributes[] = array(
                    'label' => Mage::helper('catalog/output')->__('Unit') ,
                    'value' => Mage::helper('catalog/output')->__('%d Pieces', $vpe),
                );
            }
        }

        if (isset($options['options'])) {
            $options['options'] = array_merge($options['options'], $this->_usedAttributes);
        } else {
            $options['options'] = $this->_usedAttributes;
        }


        return $options;
    }
}