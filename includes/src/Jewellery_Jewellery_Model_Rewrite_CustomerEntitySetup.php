<?php

class Jewellery_Jewellery_Model_Rewrite_CustomerEntitySetup extends Mage_Customer_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
        $defaultEntities = parent::getDefaultEntities();

        $customerAddressFields = array(
            'additional' => array(
                'label' => 'Additional Information',
                'required' => false,
                'sort_order' => 45
            ),
        );

        //$defaultEntities['customer_address']['attributes'] = array_merge($defaultEntities['customer_address']['attributes'], $customerAddressFields);

        print '<pre>';
        print_r($defaultEntities);exit;
        print '</pre>';

        return $defaultEntities;
    }
}