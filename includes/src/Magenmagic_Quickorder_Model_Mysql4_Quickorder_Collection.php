<?php

class Magenmagic_Quickorder_Model_Mysql4_Quickorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('quickorder/quickorder');
    }
}