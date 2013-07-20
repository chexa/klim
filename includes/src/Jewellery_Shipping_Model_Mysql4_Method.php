<?php
class Jewellery_Shipping_Model_Mysql4_Method extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('jeshipping/method', 'id');
    }
}
