<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Block_Rewriter_List extends Aitoc_Aitsys_Abstract_Adminhtml_Block
{
    protected $_extensions = array();
    protected $_groups     = array();
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aitsys/rewriter/list.phtml');
        $this->setTitle('Aitoc Rewrites Manager');
        
        $this->_prepareConflictGroups();
    }
    
    protected function _prepareLayout()
    {
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Save changes'),
                    'onclick'   => '$(\'rewritesForm\').submit()',
                    'class' => 'save',
                ))
        );
        
        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('adminhtml')->__('Reset rewrites order to default values'),
                    'onclick'   => 'if (confirm(\'' . $this->__('Are you sure want to reset rewrites order?') . '\')) $(\'rewritesResetForm\').submit()',
                    'class' => 'cancel',
                ))
        );
        
        return parent::_prepareLayout();
    }
    
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }
    
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }
    
    protected function _prepareConflictGroups()
    {
        $allExtensions    = array();
        $currentExtension = Mage::app()->getRequest()->getParam('extension');
        
        list($conflicts, ) = Mage::getModel('aitsys/rewriter_conflict')->getConflictList();
        
        // will combine rewrites by alias groups
        $groups = array();
        
        if (!empty($conflicts))
        {
            foreach($conflicts as $groupType => $modules) {
                $groupType = substr($groupType, 0, -1);
                foreach($modules as $moduleName => $moduleRewrites) {
                    foreach($moduleRewrites['rewrite'] as $moduleClass => $rewriteClasses) 
                    {
                        // building inheritance tree
                        $alias              = $moduleName . '/' . $moduleClass;
                        $baseClass          = Mage::getModel('aitsys/rewriter_class')->getBaseClass($groupType, $alias);
                        $inheritedClasses   = Mage::getModel('aitsys/rewriter_inheritance')->build($rewriteClasses, $baseClass, false);
                        $groups[$baseClass] = array_keys($inheritedClasses);
                        ksort($groups[$baseClass]);
                        $groups[$baseClass] = array_values($groups[$baseClass]);
                    }
                }
            }
            
            $order = Mage::helper('aitsys/rewriter')->getOrderConfig();

            foreach ($groups as $baseClass => $group)
            {
                $groups[$baseClass] = array_flip($group);
                $isCurrentFound = !(bool)$currentExtension;
                foreach ($groups[$baseClass] as $class => $i)
                {
                    if (isset($order[$baseClass][$class]))
                    {
                        $groups[$baseClass][$class] = $order[$baseClass][$class];
                    }
                    
                    // adding class to the list of all extensions
                    $key = substr($class, 0, strpos($class, '_', 1 + strpos($class, '_')));
                    //                                           ^^^^^^^^^^^^^^^^^^^^^  --- this is offset, so start searching second "_"
                    $allExtensions[] = $key;
                    if ($key == $currentExtension)
                    {
                        $isCurrentFound = true;
                    }
                }
                $groups[$baseClass] = array_flip($groups[$baseClass]);
                ksort($groups[$baseClass]);
                if (!$isCurrentFound || in_array($baseClass, Mage::helper('aitsys/rewriter')->getExcludeClassesConfig()))
                {
                    // will display conflicts only for groups where current selected extension presents
                    // exclude conflicts for excluded base Magento classes
                    unset($groups[$baseClass]);
                }
            }
        }
        
        $aModuleList   = Mage::getModel('aitsys/aitsys')->getAitocModuleList();
        $allExtensions = array_unique($allExtensions);
        foreach ($allExtensions as $key)
        {
            $moduleName = $key;
            foreach ($aModuleList as $moduleItem)
            {
                if ($key == $moduleItem->getKey())
                {
                    $moduleName = (string)$moduleItem->getLabel();
                }
            }
            $this->_extensions[$this->getExtensionUrl($key)] = $moduleName;
        }
        
        $this->_groups = $groups;
    }
    
    public function getConflictGroups()
    {
        return $this->_groups;
    }
    
    public function getExtensions()
    {
        return $this->_extensions;
    }
    
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
    public function getResetUrl()
    {
        return $this->getUrl('*/*/reset', array('_current'=>true));
    }
    
    public function getSelfUrl()
    {
        return $this->getUrl('*/*/*', array('_current'=>true));
    }
    
    public function getExtensionUrl($extension)
    {
        if ($extension)
        {
            return $this->getUrl('*/*/*', array('extension' => $extension));
        }
        return $this->getUrl('*/*/*');
    }
    
    public function getExcludedClasses()
    {
        $classes = Mage::helper('aitsys/rewriter')->getExcludeClassesConfig();
        $classes = implode("\n",$classes);
        return $classes;
    }
}
