<?php

require_once 'Mage/Catalog/controllers/ProductController.php';

class Czettner_Ajax_ProductController extends Mage_Catalog_ProductController {

    protected  $_product = null;

    protected function _initAction() {
        $this->_product = $this->_initProduct();
		$this->loadLayout();
		return $this;
	}

    public function quickViewAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('/');
        }
        $this->_initAction();

        $product = $this->_product;
        // Prepare helper and params

		$viewHelper = Mage::helper('catalog/product_view');
		$viewHelper->initProductLayout($product, $this);
        if ($product) {
            $this->getResponse()
                   /* ->appendBody($this->getLayout()
                            ->getBlock('head')
                            ->renderView())*/
                    ->appendBody($this->getLayout()
                            ->getBlock('product.info')
                            ->setProduct($product)
                            ->renderView());

        } else {
            echo Mage::helper('catalog')->__('Product not found');
        }
    }

    

}

