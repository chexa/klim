<?php

if (Mage::getConfig()->getNode('modules/OrganicInternet_SimpleConfigurableProducts')) {
    class Jewellery_Jewellery_Block_Rewrite_CatalogProductViewAttributes_Abstract extends OrganicInternet_SimpleConfigurableProducts_Catalog_Block_Product_View_Attributes {}
} else {
    class Jewellery_Jewellery_Block_Rewrite_CatalogProductViewAttributes_Abstract extends Mage_Catalog_Block_Product_View_Attributes {}
}

class Jewellery_Jewellery_Block_Rewrite_CatalogProductViewAttributes extends  Mage_Catalog_Block_Product_View_Attributes
{
    public function getAdditionalData(array $excludeAttr = array())
    {
        $data = array();
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    continue;
                    //$value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    continue;
                    //$value = Mage::helper('catalog')->__('No');
                } elseif ((string)$value == Mage::helper('catalog')->__('No')) {
                    continue;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    continue;
                    //$value = Mage::app()->getStore()->convertPrice($value, true);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code'  => $attribute->getAttributeCode()
                    );
                }
            }
        }
        return $data;
    }
}