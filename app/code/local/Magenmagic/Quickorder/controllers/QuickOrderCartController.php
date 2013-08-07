<?php 

ini_set('display_errors', 1);

include_once "Jewellery".DS."Checkout".DS."controllers".DS."CartController.php";

class Magenmagic_Quickorder_QuickOrderCartController extends Jewellery_Checkout_CartController
{

	protected $_newProducts = array();

	protected function _getConfigurableAttributes($product)
    {
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
  
	public function addFromQuickOrderAction()
	{
		$request  = $this->getRequest();
		$products = $request->getParam("qty");
		/*if ($request->getParam('ajax') == 1) {
			$this->_getSession()->setNoCartRedirect(true);
		}*/
		$request->setParam("qty", null);
		if ( !count($products) ) return false;
		
		$super_attribute = array();

		$iterator = 0;

		foreach( $products as $item=>$val )
		{
			$iterator++;
			if ( $iterator == sizeof($products) ) $this->_getSession()->unsNoCartRedirect();
			
			$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item); 
			//echo $parentIds[0] ;
			//echo '<br />';
			if ( ! count( $parentIds ) ) continue;
			$product = Mage::getModel('catalog/product')->load($parentIds[0]);
			$productID = $product->getId();
			if ( !$productID || ! $product->isAvailable() ) continue;
			$request->setParam("product", $productID);
			$request->setParam("qty", array($item=>$val));
            $this->_newProducts[] = $item;
			
		   $attributes = $this->_getConfigurableAttributes($product);
		   $superAttrHtml = '';
		   foreach ($attributes as $_a)
		   {
				$_aFunc = 'get' . ucfirst($_a['code']);
				$super_attribute[$item][$_a['id']] = $product->$_aFunc();
		   }


		   $request->setParam("super_attribute", $super_attribute);
		   parent::addAction();
		}

        $this->_getSession()->unsetData('newProducts');
        $this->_getSession()->setData('newProducts', $this->_newProducts);

		//exit;
	
	}

    protected function _goBack()
    {
        $request  = $this->getRequest();
        $isAjax = $request->getParam("ajax") == 1;
		if ($isAjax) {
			$responseData = array();
			$responseData['success'] = true;
			$response = $this->getResponse();
			$response->setHeader('HTTP/1.1 200 OK','');
			$response->setHeader('Content-type', 'application/json');

			$cartBlock = $this->getLayout()->createBlock('checkout/cart');
			$items = $cartBlock->getItems();
			$html = '';
			$newItem = null;
			foreach ($items as $item) {
				if (\in_array($item->getProduct()->getId(), $this->_newProducts)) {
					$item->isNewProduct = true;
					$newItem = $item;
					$html .= $cartBlock->getItemHtml($item);
				}
			}

			//Mage::getSingleton('checkout/session')->unsSuccess();
  			Mage::getSingleton("checkout/session")->getMessages(true);
 			
			$totalBlock = $this->getLayout()->createBlock('checkout/cart_totals');

			$responseData['html'] = $html;
			$responseData['totals'] = $totalBlock->renderTotals() . $totalBlock->renderTotals('footer');
			//$responseData['totals'] = $totalBlock->getHtml();
			$responseData['id'] = $newItem ? $newItem->getId() : null;

			$response->setBody(Mage::helper('core')->jsonEncode($responseData));
			$response->sendResponse();

			exit;
		}

        parent::_goBack();
    }
  
}
