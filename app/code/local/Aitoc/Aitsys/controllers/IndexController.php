<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_IndexController extends Aitoc_Aitsys_Abstract_Adminhtml_Controller
{
    
    public function preDispatch()
    {
        $result = parent::preDispatch();
        $this->tool()->setInteractiveSession($this->_getSession());
        if ($this->tool()->platform()->isBlocked() && 'error' != $this->getRequest()->getActionName())
        {
            $this->_forward('error');
        }
        return $result;
    }
    
    public function errorAction()
    {
        $this->loadLayout()->_setActiveMenu('system/aitsys');
        $this->renderLayout();
    }
    
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/aitsys')
            ->_addContent($this->getLayout()->createBlock('aitsys/edit')->initForm());
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }
    
    public function saveAction() {
        
        if ($data = $this->getRequest()->getPost('enable')) 
        {
            if ($aErrorList = Mage::getModel('aitsys/aitsys')->saveData($data))
            {
                $aModuleList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();
                
                foreach ($aErrorList as $aError)
                {
                    $this->_getSession()->addError($aError);
                }
                if ($notices = Mage::getModel('aitsys/aitpatch')->getCompatiblityError($aModuleList))
                {
                    foreach ($notices as $notice)
                    {
                        $this->_getSession()->addNotice($notice);
                    }
                }
                /*if (Mage::registry('aitsys_patch_incompatible_files'))
                {
                    $incompatibleList = Mage::registry('aitsys_patch_incompatible_files');

                    $this->_getSession()->addNotice('
                    You can try to fix the above mentioned error yourself. But this will involve opening Magento and Module files via FTP and editing code in them. <br />
                    Or if you don\'t feel confident you can post a support ticket. <br />
                    Note, that if you are having problems with more than one Module and you would like to post a support ticket, please post all the Modules in one ticket.
                    ');
                    $this->_getSession()->addNotice('The following Module(s) encountered the error:');
                    foreach ($incompatibleList as $mod => $modPatches)
                    {
                        $key = '';
                        if (isset($modPatches[0]['modkey']))
                        {
                            $key = $modPatches[0]['modkey'];
                        }
                        $module = $this->tool()->platform()->getModule($key);
                        $supportLink = '#';
                        if ($module->getId())
                        {
                            $supportLink = Mage::helper('aitsys')->getModuleSupportLink(true);
                        }
                        
                        $moduleName = '';
                        foreach ($aModuleList as $moduleItem)
                        {
                            if ($key == $moduleItem->getKey())
                            {
                                $moduleName = (string)$moduleItem->getLabel();
                            }
                        }
                        
                        $this->_getSession()->addNotice('Module ' . $moduleName . '. <a href="' . $this->getUrl('*//*patch/instruction', array('mod' => $mod)) . '">Read the Guide</a> 
                                                            or <a href="' . $supportLink . '">Post a support ticket</a>.');
                    }
                }*/
            }
            else 
            {
                $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__('Module settings saved successfully'));
            }
        }
        
        $this->_redirect('*/*');
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/aitsys');
    }
    
    
}

?>