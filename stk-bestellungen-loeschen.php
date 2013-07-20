<?php
/**
 * @author Designer24.ch
 * Demobestellungen loeschen in Magento Commerce
 * 11. Januar 2012
 */

if (version_compare(phpversion(), '5.2.0', '<')===true) {
    echo  '<div style="font:14px/1.40em Verdana, Arial, Helvetica, sans-serif;"><div style="margin:0 0 30px 0; border-bottom:1px solid #ccc;"><h3 style="margin:0; font-size:1.5em; font-weight:normal; text-transform:none; text-align:left; color:#2f2f2f;">Die vorhandene PHP Version ist nicht die richtige fuer Magento.</h3></div><p>Magento unterstuetzt PHP 5.2.0 und hoeher. Klicken Sie auf <a href="http://www.magentocommerce.com/install" target="">wie kann ich Magento installieren</a>. Wenn Sie dies nicht moechten, koennen Sie Designer24.ch beauftragen. Klicken Sie dazu auf das <a href="http://designer24.ch/unternehmen/kontakt/">Kontakt Formular</a></p></div>';
    exit;
}

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$mageFilename = 'app/Mage.php';

if (!file_exists($mageFilename)) {
    echo $mageFilename." wurde nicht gefunden";
    exit;
}

require_once $mageFilename;

Mage::app();

$executionPath = null;

/*
 * Welche Magento Version?
 */
if (file_exists('LICENSE_EE.txt')) {
    $edition = 'EE';
}elseif (file_exists('LICENSE_PRO.html')) {
    $edition = 'PE';
} else {
    $edition = 'CE';    
}

if(($edition=='EE' && version_compare(Mage::getVersion(), '1.11.0.0.', '<')===true)
        || ($edition=='PE' && version_compare(Mage::getVersion(), '1.11.0.0.', '<')===true)
        || ($edition=='CE' && version_compare(Mage::getVersion(), '1.6.0.0.', '<')===true)
  ){
   $executionPath = 'alt'; 
} else {
   $executionPath = 'neu';  
}

$xpathEntity = 'global/models/sales_entity/entities//table';

if ($executionPath == 'alt') {
    $xpathResource = 'global/models/sales_mysql4/entities//table';
} else {
    $xpathResource = 'global/models/sales_resource/entities//table';
}

$salesEntitiesConf = array_merge(
    Mage::getSingleton('core/config')->init()->getXpath($xpathEntity), 
    Mage::getSingleton('core/config')->init()->getXpath($xpathResource)
);

$resource = Mage::getSingleton('core/resource');
$connection = $resource->getConnection('core_write');

$skipTables = array (
        $resource->getTableName('sales_order_status'),
        $resource->getTableName('sales_order_status_state'),
        $resource->getTableName('sales_order_status_label')
    );
$salesEntitiesConf = array_diff($salesEntitiesConf, $skipTables);


while ($table = current($salesEntitiesConf) ){
    $table = $resource->getTableName($table);
    
    if ($executionPath == 'alt') {
        $isTableExists = $connection->showTableStatus($table);
    } else {
        $isTableExists = $connection->isTableExists($table);
    }
    if ($isTableExists) {
        try {
            if ($executionPath == 'alt') {
                $connection->truncate($table);
            } else {
                $connection->truncateTable($table);
            }

            printf('Die Tabelle <i style="color:Chartreuse;">%s</i> wurde erfolgreich geleert.<br />', $table);
        } catch(Exception $e) {
            printf('Fehler <i style="color:crimson;">%s</i> verursachte ein Fehler bei der Leerung der Tabelle  <i style="color:crimson;">%s</i>.<br />', $e->getMessage(), $table);
        }
    }

    next($salesEntitiesConf);
}

exit('Alles erledigt. Nicht vergessen!!! Sie koennen die Datei <i style="color:Chartreuse;">magento-bestellungen-loeschen.php</i> nun in ihrem Webshop Verzeichnis loeschen.');