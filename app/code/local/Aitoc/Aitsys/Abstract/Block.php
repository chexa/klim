<?php

class Aitoc_Aitsys_Abstract_Block extends Mage_Core_Block_Template 
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    
    public function renderView()
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        return parent::renderView();
    }
    
    protected function _toHtml()
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        return parent::_toHtml();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
    
}