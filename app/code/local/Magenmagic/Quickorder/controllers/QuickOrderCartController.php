<?php 

ini_set('display_errors', 1);

include_once "Jewellery".DS."Checkout".DS."controllers".DS."CartController.php";

class Magenmagic_Quickorder_QuickOrderCartController extends Jewellery_Checkout_CartController
{
  
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
		$request->setParam("qty", null);
		if ( !count($products) ) return false;
		
		$super_attribute = array();
		
		$iterator = 0;
		$this->_getSession()->setNoCartRedirect(true);
        $newProducts = array();
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
			if ( !$productID ) continue;
			$request->setParam("product", $productID);
			$request->setParam("qty", array($item=>$val));
            $newProducts[] = $item;
			
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
        $this->_getSession()->setData('newProducts', $newProducts);

		//exit;
	
	}
  
}