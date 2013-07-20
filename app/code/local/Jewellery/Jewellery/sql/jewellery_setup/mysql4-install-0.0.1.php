<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('customer_address', 'additional', array(
	'label'		=> 'Additional Information',
	'type'		=> 'varchar',
	'input'		=> 'text',
	'visible'	=> true,
	'required'	=> true,
	'position'	=> 45,
	));


$installer->endSetup(); 