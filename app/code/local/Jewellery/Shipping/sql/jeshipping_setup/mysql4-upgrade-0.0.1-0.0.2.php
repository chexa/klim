<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('jeshipping/method')} ADD COLUMN `display_order` INT(10) NULL DEFAULT '0' AFTER `delivery_time`

");

$installer->endSetup();