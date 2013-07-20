<?php

class Jewellery_Sales_Block_Order_Netto extends Mage_Core_Block_Template
{
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        $nettoSubtotal = new Varien_Object(array(
                    'code'  => 'netto',
                    'value' => $source->getSubtotal() + $source->getShippingAmount(),
                    'label' => $this->__('Netto Subtotal')
                ));

        $parent->addTotal($nettoSubtotal, 'shipping');

        return $this;
    }
}