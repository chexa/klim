<?php

class Czettner_Ajax_Block_Product extends Mage_Catalog_Block_Product
{
    
    private $product;

    protected function _construct()
    {
        parent::_construct();
        /*$this->setTemplate('czettner_ajax/product.phtml');*/
        $this->setTemplate('czettner_ajax/quickview.phtml');

    }

    public function getSubmitUrl($product, $additional = array())
    {
        return $this->getUrl("checkout/cart/add");
    }

     /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
       
        return parent::_prepareLayout();
    }

    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
   /* public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }*/

 /*   protected function _toHtml() {
        return parent::_toHtml();
    }*/
    
    public function setProduct($product) {
        $this->product = $product;
        return $this;
    }
    
    public function getProduct() {
        return $this->product;
    }
}
