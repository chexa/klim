<?php

class Jewellery_Catalog_Block_Product_Single extends Mage_Catalog_Block_Product_Abstract
{
    public function setProduct($product)
    {
        $this->setData('product', $product);
        return $this;
    }
}