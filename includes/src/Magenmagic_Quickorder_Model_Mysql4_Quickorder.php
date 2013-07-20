<?php

class Magenmagic_Quickorder_Model_Mysql4_Quickorder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the quickorder_id refers to the key field in your database table.
        $this->_init('quickorder/quickorder', 'quickorder_id');
    }
}