<?php

class Jewellery_Jewellery_Model_Rewrite_CustomerEntityCustomer extends Mage_Customer_Model_Entity_Customer {

    public function loadByCustomerNumber(Mage_Customer_Model_Customer $customer, $number)
    {
        $collection = $customer->getResourceCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('customer_number', $number)
            ->setPage(1,1);

        // $store is ID or code for specific store you want to get data from
        //$collection->getEntity()->setStore($store);



        $loadedCustomer = current($collection->load()->getIterator());

         if (!$loadedCustomer) {
            $customer->setData(array());
        } else {
            $customer->setData($loadedCustomer->getData());
        }

        return $this;
    }

}
