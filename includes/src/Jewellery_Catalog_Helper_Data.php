<?php
class Jewellery_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function formatDecimal($decimal, $decCount = 2)
    {
        return number_format($decimal, $decCount, ',', '');
    }



    public function buildRecommendedPrice(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeId() != 'simple') {
            return $this;
        }

        $product->setUnverbindlichePreisempfehlung($this->calculateRecommendedPrice($product->getPrice(), $product->getVerpackungseinheitVe()));

        return $this;
    }

    public function calculateRecommendedPrice($productPrice, $packs = 1, $taxPercent = 1.19, $PPFactor = 1)
    {
        // if ($productPrice >= 0.01 && $productPrice <= 10) {
        //     $PPFactor = 2.52;
        // } else if ($productPrice >= 10.01 && $productPrice <= 20) {
        //     $PPFactor = 2.35;
        // } else if ($productPrice >= 20.01 && $productPrice <= 50) {
        //     $PPFactor = 2.20;
        // } else if ($productPrice >= 50.01 && $productPrice <= 100) {
        //     $PPFactor = 2.15;
        // } else if ($productPrice >= 100.01) {
        //     $PPFactor = 2.05;
        // }

        // $PPFactor = 1;

        if ($productPrice >= 0.01 && $productPrice <= 10) {
            $PPFactor = 2.52;
        } else if ($productPrice >= 10.01 && $productPrice <= 15) {
            $PPFactor = 2.45;
        } else if ($productPrice >= 15.01 && $productPrice <= 20) {
            $PPFactor = 2.40;
        } else if ($productPrice >= 20.01 && $productPrice <= 25) {
            $PPFactor = 2.35;
        } else if ($productPrice >= 25.01 && $productPrice <= 30) {
            $PPFactor = 2.30;
        } else if ($productPrice >= 30.01 && $productPrice <= 40) {
            $PPFactor = 2.25;
        } else if ($productPrice >= 40.01 && $productPrice <= 50) {
            $PPFactor = 2.20;
        } else if ($productPrice >= 50.01 && $productPrice <= 75) {
            $PPFactor = 2.15;
        } else if ($productPrice >= 75.01 && $productPrice <= 100) {
            $PPFactor = 2.10;
        } else if ($productPrice >= 100.01) {
            $PPFactor = 2.05;
        }

        return round($productPrice * $PPFactor * $taxPercent / $packs, 1);
    }

    public function buildConfigurableAttributes(Mage_Catalog_Model_Product $product)
    {
        if ($product->getAlreadySaved()) {
            return $this;
        }

        $shouldBeSaved = false;


        if ($product->getTypeId() == 'simple') {
            if ($product->getSaveWhileImporting()) {
                // we don't want to do this while importing
                return $this;
            }
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild( $product->getId());
            if (count($parentIds)) {
                $parentId = $parentIds[0];
            } else {
                return $this;
            }

            $parentProduct = Mage::getModel('catalog/product')->load($parentId);

            $shouldBeSaved = true;

        } elseif ($product->getTypeId() == 'configurable') {
            $parentProduct = $product;
        } else {
            return $this;
        }

        $childProducts = Mage::helper('jewellery')->getChildProducts($parentProduct);

/*        // this is new product
        if (!$parentProduct->getId()) {
            $childIds = array_keys($parentProduct->getConfigurableProductsData());

            $childProducts = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('legierung')
                ->addAttributeToSelect('material')
                ->addAttributeToSelect('unverbindliche_preisempfehlung')
                ->addAttributeToFilter('entity_id', array('in' => $childIds));
        } else {
            $childProducts = Mage::helper('jewellery')->getChildProducts($parentProduct);
        }
*/
        if (!$childProducts) {
            return $this;
        }


        $recommendedPrice = array();
        $material = array();

        foreach ($childProducts  as $childProduct) {
            //print_r($childProduct->getData());exit;
            $recommendedPrice[] = $childProduct->getUnverbindlichePreisempfehlung();

            $tmpMaterial = $childProduct->getAttributeText('legierung');
            if (!$tmpMaterial) $tmpMaterial = Mage::helper('catalog/output')->productAttribute($childProduct, $childProduct->getMaterial(), 'material'); //$childProduct->getAttributeText('material');
            if (!$tmpMaterial) continue;

            $material[] = $tmpMaterial;
        }

        $parentProduct->setMaterialListview(implode(', ', array_unique($material)));
        $parentProduct->setUnverbindlichePreisempfehlung(min($recommendedPrice));
        $parentProduct->setAlreadySaved(true);

        if ($shouldBeSaved) {
            // this woll work only if we save parent product of current simple product
            $parentProduct->save();
        }

    }


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

    public function getUvpPriceHtml($product)
    {
        $html = '<span class="uvp-price">';

        if(is_callable(array($product, 'getMaxPossibleFinalPrice')) && is_callable(array($product, 'getFinalPrice'))) {
            if ($product->getMaxPossibleFinalPrice() != $product->getFinalPrice()) {
                $html .= '<span class="uvp-price-label">' . $this->__('Recommended Price From:') . '</span>';
            } else {
                $html .= '<span class="uvp-price-label">' . $this->__('Recommended Price:') . '</span>';
            }
        }

        $html .= ' <span class="uvp-price-value">' . $this->formatDecimal($product->getUnverbindlichePreisempfehlung()) . ' &#8364;</span>';

        $html .= '</span>';

        return $html;
    }
}
