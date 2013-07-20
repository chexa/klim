<?php

class Mage_Catalog_Model_Convert_Adapter_Stockimport extends Mage_Catalog_Model_Convert_Adapter_Product
{

    protected $_opalnummerAttributeId = null;

    protected $_priceAttributeId = null;

    protected $_uvpAttributeId = null;

    protected $_vpeAttributeId = null;
    /**
    * Save product (import)
    * 
    * @param array $importData 
    * @throws Mage_Core_Exception
    * @return bool 
    */
    public function saveRow( array $importData )
    {
        // http://fishpig.co.uk/blog/magento-update-product-prices-globaly.html
        $opalNrField   = 'InterneNr';
        $qtyField   = 'Bestand';
        $priceField = 'Tagespreis';

        // check if SKU is defined in the file
        if (empty($importData[$opalNrField])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', $opalNrField);
            Mage::throwException($message);
        }

        // check if price is defined in the file
        if (!isset($importData[$priceField])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', $priceField);
            Mage::throwException($message);
        }

        // check if qty is defined in the file
        if (!isset($importData[$qtyField])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', $qtyField);
            Mage::throwException($message);
        }

        $storeId    = $this->getBatchParams('store');
        $opalNr     = $importData[$opalNrField];
        $qty        = (int)$importData[$qtyField];
        $price      = str_replace(',', '.', $importData[$priceField]);


        $this->log('--------------');
        $this->log('Importing Stock Opalnummer #' . $opalNr);

         $product = $this->_getProduct($opalNr, $storeId);

        if (!$product) {
            $this->log('Not found');
            return true;
        }

        $this->log('Found');

        if ($product['type_id'] != 'simple') {
            $this->log('Wrong type id');
            return true;
        }

        $productId      = $product['entity_id'];
        $configurableId = $product['parent_id'];
        $packs          = $product['packs'];

        //Beginn Klim's Edit Disable Pricecalculation
        //$recommendedPrice = Mage::helper('jewellery_catalog')->calculateRecommendedPrice($price, $packs);
         
         $recommendedPrice = 'Publikumspreis';
         // check if price is defined in the file
       
        if (!isset($importData[$recommendedPrice])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', $recommendedPrice);
            Mage::throwException($message);
        }
        $recommendedPrice =  str_replace(',', '.', $importData[$recommendedPrice]);
        // END Klim's Edit Disable Pricecalculation
        
        $this->_storeConfigurableRecommendedPrice($configurableId, $recommendedPrice);

        // update price
        $this->_getWriteConnection()->query("
          UPDATE catalog_product_entity_decimal val
          SET  val.value = '{$price}'
          WHERE  val.attribute_id = {$this->_getPriceAttributeId()} AND val.store_id = {$storeId} AND val.entity_id = {$productId}
        ");

        // update recommended price
        $this->_getWriteConnection()->query("
          UPDATE catalog_product_entity_varchar val
          SET  val.value = '{$recommendedPrice}'
          WHERE  val.attribute_id = {$this->_getUvpAttributeId()} AND val.store_id = {$storeId} AND val.entity_id = {$productId}
        ");

        $stockItemTable     = Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_item');
        $stockStatusTable   = Mage::getSingleton('core/resource')->getTableName('cataloginventory/stock_status');

        // update stock
        $this->_getWriteConnection()->query("UPDATE {$stockItemTable} s_i, {$stockStatusTable} s_s
            SET   s_i.qty = '{$qty}', s_i.is_in_stock = 1,
                  s_s.qty = '{$qty}', s_s.stock_status = 1
            WHERE s_i.product_id = {$productId} AND s_i.product_id = s_s.product_id ");

        /*

        $product->setStoreId($store->getId());
        $product->load($productId);

        $product->setPrice($importData[$priceField]);
        $product->setStockData(array('is_in_stock' => 1, 'qty' => $importData[$qtyField]));

        // this else is for check for if we can reimport products
        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->setSaveWhileImporting(true);

        $product->save();
        */

        return true;
    } 

    protected function log($str)
    {
        Mage::log($str, null, 'product_import.log');
        return $this;
    }

    public function finish()
    {
        $this->updateConfigurablePrices();

        // reindex prices only!
        $process = Mage::getModel('index/process')->load(2);
        $process->reindexAll();

        return $this;
    }

    protected function _getProduct($opalNr, $storeId)
    {
        // get product id by opalnummer
        $product = $this->_getReadConnection()->fetchAll("SELECT e.entity_id, e.type_id, p.parent_id, _table_vpe.value AS packs
            FROM catalog_product_entity AS e
            INNER JOIN catalog_product_entity_varchar AS _table_opalnummer
              ON (_table_opalnummer.entity_id = e.entity_id)
              AND (_table_opalnummer.attribute_id = {$this->_getOpalnummerAttributeId()})
              AND (_table_opalnummer.store_id = {$storeId})
            INNER JOIN catalog_product_entity_varchar AS _table_vpe
              ON (_table_vpe.entity_id = e.entity_id)
              AND (_table_vpe.attribute_id = {$this->_getVpeAttributeId()})
              AND (_table_vpe.store_id = {$storeId})
            LEFT JOIN catalog_product_super_link AS p
              ON e.entity_id = p.product_id
            WHERE (_table_opalnummer.value = {$opalNr}) LIMIT 1"
        );

        if (count($product)) {
            return $product[0];
        }

        return false;

    }

    protected function _getReadConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    protected function _getWriteConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    protected function _getOpalnummerAttributeId()
    {
        if (is_null($this->_opalnummerAttributeId)) {
            $this->_opalnummerAttributeId = $this->_getReadConnection()->fetchOne("SELECT attribute_id FROM eav_attribute eav WHERE eav.entity_type_id = 4 AND eav.attribute_code = 'opalnummer' LIMIT 1");
        }

        return $this->_opalnummerAttributeId;
    }

    protected function _getPriceAttributeId()
    {
        if (is_null($this->_priceAttributeId)) {
            $this->_priceAttributeId = $this->_getReadConnection()->fetchOne("SELECT attribute_id FROM eav_attribute eav WHERE eav.entity_type_id = 4 AND eav.attribute_code = 'price' LIMIT 1");
        }

        return $this->_priceAttributeId;
    }

    protected function _getUvpAttributeId()
    {
        if (is_null($this->_uvpAttributeId)) {
            $this->_uvpAttributeId = $this->_getReadConnection()->fetchOne("SELECT attribute_id FROM eav_attribute eav WHERE eav.entity_type_id = 4 AND eav.attribute_code = 'unverbindliche_preisempfehlung' LIMIT 1");
        }

        return $this->_uvpAttributeId;
    }

    protected function _getVpeAttributeId()
    {
        if (is_null($this->_vpeAttributeId)) {
            $this->_vpeAttributeId = $this->_getReadConnection()->fetchOne("SELECT attribute_id FROM eav_attribute eav WHERE eav.entity_type_id = 4 AND eav.attribute_code = 'verpackungseinheit_ve' LIMIT 1");
        }

        return $this->_vpeAttributeId;
    }

    protected function _storeConfigurableRecommendedPrice($configurableId, $price)
    {
        if (!$configurableId) {
            return $this;
        }

        $products = Mage::getSingleton('jewellery/session')->getInventoryConfigurableProducts();
        if (!$products) {
            $products = array();
        }

        $products[$configurableId][] = $price;

        Mage::getSingleton('jewellery/session')->setInventoryConfigurableProducts($products);

        return $this;
    }

    protected function updateConfigurablePrices($storeId = 0)
    {
        $products = Mage::getSingleton('jewellery/session')->getInventoryConfigurableProducts();

        $this->log('Updating Configurables...');
        $time = time();

        foreach($products as $productId => $prices) {
            $recommendedPrice = min($prices);

            if (!$recommendedPrice) {
                continue;
            }

            // update recommended price
            $this->_getWriteConnection()->query("
              UPDATE catalog_product_entity_varchar val
              SET  val.value = '{$recommendedPrice}'
              WHERE  val.attribute_id = {$this->_getUvpAttributeId()} AND val.store_id = {$storeId} AND val.entity_id = {$productId}
            ");

        }

        $products = Mage::getSingleton('jewellery/session')->setInventoryConfigurableProducts(null);

        $this->log('Done, time = ' . (time() - $time));
    }
}