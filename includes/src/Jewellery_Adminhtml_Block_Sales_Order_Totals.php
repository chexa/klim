<?php

class Jewellery_Adminhtml_Block_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{

    protected function _initTotals()
    {
        parent::_initTotals();

        $this->_totals['netto'] = new Varien_Object(array(
            'code'      => 'netto',
            'value'     => ($this->getSource()->getSubtotal() + $this->getSource()->getShippingAmount()),
            'base_value'=> ($this->getSource()->getSubtotal() + $this->getSource()->getShippingAmount()),
            'label'     => $this->helper('sales')->__('Netto Subtotal')
        ));

        return $this;
    }
}
