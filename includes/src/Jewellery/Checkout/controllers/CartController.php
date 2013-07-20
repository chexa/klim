<?php

require_once 'Mage/Checkout/controllers/CartController.php';
/**
 * Shopping cart controller
 */
class Jewellery_Checkout_CartController extends Mage_Checkout_CartController
{
    /**
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $confParams = $params = $this->getRequest()->getParams();
        if (!(isset($confParams['qty']) && is_array($confParams['qty']) && isset($confParams['super_attribute']))) {
            parent::addAction();
            exit;
        }
//die('s');
        try {
            $parentProduct = $this->_initProduct();
		
            /**
             * Check product availability
             */
            if (!$parentProduct) {
                $this->_goBack();
                return;
            }
			
            if (!array_sum($confParams['qty'])) {
                $this->_getSession()->addNotice( $this->__('Please select quantity of product(s).'));
                $this->getResponse()->setRedirect($this->_getRefererUrl());
                return $this;
            }

            $relatedProduct = $this->getRequest()->getParam('related_product');

            $productIds = array_keys($confParams['qty']);

            $filter = new Zend_Filter_LocalizedToNormalized(
                array('locale' => Mage::app()->getLocale()->getLocaleCode())
            );

            /**
             * The main change is here: we try to add all selected products
             */
            foreach($productIds as $productId) {
                $params = array();

                if (isset($confParams['qty'][$productId]) && $confParams['qty'][$productId]) {
                    $params['qty'] = $filter->filter($confParams['qty'][$productId]);
                } else {
                    continue;
                }

                $params['uenc']             = $confParams['uenc'];
                $params['cpid']             = $confParams['product'];
                $params['product']          = $productId;
                $params['related_product']  = $relatedProduct;

                $cart   = $this->_getCart();
                $cart->init();

                $product = Mage::getModel('catalog/product')->load($productId);

                $cart->addProduct($product, $params);

                $cart->save();

                Mage::dispatchEvent('checkout_cart_add_product', array('product'=> $product));
            }

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            $this->_goBack();
        }
    }
}
