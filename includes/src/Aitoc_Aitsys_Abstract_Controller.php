<?php

abstract class Aitoc_Aitsys_Abstract_Controller extends Mage_Core_Controller_Front_Action
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
    public function preDispatch()
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        return parent::preDispatch();
    }
    
}