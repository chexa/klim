<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `magenmagic_promobannerslider`;
CREATE TABLE IF NOT EXISTS `magenmagic_promobannerslider` (
  `promobannerslider_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`promobannerslider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `magenmagic_promobannerslider_images`;
CREATE TABLE IF NOT EXISTS `magenmagic_promobannerslider_images` (
  `bannersimages_id` int(10) NOT NULL AUTO_INCREMENT,
  `path` varchar(150) DEFAULT NULL,
  `thumb` varchar(150) DEFAULT NULL,
  `link` varchar(150) DEFAULT NULL,
  `date_create` datetime DEFAULT NULL,
  PRIMARY KEY (`bannersimages_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;


DROP TABLE IF EXISTS `magenmagic_promobannerslider_links`;
CREATE TABLE IF NOT EXISTS `magenmagic_promobannerslider_links` (
  `links_id` int(10) NOT NULL AUTO_INCREMENT,
  `gallery_id` int(10) NOT NULL DEFAULT '0',
  `photo_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`links_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

    ");

$installer->endSetup(); 