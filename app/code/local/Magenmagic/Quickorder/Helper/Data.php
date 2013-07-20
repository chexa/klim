<?php

class Magenmagic_Quickorder_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getInventoryData($product)
    {
        if (!$product) {
            throw new Exception('No product');
        }

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $stockStatus = $stockItem->getIsInStock();
        $productQty = $stockStatus ? $stockItem->getQty() : 0;
        $stockStatusCss = ($stockStatus && $productQty > 3) ? 'auf-lager' : (($stockStatus && $productQty > 0) ? 'begrenzt-auf-lager' : 'nicht-auf-lager');

        // reset product qty to 12
        if ($productQty > 12) {
            $productQty = 12;
        }

        return array(
            'status' => $stockStatusCss,
            'qty' => $productQty
        );
    }

    public function getHtmlQuantity ($_product)
    {
        $html = '<span class="qty-minus" onClick="javascript:del(\'qty-'.$_product->getId().'\')"></span>
                 <input size="1" value="1"  name="qty['.$_product->getId().']" id="qty-'.$_product->getId().'" class="product-qty" />
                 <span class="qty-plus" onClick="javascript:add(\'qty-'.$_product->getId().'\')"></span>';
        return $html;
    }

}