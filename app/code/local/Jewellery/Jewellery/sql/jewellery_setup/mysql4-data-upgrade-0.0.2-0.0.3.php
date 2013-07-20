<?php

$installer = $this;
/* @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');

$installer->startSetup();

// add "street_additional" attribute
$installer->addAttribute('customer_address', 'telephone_area_code', array(
	'label'		=> 'Telephone Area Code',
	'type'		=> 'varchar',
	'input'		=> 'text',
	'visible'	=> true,
	'required'	=> true,
	'position'	=> 99,
	));


$attributes = array(
    'telephone_area_code'
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


/*
 *******************************
 * add customer attributes
 *******************************
 */
// add "street_additional" attribute
$installer->addAttribute('customer', 'customer_number', array(
	'label'		=> 'Customer Number',
	'type'		=> 'varchar',
	'input'		=> 'text',
	'visible'	=> true,
	'required'	=> false,
	'position'	=> 5,
	));

$installer->addAttribute('customer', 'customer_message', array(
	'label'		=> 'Customer Message',
	'type'		=> 'varchar',
	'input'		=> 'text',
	'visible'	=> true,
	'required'	=> false,
	'position'	=> 90,
	));

$attributes = array(
    'customer_number',
    'customer_message'

);

$defaultUsedInForms = array(
    'adminhtml_customer',
    'customer_account_create',
    'customer_account_edit',
);

foreach ($attributes as $attributeCode) {
    $attribute = $eavConfig->getAttribute('customer', $attributeCode);
    if (!$attribute) {
        continue;
    }
    $attribute->setData('used_in_forms', $defaultUsedInForms);
    $attribute->save();
}

$installer->endSetup();