<?php
class Magenmagic_PromoBannerSlider_Block_Adminhtml_Info extends Mage_Core_Block_Template
{
    /**
     * Disable render if section is not magenmagicinfo
     * @return string
     */
    public function getTemplateFile()
    {
        $section = Mage::app()->getRequest()->getParam("section");
        if ( $section != "magenmagic_promosliderbanner" ) return "";
        return parent::getTemplateFile();
    }

}