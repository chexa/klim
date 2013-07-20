<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Rewrite/CatalogModelResourceEavMysql4CategoryTree.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ MqZDpchjoUIjUsDg('de40c2bed4d65161465e1ee1b429cf9d'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Model_Rewrite_CatalogModelResourceEavMysql4CategoryTree extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
{
    protected function _updateAnchorProductCount(&$data)
    {
        foreach ($data as $key => $row) {
        	if (isset($row['is_anchor']))
        	{
	            if (0 === (int)$row['is_anchor']) {
	                $data[$key]['product_count'] = $row['self_product_count'];
	            }
        	}
        }
    }
} } 