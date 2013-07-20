<?php

class Jewellery_Jewellery_Model_Observer
{
    public function customer_load_after($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if (strpos($customer->getEmail(), '@standard-schmuck.de') !== FALSE) {
            $customer->setEmail();
        }

        return $this;
    }
}
 
