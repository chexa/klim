<?php
if (Mage::getConfig()->getNode('modules/OrganicInternet_SimpleConfigurableProducts')) {
    class Jewellery_Catalog_Model_Product_Abstract extends OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product {}
} else {
    class Jewellery_Catalog_Model_Product_Abstract extends Mage_Catalog_Model_Product {}
}


class Jewellery_Catalog_Model_Product extends Jewellery_Catalog_Model_Product_Abstract
{
    public function cleanCache()
    {
        if (!$this->getSaveWhileImporting()) {
            Mage::app()->cleanCache('catalog_product_'.$this->getId());
        }
        return $this;
    }

    public function afterCommitCallback()
    {
        Mage_Core_Model_Abstract::afterCommitCallback();
        if (!$this->getSaveWhileImporting()) {
            Mage::getSingleton('index/indexer')->processEntityAction(
                $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
            );
        }
        return $this;
    }
}