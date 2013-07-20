<?php

class Jewellery_Jewellery_Model_Wysiwyg_Config extends Mage_Cms_Model_Wysiwyg_Config
{

	public function getConfig($data = array())
	{
		$config = parent::getConfig($data);
		$config->setData('content_css', Mage::getDesign()->getSkinUrl('css/tiny_mce/jewellery_content.css') .'?'. time() );
		return $config;
	}

}