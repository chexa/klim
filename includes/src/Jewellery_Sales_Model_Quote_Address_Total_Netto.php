<?php
class Jewellery_Sales_Model_Quote_Address_Total_Netto extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $subtotal = $address->getBaseSubtotal();
        $shipping = $address->getShippingAmount();

        $address->setNettoSubtotal($subtotal + $shipping);

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {

        $address->addTotal(array(
            'code'  => $this->getCode(),
            'title' => Mage::helper('sales')->__('Netto Subtotal'),
            'value' => $address->getNettoSubtotal(),
        ));

        return $this;
    }
}