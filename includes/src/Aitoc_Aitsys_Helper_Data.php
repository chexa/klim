<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitsys_Helper_Data extends Aitoc_Aitsys_Abstract_Helper
{
    public function getErrorText($code)
    {
        $args = func_get_args();
        try
        {
            $args[0] = Mage::getStoreConfig('aitsys/errors/'.$code.'/text');
        }
        catch (Mage_Core_Model_Store_Exception $exc)
        {
            $args[0] = $code;
        }
        return call_user_func_array(array($this,'__'),$args); 
    }
    
    public function getErrorCode($code)
    {
        return Mage::getStoreConfig('aitsys/errors/'.$code.'/code');
    }
    
    public function getModuleLicenseUpgradeLink( Aitoc_Aitsys_Model_Module $module , $onlyUrl = true )
    {
        if ($license = $module->getLicense())
        {
            $licenseId = $license->getLicenseId();
        }
        else
        {
            return '';
        }
        $url = $module->getStoreUrl().'aitcprod/license/upgrade/license_id/'.$licenseId;
        if ($onlyUrl)
        {
            return $url;
        }
        return '<a target="_blank" href="'.$url.'">'.$this->__('Buy license upgrade').'</a>';
    }
    
    public function getModuleSupportLink( Aitoc_Aitsys_Model_Module $module , $onlyUrl = true )
    {
        if ($module->getId())
        {
            $url = $module->getStoreUrl().'catalog/product/view/id/'.$module->getId()."#support";
        }
        else
        {
            $url = 'http://www.aitoc.com/';
        }
        if ($onlyUrl)
        {
            return $url;
        }
        return '<a target="_blank" href="'.$url.'">'.$this->__('Create support ticket').'</a>';
    }
    
}

?>