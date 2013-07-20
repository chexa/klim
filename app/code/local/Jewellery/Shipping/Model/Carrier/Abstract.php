<?php

abstract class Jewellery_Shipping_Model_Carrier_Abstract
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    public function getCode($type = 'simple', $code='')
    {
        $methodsCollection = Mage::getModel('jeshipping/method')->getCollection();

        $methodsCollection
                ->addFieldToFilter('delivery_type', array('eq' => $type))
                ->addOrder('display_order', Varien_Data_Collection_Db::SORT_ORDER_ASC);

        $codes = array();

        foreach ($methodsCollection as $method)
        {
            $codes[$type][$method->getId()] = $method->getName();
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (isset ($codes[$type][$code])) {
            return $codes[$type][$code];
        }
    }


    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

        $methodsCollection = Mage::getModel('jeshipping/method')->getCollection();

        $methodsCollection->getSelect()->order('display_order ASC');

        $methodsCollection
                ->addFieldToFilter('delivery_type', array('eq' => $this->_type))
                ->addFieldToFilter('id', array('in' => $allowedMethods))
        ;
        
        foreach ($methodsCollection as $method) {
            $rate = Mage::getModel('shipping/rate_result_method');
            $rate->setCarrier($this->_code);
            $rate->setCarrierTitle($this->getConfigData('title'));
            $rate->setMethod($method->getId());
            $rate->setMethodTitle($method->getName());
            $rate->setCost($method->getPrice());
            $rate->setPrice($method->getPrice());
            $rate->setMethodDescription((int)$method->getEnsurance());
            $result->append($rate);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        foreach ($allowed as $k) {
            $arr[$k] = $k;
        }
        return $arr;
    }

}