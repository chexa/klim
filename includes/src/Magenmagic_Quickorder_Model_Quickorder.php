<?php

class Magenmagic_Quickorder_Model_Quickorder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('quickorder/quickorder');
    }
}