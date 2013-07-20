<?php
class Magenmagic_Platform_Block_Platform extends Mage_Core_Block_Template
{

    protected $_validPrefix = 'Magenmagic_';

    /**
     * Disable render if section is not magenmagicinfo
     * @return string
     */
    public function getTemplateFile()
    {
        $section = Mage::app()->getRequest()->getParam("section");
        if ( $section != "magenmagicinfo" ) return "";
        return parent::getTemplateFile();
    }

    public function _beforeToHtml()
    {
        $this->installedModules = $this->getModuleList();
        return parent::_beforeToHtml();
    }

    public function getModuleList()
        {
            $modules = (array)Mage::getConfig()->getNode('modules')->children();
            foreach ($modules as $moduleId=>$moduleInfo) {
            	if (!$this->isValidModule($moduleId))
                    unset($modules[$moduleId]);
                else
                    $modules[$moduleId]->id = $moduleId;
            }
            return $modules;
        }

    public function isValidModule($moduleId)
    {
        if (0 === strpos($moduleId,$this->_validPrefix))
        {
            return true;
        }
        return false;
    }

}