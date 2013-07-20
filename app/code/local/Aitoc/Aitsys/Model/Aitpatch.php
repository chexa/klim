<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Model_Aitpatch extends Mage_Eav_Model_Entity_Attribute
{
    private $_aPatch = array();
    
    protected $_patchedTemplatesDir     = '';
    
    public function __construct()
    {
        $this->_patchedTemplatesDir = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'ait_patch' . DIRECTORY_SEPARATOR;
    }
    
    public function applyPatchDryrun()
    {
        return $this->applyPatch(true);
    }
    
    public function getCompatiblityError( $aModuleList )
    {
        $notices = array();
        if (Mage::registry('aitsys_patch_incompatible_files'))
        {
            $service = Aitoc_Aitsys_Abstract_Service::get();
            $incompatibleList = Mage::registry('aitsys_patch_incompatible_files');

            $notices[] = '
            You can try to fix the above mentioned error yourself. But this will involve opening Magento and Module files via FTP and editing code in them. <br />
            Or if you don\'t feel confident you can post a support ticket. <br />
            Note, that if you are having problems with more than one Module and you would like to post a support ticket, please post all the Modules in one ticket.
            ';
            $notices[] = 'The following Module(s) encountered the error:';
            foreach ($incompatibleList as $mod => $modPatches)
            {
                $key = '';
                if (isset($modPatches[0]['modkey']))
                {
                    $key = $modPatches[0]['modkey'];
                }
                $module = $service->platform()->getModule($key);
                $supportLink = Mage::helper('aitsys')->getModuleSupportLink($module,true);
                $moduleName = '';
                foreach ($aModuleList as $moduleItem)
                {
                    if ($key == $moduleItem->getKey())
                    {
                        $moduleName = (string)$moduleItem->getLabel();
                    }
                }
                
                $notices[] = 'Module ' . $moduleName . '. <a href="' . 
                Mage::helper('adminhtml')->getUrl('*/patch/instruction', array('mod' => $mod)) . 
                '">Read the Guide</a> 
                                                    or <a href="' . $supportLink . '">Post a support ticket</a>.';
            }
        }
        return $notices;
    }
    
    protected function _getModuleKey($alias)
    {
        if ($enable = Mage::app()->getRequest()->getPost('enable'))
        {
            $keys = array_keys($enable);
            if (is_array($keys) && !empty($keys))
            {
                foreach ($keys as $modKey)
                {
                    if (false !== strpos($modKey, $alias))
                    {
                        return $modKey;
                    }
                }
            }
        }
        else
        {
            $service = Aitoc_Aitsys_Abstract_Service::get();
            foreach ($service->platform()->getModules() as $module)
            {
                $key = $module->getKey();
                if (false !== strpos($key, $alias))
                {
                    return $key;
                }
            }
        }
        return '';
    }
    
    public function applyPatch($bDryRun = false)
    {
        $oConfig     = Mage::getConfig();
        $aPatchFiles = $this->_getPatchFiles();
        $oPatcher    = Mage::getModel('aitsys/aitfilepatcher');

		$oFileSys    = Mage::getModel('aitsys/aitfilesystem');

        $aErrors = array();
        $uncompatibleList = array();
        
        foreach ($aPatchFiles as $sFile => $aParams)
        {
            $sPatch = '';
            $aModules = $aParams['modules'];
            $currentModulePath = '';
            $currentModuleName = '';
            foreach ($aModules as $sMod => $sModPathToFile)
            {
                $patchFile = $oFileSys->getPatchFilePath($sFile, $sMod . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR);
                if (!$patchFile->getIsError())
                {
                    $sPatch .= file_get_contents($patchFile->getFilePath());
                }

                $currentModulePath = $sMod;
                $currentModuleName = str_replace(array('/','\\'),'_',substr($sMod, 6 + strrpos($sMod, 'local')));
            }
            $oPatcher->parsePatch($sPatch);
            
            // now will check if we can patch current file
            #$sFileToPatch = $oConfig->getOptions()->getAppDir() . '/' . substr(str_replace('_', '/', $sFile), 0, strpos($sFile, '.patch'));
#            $sFileToPatch = $oConfig->getOptions()->getAppDir() . '/' . substr(str_replace('_', '/', $sFile), 0, strpos($sFile, '.patch'));
            $sFileNameToPatch = str_replace('--', '/', $sFile);
            
            $sFileToPatch = $oConfig->getOptions()->getAppDir() . '/' . substr($sFileNameToPatch, 0, strpos($sFileNameToPatch, '.patch'));
            if (!file_exists($sFileToPatch) && $aParams['optional'])
            {
                continue;
            }

            if (!$oPatcher->canApplyChanges($sFileToPatch))
            {
                $aErrors[] = array('file' => $sFileToPatch, 'type' => 'file_uncompatible');
                // adding session data for instructions page
                $uncompatibleList[$currentModuleName][] = array(
                    'file'      => $sFileToPatch,
                    'mod'       => $currentModulePath,
                    'patchfile' => $sFile,
                    'modkey'    => $this->_getModuleKey($currentModuleName),
                );
            }
            
            if (!$bDryRun)
            {
                // apply patch
                $sApplyPatchTo = $oFileSys->makeTemporary($sFileToPatch);
                $oPatcher->applyPatch($sApplyPatchTo);
                foreach ($aModules as $sMod => $sModPathToFile)
                {
#                    $oFileSys->cpFile($sApplyPatchTo, $oConfig->getOptions()->getAppDir() . '/' . str_replace('_', '/', $sModPathToFile));
                    $oFileSys->cpFile($sApplyPatchTo, $this->_patchedTemplatesDir . $sModPathToFile);
                }
                $oFileSys->rmFile($sApplyPatchTo);
            }
        }
        if ($bDryRun)
        {
            if ($uncompatibleList)
            {
                Mage::getSingleton('adminhtml/session')->setData('aitsys_patch_incompatible_files', $uncompatibleList);
                Mage::unregister('aitsys_patch_incompatible_files');
                Mage::register('aitsys_patch_incompatible_files', $uncompatibleList);
            }
            return $aErrors;
        }
        return true;
    }
    
    public function setPatchFiles($aPatch)
    {
        $this->_aPatch = $aPatch;
    }
    
    final private function _getPatchFiles()
    {
        return $this->_aPatch;
    }
    
}
