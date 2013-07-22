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
        $prepareSku = function (&$keywords, $replacedSku) {
            $keywords = preg_replace('/\,' . preg_quote($replacedSku) . '\,/', ',', $keywords);
            $keywords = preg_replace('/^' . preg_quote($replacedSku) . '/', '', $keywords);
            $keywords = preg_replace('/' . preg_quote($replacedSku) . '$/', '', $keywords);
            $keywords = preg_replace('/^\,/', '', $keywords);
            $keywords = preg_replace('/\,$/', '', $keywords);
            $keywords = preg_replace('/(\,){2}/', '', $keywords);
        };

        $sku1 = str_replace("/", " ", $sku);
        $sku2 = str_replace("/", "", $sku);

        $prepareSku($keywords, $sku1);
        if ($sku1 != $sku2) {
            $prepareSku($keywords, $sku2);
            $sku1 = $sku1 . "," . $sku2;
        }

        $keywords = $keywords . "," . $sku1;
        $product->setMetaKeyword($keywords);

        Mage::helper('jewellery_catalog')->buildRecommendedPrice($product);

        Mage::helper('jewellery_catalog')->buildConfigurableAttributes($product);

        return $this;
    }

}
 
