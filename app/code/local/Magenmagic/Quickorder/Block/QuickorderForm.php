<?php
class Magenmagic_Quickorder_Block_QuickorderForm extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("magenmagic/quickorder/form.phtml");
    }
}