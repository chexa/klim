<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('sales/quote_address')}  ADD COLUMN `street_additional` VARCHAR(50) NULL DEFAULT NULL AFTER `street`;
ALTER TABLE {$this->getTable('sales/quote_address')}  ADD COLUMN `telephone_area_code` VARCHAR(255) NULL DEFAULT NULL AFTER `country_id`;

");

$installer->endSetup();

