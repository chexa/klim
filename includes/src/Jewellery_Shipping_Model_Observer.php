<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

class Jewellery_Shipping_Model_Observer
{
	private $_totalBorder = 100;

    public function checkout_cart_save_after($observer)
    {
	
		$code = (string) Mage::app()->getRequest()->getParam('estimate_method');
		
        if (!empty($code)) {
            Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
	
        $cart = $observer->getEvent()->getCart();
		
        $quote = $cart->getQuote();

        $customer = Mage::helper('customer')->getCustomer();

        $customerAddress = $customer->getDefaultShippingAddress();

        if ($customerAddress) {

            $address = $quote->getShippingAddress();

            $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);

            $address->setCollectShippingRates(true)->collectShippingRates();
			
			//Check for Total > 100
			$checkTotal = $this->_checkTotal($address);

            if (!$address->getShippingMethod() || !$checkTotal) {
                //$shippingMethodId = $address->getCountryId() == 'DE' ? 'deutschepost_simple_1' : 'deutschepost_intl_8';
		
				if ( $shippingM = $this->_getShippingMethod($address->getCountryId()) )
				{
					$address
						->setShippingMethod($shippingM['id'])
						->setGrandTotal($shippingM['price'])
						->setBaseGrandTotal($shippingM['price'])
						->collectTotals();//save();
				}
            }

            $quote->collectTotals()->save();
        }

        return $this;
    }

	protected function _getShippingMethod($country_id)
	{
		$delType = $country_id == "DE" ? "simple" : "international";
		$_prefix = $country_id == "DE" ? "deutschepost_simple_" : "deutschepost_intl_";

        $total = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal();

		$collection = Mage::getSingleton('jeshipping/method')->getCollection()
			->setOrder('display_order', 'ASC')
			->addFieldToFilter('delivery_type', $delType)
			->setOrder('price', 'ASC')
			->setPageSize(1); 
			
		if ( $total < $this->_totalBorder )
		{
			$collection->addFieldToFilter('price', array("neq" => "0.0000"));
		}
			
		if ( sizeof($collection) == 0 ) return false;
		$item = $collection->getFirstItem();
		$id = $item->getId();
        
		return array("id"=>$_prefix.$id , "price"=>$item->getPrice());
	}
	
	// Check total value and unset shipping method
	protected function _checkTotal ($address)
	{
		if ( ! $shippingMethod = $address->getShippingMethod() ) return false;
		
		$shippingMethodID = (int) preg_replace("/\D+/","", $shippingMethod);
		$item = Mage::getSingleton('jeshipping/method')->load($shippingMethodID);
		
		if ( $item->getDeliveryType() == 'express') return true;
		
		$methodPrice = $item->getPrice();
		
        $total = Mage::getSingleton('checkout/session')->getQuote()->getSubtotal();
		
		if ( $total >= $this->_totalBorder && $methodPrice > 0 )
        {
            return false;
        }
		
        if ( $total < $this->_totalBorder && $methodPrice == 0 )
        {
            return false;
        }

        return true;
	}
	
    public function controller_action_postdispatch_checkout_cart_updatePost($observer)
    {
        $code = (string) Mage::app()->getRequest()->getParam('estimate_method');
		
        if (!empty($code)) {
            Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
    }


}
 
