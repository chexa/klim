<?php

Mage::setRoot();
require_once Mage::getRoot().'/code/core/Mage/Core/Model/App.php';

$initProcessor = new Aitoc_Aitsys_Model_Init_Processor();

if ($initProcessor->isInstallerEnabled())
{
    Aitoc_Aitsys_Model_Rewriter_Autoload::register(true);
    $initProcessor->realize();
}

$_magIsDeveloperMode = Mage::getIsDeveloperMode();
Mage::reset();
Mage::setIsDeveloperMode($_magIsDeveloperMode);

Mage::register('aitsys_autoload_initialized', true);

Mage::register('aitsys_autoload_initialized_base',true);
