<?php

class Aitoc_Aitsys_Model_Rewriter_Mage_Core_Model_Design_Package extends Mage_Core_Model_Design_Package
{
    /**
    * Changing path for patched layouts
    */
    public function getLayoutFilename($file, array $params=array())
    {
        $filename = parent::getLayoutFilename($file, $params);
        
        $filenameNew = str_replace(Mage::getBaseDir('app'), Mage::getBaseDir('var') . DS . 'ait_patch', $filename);
        if (file_exists($filenameNew))
        {
            $filename = $filenameNew;
        } 
        else
        {
            $filenameNew = str_replace(DS . 'base' . DS, DS . 'default' . DS, $filenameNew);
            if (file_exists($filenameNew))
            {
                $filename = $filenameNew;
            }
        }
        return $filename;
    }
}