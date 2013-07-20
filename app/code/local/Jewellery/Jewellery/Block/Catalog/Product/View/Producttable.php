<?php
class Jewellery_Jewellery_Block_Catalog_Product_View_Producttable extends Mage_Catalog_Block_Product_View
{
    protected $_tableColumns;
    protected $_tableHeader;
    protected $_tableData;

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getChildProducts()
    {
        $product = $this->getProduct();

        return Mage::helper('jewellery')->getChildProducts($product);
        
    }

    public function getConfigurableAttributes()
    {
        $product = $this->getProduct();

        $_attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        $_data = array();

        foreach ($_attributes as $a) {
            $_data[] = array(
                'code' => $a->getProductAttribute()->getAttributeCode(),
                'id' => $a->getProductAttribute()->getAttributeId(),
            );
        }

        return $_data;
    }

	public function formatDecimal($decimal, $decCount = 2)
	{
        return Mage::helper('jewellery_catalog')->formatDecimal($decimal, $decCount);
	}



}