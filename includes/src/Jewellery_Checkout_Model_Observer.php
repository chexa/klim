<?php

class Jewellery_Checkout_Model_Observer
{
    public function checkout_type_onepage_save_order($observer)
    {
        $_order = $observer->getEvent()->getOrder();
        $_request = Mage::app()->getRequest();

        $_comments = strip_tags($_request->getParam('order_comment'));

        $_comments = '<b><u><i>' . $_comments . '</i></u></b>';

        if(!empty($_comments)){
            $_order
                    ->setCustomerNote($_comments)
                    ->addStatusHistoryComment($_comments)
                    ->save();
        }          

        return $this;
    }
}
 
