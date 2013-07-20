<?php

abstract class Aitoc_Aitsys_Abstract_Helper extends Mage_Core_Helper_Abstract 
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
    
    protected function _getUrl($route, $params = array())
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        return parent::_getUrl($route,$params);
    }
    
    public function __()
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        $args = func_get_args();
        $argsString = array();
        for ($i = 0 ; $i < count($args) ; ++$i)
        {
            $argsString[] = '$args['.$i.']';
        }
        eval('$result = parent::__('.join(",",$argsString).');');
        return $result;
    }
    
    /**
     * 
     * @return Mage_Adminhtml_Helper_Data
     */
    public function getAdminhtmlHelper()
    {
        return Mage::helper('adminhtml');
    }
    
}