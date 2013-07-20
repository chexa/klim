<?php

class Aitoc_Aitsys_Abstract_Adminhtml_Block extends Mage_Adminhtml_Block_Template
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
    
}