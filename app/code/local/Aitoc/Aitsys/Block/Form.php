<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Block_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('module_list', array(
            'legend' => Mage::helper('adminhtml')->__('Enable/Disable Module List')
        ));

        $aModuleList = Mage::getModel('aitsys/aitsys')->getAitocModuleList();

        $elementRenderer = $this->getLayout()->createBlock('aitsys/form_element_renderer');
        /* @var $elementRenderer Aitoc_Aitsys_Block_Form_Element_Renderer */ 

        if ($aModuleList)
        {
            foreach ($aModuleList as $module) 
            {
                /* @var $module Aitoc_Aitsys_Model_Module */
                $aModule = $module;
                $label = $aModule['label'].($module->getVersion()?' v'.$module->getVersion():'');
                if ($aModule['access'] || !$module->isAvailable())
                {
                    if ($module->isAvailable())
                    {
                        $fieldset->addField('hidden_enable_'.$aModule['key'], 'hidden', array(
                            'name'=>'enable['.$aModule['key'].']',
                            'value'=>0,
                        ));
                        
                        $fieldset->addField('enable_'.$aModule['key'], 'checkbox', array(
                            'name'=>'enable['.$aModule['key'].']',
                            'label'=>$label,
                            'value'=>1,
                            'checked'=>$aModule['value'],
                            'module' => $module
                        ))->setRenderer($elementRenderer);
                    }
                    else
                    {
                        $fieldset->addField('ignore_'.$aModule['key'], 'note', array(
                            'name'=>'ignore['.$aModule['key'].']',
                            'label'=>$label,
                            'text'=> '',
                        	'module' => $module
                        ))->setRenderer($elementRenderer);
                    }
                }
                else 
                {
                    $sMessage = 'File does not have write permissions: %s';
                    $fieldset->addField('ignore_'.$aModule['key'], 'note', array(
                        'name'=>'ignore['.$aModule['key'].']',
                        'label'=>$label,
                        'text'=> '<ul class="messages"><li class="error-msg"><ul><li>' . Mage::helper('adminhtml')->__($sMessage, $aModule['file']) . '</li></ul></li></ul>'
                    ));
                }
            }
        }

        $this->setForm($form);

        return $this;
    }
    
}


