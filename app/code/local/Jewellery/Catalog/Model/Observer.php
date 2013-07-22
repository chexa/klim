<?php

class Jewellery_Catalog_Model_Observer
{
    public function catalog_product_is_salable_after($observer)
    {
        $salable = $observer->getEvent()->getSalable();

        if (!Mage::helper('customer')->isLoggedIn()) {
            $salable->setIsSalable(false);
        }

        return $this;
    }

    public function catalog_product_save_before($observer)
    {
        $product = $observer->getEvent()->getProduct();

        $keywords = $product->getMetaKeyword();

        $sku = $product->getSku();
        $keywords = $keywords . "," . str_replace("/", " ", $sku) . "," . str_replace("/", "", $sku);
        $product->setMetaKeyword($keywords);

        Mage::helper('jewellery_catalog')->buildRecommendedPrice($product);

        Mage::helper('jewellery_catalog')->buildConfigurableAttributes($product);

        return $this;
    }

}
 
