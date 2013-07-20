<?php

$installer = $this;
/* @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');

$installer->startSetup();

// remove old attribute
$installer->removeAttribute('customer_address', 'additional');

// add "street_additional" attribute
$installer->addAttribute('customer_address', 'street_additional', array(
	'label'		=> 'Street Additional Information',
	'type'		=> 'varchar',
	'input'		=> 'text',
	'visible'	=> true,
	'required'	=> true,
	'position'	=> 45,
	));


$attributes = array(
    'street_additional'
);

$defaultUsedInForms = array(
    'adminhtml_customer_address',
    'customer_address_edit',
    'customer_register_address'
);

foreach ($attributes as $attributeCode) {
    $attribute = $eavConfig->getAttribute('customer_address', $attributeCode);
    if (!$attribute) {
        continue;
    }
    $attribute->setData('used_in_forms', $defaultUsedInForms);
    $attribute->save();
}


$installer->endSetup();