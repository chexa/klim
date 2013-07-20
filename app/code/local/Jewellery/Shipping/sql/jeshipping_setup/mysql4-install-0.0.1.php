<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('jeshipping/method')};
CREATE TABLE {$this->getTable('jeshipping/method')} (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`price` DECIMAL(12,4) NOT NULL,
	`ensurance` DECIMAL(12,4) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`delivery_type` VARCHAR(50) NOT NULL,
	`delivery_time` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `id` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
ROW_FORMAT=DEFAULT
");

$installer->endSetup();
