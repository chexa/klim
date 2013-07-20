<?php
if (Mage::getConfig()->getNode('modules/OrganicInternet_SimpleConfigurableProducts')) {
    class Jewellery_Catalog_Model_Product_Type_Configurable_Abstract extends OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Configurable {}
} else {
    class Jewellery_Catalog_Model_Product_Type_Configurable_Abstract extends Mage_Catalog_Model_Product_Type_Configurable {}
}

 class Jewellery_Catalog_Model_Product_Type_Configurable
    extends Jewellery_Catalog_Model_Product_Type_Configurable_Abstract
{
    /**
     * Retrieve related products collection
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable_Product_Collection
     */
    public function getUsedProductCollection($product = null)
    {
        $collection = parent::getUsedProductCollection($product);

        $collection->addAttributeToSort('material');
        $collection->addAttributeToSort('legierung');

        //$collection->addAttributeToSort('weight');

        $collection->addAttributeToSort('breite_mm');
        $collection->addAttributeToSort('hoehe_mm');
        $collection->addAttributeToSort('durchmesser_mm');

        $collection->addAttributeToSort('laenge_cm');

        $collection->addAttributeToSort('ringweite');

        $collection->addAttributeToSort('ansatzband_bandendbreite_mm');
        $collection->addAttributeToSort('ansatzband_anstossbreite_mm');

        $collection->addAttributeToSort('muenzfassung_innenmass_mm');
        $collection->addAttributeToSort('muenzfassung_innenmass_hoehe_mm');

        $collection->addAttributeToSort('motiv');
        
        $collection->addAttributeToSort('buchstabenanhaenger');
        
        $collection->addAttributeToSort('besatz');

        return $collection;
    }
}
