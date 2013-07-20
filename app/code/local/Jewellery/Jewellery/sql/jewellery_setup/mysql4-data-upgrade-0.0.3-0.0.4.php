<?php

$installer = $this;
/* @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');

$installer->startSetup();

$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'street_additional');
$attribute->setData('sort_order', 75)->save();

$attribute   = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'telephone_area_code');
$attribute->setData('sort_order', 119)->save();

$installer->endSetup();