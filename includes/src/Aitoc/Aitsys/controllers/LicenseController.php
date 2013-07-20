<?php

class Aitoc_Aitsys_LicenseController extends Aitoc_Aitsys_Abstract_Adminhtml_Controller
{
    
    protected $_usedModuleName = 'aitsys';
    
    protected $_prepared = false;
    
    public function preDispatch()
    {
        $result = parent::preDispatch();
        $this->tool()->setInteractiveSession($this->_getSession());
        if ($this->tool()->platform()->isBlocked() && 'error' != $this->getRequest()->getActionName())
        {
            $this->_forward('error','index');
        }
        return $result;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_LicenseController
     */
    protected function _prepare()
    {
        if (!$this->_prepared)
        {
            $key = $this->getRequest()->getParam('modulekey');
            $this->tool()->platform()->setData('mode', 'live');
            Mage::register('aitoc_module',$this->tool()->platform()->getModule($key));
            $this->_prepared = true;
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return Mage::registry('aitoc_module');
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License
     */
    public function getLicense()
    {
        return $this->getModule()->getLicense();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_LicenseController
     */
    protected function _prepareLayout()
    {
        $this->_prepare()->loadLayout()->_setActiveMenu('system/aitsys')
        ->_addContent($this->getLayout()->createBlock('aitsys/manage_widget'));
        $this->getLayout()->getBlock('head')->addCss('aitoc/aitsys.css');
        return $this;
    }
    
    public function deleteAction()
    {
        $this->_prepare();
        $license = $this->getLicense();
        $license->uninstall();
        if (!$license->isUninstalled())
        {
            if ($this->getModule()->produceErrors($this,$this->_getSession()))
            {
                $this->_redirect('*/*/manage',array('modulekey' => $this->getModule()->getKey()));
                return;
            }
        }
        $this->_getSession()->addSuccess($this->__('License deleted, `%s` module uninstalled.',$this->getModule()->getLabel()));
        $this->_redirect('*');
    }
    
    public function upgradeAction()
    {
        $this->_prepare();
        $this->getLicense()->upgrade();
        if ($this->getModule()->produceErrors($this,$this->_getSession()))
        {
            $this->_redirect('*/*/manage',array('modulekey' => $this->getModule()->getKey()));
        }
        else
        {
            $this->_getSession()->addSuccess($this->__('New license for `%s` installed.',$this->getModule()->getLabel()));
            $this->_redirect('*/*/manage',array('modulekey' => $this->getModule()->getKey()));
        }
    }
    
    public function installAction()
    {
        $this->_prepare();
        $license = $this->getLicense();
        if ($license->install()->isInstalled())
        {
            $install = $license->getInstall();
            if ($install->isInstalled())
            {
                $this->_getSession()->addSuccess($this->__('License and module %s installed.',$this->getModule()->getLabel()));
            }
            else 
            {
                $this->_getSession()->addWarning($this->__('License of %s module has been installed.',$this->getModule()->getLabel()));
                $this->getModule()->produceErrors($this,$this->_getSession());
                $aModuleList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();
                if ($notices = Mage::getModel('aitsys/aitpatch')->getCompatiblityError($aModuleList))
                {
                    foreach ($notices as $notice)
                    {
                        $this->_getSession()->addNotice($notice);
                    }
                }
            }
            $this->_redirect('*');
        }
        else
        {
            $this->getModule()->produceErrors($this, $this->_getSession());
            $this->getRequest()->setParam('confirmed',true);
            $this->_prepareLayout()->renderLayout();
        }
    }
    
    public function confirmAction()
    {
        $platform = $this->tool()->platform(); 
        $this->_prepare();
        if (!$platform->isModePresetted())
        {
            $testMode = 'test' == $this->getRequest()->getParam('installation_type');
            $platform->setTestMode($testMode);
            $platform->save();
        }
        $this->_redirect('*/*/manage',array(
            'modulekey' => $this->getModule()->getKey() ,
            'confirmed' => true
        ));
    }
    
    public function manageAction()
    {
        $this->_prepareLayout()->renderLayout();
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