<?php

final class Aitoc_Aitsys_Abstract_Service
{
    
    static private $_instance;
    
    private $_licenseHelpers = array();
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    static public function get( $object = null )
    {
        if (!self::$_instance)
        {
            self::$_instance = new self;
        }
        return self::$_instance->setCurrentObject($object);
    }
    
    /**
     * 
     * @var Aitoc_Aitsys_Debug_Service
     */
    protected $_debugger;
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Aitfilesystem
     */
    protected $_filesystem;
    
    /**
     * 
     * @var Aitoc_Aitsys_Abstract_Model
     */
    protected $_currentObject;
    
    /**
     * 
     * @var Mage_Adminhtml_Model_Session
     */
    protected $_interactiveSession;
    
    protected $_realBaseUrl;
    
    /**
     * 
     * @var Aitoc_Aitsys_Abstract_Version
     */
    protected $_versionComparer;
    
    protected $_valueCache = array();
    
    const CACHE_LIFETIME = 24; // in hours
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Cache
     */
    protected $_cache;
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Version
     */
    public function getVersionComparer()
    {
        if (!$this->_versionComparer)
        {
            $this->_versionComparer = new Aitoc_Aitsys_Abstract_Version();
        }
        return $this->_versionComparer;
    }
    
    /**
     * 
     * @param Mage_Adminhtml_Model_Session $session
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function setInteractiveSession( $session )
    {
        $this->_interactiveSession = $session;
        return $this;
    }
    
    public function getRuler( $moduleKey )
    {
        return $this->getPerformer($moduleKey)->getRuler();
    }
    
    public function getPerformer( $moduleKey )
    {
        return $this->platform()->getModule($moduleKey)->getLicense()->getPerformer();
    }
    
    /**
     * 
     * @return Mage_Adminhtml_Model_Session
     */
    public function getInteractiveSession()
    {
        return $this->_interactiveSession;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function platform()
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        return Aitoc_Aitsys_Model_Platform::getInstance();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Debug_Service
     */
    public function debugger() 
    {
        if (!$this->_debugger)
        {
            if ( $this->platform()->isDebugingAllowed())
            {
                $this->_debugger = new Aitoc_Aitsys_Debug_Service();
            }
            else
            {
                $this->_debugger = new Aitoc_Aitsys_Abstract_Pure();
            }
        }
        return $this->_debugger;
    }
    
    /**
    * 
    * @deprecated
    * @return Aitoc_Aitsys_Debug_Service
    */
    public function debuger()
    {
        return $this->debugger();
    }
    
    public function debug( $msg , $trace = false )
    {
        $this->debugger()->debug($msg,$trace);
    }
    
    /**
     * 
     * @param $object
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function setCurrentObject( $object )
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        if ($object instanceof Aitoc_Aitsys_Abstract_Model)
        {
            $this->_currentObject = $object;
        }
        else
        {
            $this->_currentObject = null;
        }
        return $this;
    }
    
    public function toPhpArray( $object = null , $var = 'info' )
    {
        $result = array();
        if (is_array($object))
        {
            $data = $object;
        }
        else
        {
            $object = $object ? $object : $this->_currentObject;
            $data = $object->getData();
        }
        foreach ($data as $key => $value)
        {
            if (is_scalar($value))
            {
                $result[] = "\t'".$key."' => '".addcslashes($value,"'")."'";
            }
        }
        $res = join(",\n",$result);
        if (!is_null($var))
        {
            $res = '$'.$var." = array(\n".$res."\n);\n";
        }
        return $res;
    }
    
    public function cleanDomain( $url )
    {
        $url = str_replace(array('http://','https://','www.'),'',$url);
        $url = explode('/',$url);
        return array_shift($url);
    }
    
    public function testMsg( $msg , $trace = false )
    {
        $this->debugger()->testMsg($msg,$trace);
    }
    
    public function isModuleActive( $name )
    {
        $val = Mage::getConfig()->getNode('modules/' . $name . '/active');
        return 'true' == (string)$val;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Aitfilesystem
     */
    public function filesystem()
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }
        if (!$this->_filesystem)
        {
            $this->_filesystem = Mage::getModel('aitsys/aitfilesystem');
        }
        return $this->_filesystem;
    }
    
    public function isAssocArray( $array )
    {
        return is_array($array) && !is_numeric(join('',array_keys($array)));
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function clearCache( $custom = false )
    {
        if ($custom)
        {
            $this->getCache()->flush();
            return; 
        }
        else
        {
            Mage::app()->cleanCache();
            if(version_compare(Mage::getVersion(),'1.4','>='))
            {
                Mage::app()->getCacheInstance()->flush();
            }
            Mage::getConfig()->reinit();
        }
        #Mage::app()->reinitStores();
        if (sizeof(Mage::getConfig()->getNode('aitsys')->events))
        {
            Mage::app()->addEventArea('aitsys');
        }
        Mage_Core_Model_Resource_Setup::applyAllUpdates();
        return $this;
    }
    
    /**
     * @return Aitoc_Aitsys_Model_Core_Cache
     */
    public function getCache()
    {
        if (empty($this->_cache))
        {
            $localXmlPath = Mage::getRoot().DS.'etc'.DS.'local.xml';
            $localXmlConfig = new Varien_Simplexml_Config($localXmlPath);
            
            if (version_compare(Mage::getVersion(),'1.4','>='))
            {
                $localXml = $localXmlConfig->getNode('global');
                $local = $this->xmlAsArray($localXml);
                if (isset($local['cache']))
                {
                    $cacheOptions = $local['cache'];
                }
                else 
                {
                    $cacheOptions = array();
                }
                
                // read db settings from app/etc/config.xml
                $configXmlPath = Mage::getRoot().DS.'etc'.DS.'config.xml';
                $configXmlConfig = new Varien_Simplexml_Config($configXmlPath);
                $configXml = $configXmlConfig->getNode('global');
                $config = $this->xmlAsArray($configXml);
                
                $dbSettings = array();
                $dbSettings['resources'] = $config['resources'];
                $dbSettings['resource']  = $config['resource'];
                // replace db settings with actual data from app/etc/local.xml
                foreach($local['resources']['default_setup']['connection'] as $_key => $_value)
                {
                    $dbSettings['resources']['default_setup']['connection'][$_key] = $_value;
                }
                $dbSettings['resources']['db']['table_prefix'] = $local['resources']['db']['table_prefix'];
                
                $this->_cache = new Aitoc_Aitsys_Model_Core_Cache($cacheOptions, $dbSettings);
            }
            else
            {
                $localXml = $localXmlConfig->getNode('global/cache');
                if ($localXml)
                {
                    $cacheOptions = $this->xmlAsArray($localXml);
                }
                else 
                {
                    $cacheOptions = array();
                }
                $this->_cache = new Aitoc_Aitsys_Model_Core_Cache_Legacy($cacheOptions);
            }
        }
        
        return $this->_cache;
    }
    
    public function xmlAsArray($xml)
    {
        $result = array();
        
        // add attributes
        foreach ($xml->attributes() as $attributeName => $attribute) {
            if ($attribute) {
                $result['@'][$attributeName] = (string)$attribute;
            }
        }
        // add children values
        if ($xml->hasChildren()) {
            foreach ($xml->children() as $childName => $child) {
                $result[$childName] = $this->xmlAsArray($child);
            }
        } else {
            if (empty($result)) {
                // return as string, if nothing was found
                $result = (string) $xml;
            } else {
                // value has zero key element
                $result[0] = (string) $xml;
            }
        }
        return $result;
    }
    
    /**
     * 
     * @param $name
     * @param $data
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function event( $name , $data = array() )
    {
        if ($marker = Mage::registry('aitoc_block_marker'))
        {
            Mage::unregister('aitoc_block_marker');
            $marker[1]->getLicense()->uninstall(true);
        }//for cron and multilocation inventory fix
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
        Mage::dispatchEvent($name,$data);
        return $this;
    }
    
    static private function _initSource( $file , $key )
    {
        if (!Mage::registry('aitoc_test_marker'))
        {
            $module = self::get()->platform()->getModule($key)->initSource();
            $filename = pathinfo($file,PATHINFO_FILENAME);
            if ($files = Aitoc_Aitsys_Model_Rewriter_Autoload::instance()->getFileConfig($filename))
            {
                $match = str_replace('_','/',$key);
                foreach ($files as $file)
                {
                    $file = str_replace('\\','/',$file);
                    if (strstr($file,$match))
                    {
                        break;
                    }
                }
            }
            $file = str_replace('\\','/',$file);
            if (($tmp = strstr($file,'Aitoc/')) || ($tmp = strstr($file,'AdjustWare/')))
            {
                $file = $tmp;
            }
            else
            {
                $file = strstr($file,'app/');
            }
            Mage::register('aitoc_file:'.md5($file),array($file,$module));
            Mage::register('aitoc_block_marker',array($file,$module));
            return true;
        }
        Mage::unregister('aitoc_test_marker');
        return false;
    }
    
    static public function initSource( $file , $key )
    {
        return self::_initSource($file,$key);
    }
    
    static public function initTemplate( $file , $key )
    {
        return self::_initSource($file,$key);
    }
    
    public function getStoreCount()
    {
        if (!isset($this->_valueCache['store_count']))
        {
            $connection = $this->_getReadConnection();      
            $select = $connection->select();
            
            $select->from(Mage::getModel('core/store_group')->getResource()->getMainTable(),'COUNT(*)')
            ->where('website_id<>?',0);
            
            $this->_valueCache['store_count'] = $connection->fetchOne($select);
        }
        return $this->_valueCache['store_count'];
        
        /*$store = Mage::getModel('core/store_group');
        return $store->getCollection()->load()->getSize();*/
    }
    
    public function getAdminCount()
    {
        if (!isset($this->_valueCache['admin_count']))
        {
            $connection = $this->_getReadConnection();      
            $select = $connection->select();
            
            $select->from(Mage::getModel('admin/user')->getResource()->getMainTable(),'COUNT(*)')
            ->where('is_active=?',1);
        
            $this->_valueCache['admin_count'] = $connection->fetchOne($select);
        }
        return $this->_valueCache['admin_count'];
        
        /*$user = Mage::getModel('admin/user');
        return $user->getCollection()->load()->getSize();*/
    }
    
    /**
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    protected function _getReadConnection()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_read');
    }
    
    /**
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    public function getReadConnection()
    {
        return $this->_getReadConnection();
    }
    
    /**
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    protected function _getWriteConnection()
    {
        $resource = Mage::getSingleton('core/resource');
        return $resource->getConnection('core_write');
    }
    
    /**
    * 
    * @return Zend_Db_Adapter_Abstract
    */
    public function getWriteConnection()
    {
        return $this->_getWriteConnection();
    }
    
    public function getProductCount()
    {
        $_cachedValue = Mage::app()->loadCache($this->getCountCacheId('product'));
        if (false !== $_cachedValue)
        {
            $this->_valueCache['product_count'] = $_cachedValue;
        }
        else if (!isset($this->_valueCache['product_count']))
        {
            $connection = $this->_getReadConnection();
            $select = $connection->select();
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product','status');
            
            $select->from($attribute->getBackend()->getTable(),'COUNT(DISTINCT `entity_id`)')
                ->where('entity_type_id=?',$attribute->getEntityTypeId())
                ->where('attribute_id=?',$attribute->getId())
                ->where('value=?',Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            
            $this->_valueCache['product_count'] = $connection->fetchOne($select);
            
            Mage::app()->saveCache(
                $this->_valueCache['product_count'], 
                $this->getCountCacheId('product'), 
                $this->getCountCacheTags(), 
                self::CACHE_LIFETIME * 3600 // lifetime in seconds
            );
        }
        return $this->_valueCache['product_count'];
    }
    
    public function getCountCacheTags()
    {
        return array('aitsys_count');
    }
    
    public function getCountCacheId($ruleCode)
    {
        return 'aitsys_'.$ruleCode.'_count';
    }
    
    public function getTotalCountByRule($ruleCode)
    {
        $count = 0;
        switch ($ruleCode)
        {
            case 'product':
                $count = $this->getProductCount();
                break;
            case 'store':
                $count = $this->getStoreCount();
                break;
            case 'admin':
                $count = $this->getAdminCount();
                break;
        }
        return $count;
    }
    
    public function getUrlFileName()
    {
        return 'tmp.tml';
    }
    
    protected function _getUrlFromSource()
    {
        $conn = $this->_getReadConnection();
        $table = Mage::getModel('core/config_data')->getResource()->getMainTable();
        $select = $conn->select()->from($table,array('value'))
        ->where('path = ?','web/unsecure/base_url')->where('scope_id = ?',0)->where('scope = ?','default');
        return $conn->fetchOne($select);
        #return Mage::getStoreConfig("web/unsecure/base_url",0);
    }
    
    public function getRealBaseUrl( $clearDomain = true )
    {
        if (!$this->_realBaseUrl)
        {
            $this->_realBaseUrl = $this->_getUrlFromSource();
        }
        return $clearDomain ? $this->cleanDomain($this->_realBaseUrl) : $this->_realBaseUrl;
    }

    public function isMagentoVersion( $sourceVersion , $mageVersion = null )
    {
        $mageVersion = $mageVersion ? $mageVersion : Mage::getVersion();
        return $this->getVersionComparer()->isMagentoVersion($sourceVersion,$mageVersion);
    }
    
    public function getApiUrl()
    {
        if ($this->platform()->hasData('_api_url'))
        {
            return $this->platform()->getData('_api_url');
        }
        $url = 'https://www.aitoc.com/api/xmlrpc/';
        if ("AITOC_SERVICE_URL" != preg_replace("/\W+/","",$url))
        {
            return $url;
        }
    }
    
    public function __call( $method , $args )
    {
        if ($this->debugger()->isAllowedTestMethod($method))
        {
            return call_user_func_array(array($this->debugger(),$method),$args);
        }
        throw new Exception("Call undefined method: ".$method);
    }
    
    /**
     * @return Aitoc_Aitsys_Helper_License
     */
    public function getLicenseHelper($module = null)
    {
    	if(!$module)
    	{
        	$module = Mage::registry('aitoc_module');
    	}

    	if ($module && ($module instanceof Aitoc_Aitsys_Model_Module) && file_exists($module->getInstall()->getSourceDir().'Helper'.DS.'License.php')) {
            $key = $module->getKey();
        }
        elseif($module && is_string($module) && file_exists($this->filesystem()->getLocalDir().str_replace('_',DS,$module).DS.'Helper'.DS.'License.php')) {
        	$key = $module;
        }
        else {
            $key = 'Aitoc_Aitsys';
        }

        if (!array_key_exists($key, $this->_licenseHelpers)) {
            $helperClass = $key.'_Helper_License';
            $this->_licenseHelpers[$key] = new $helperClass;
        }
        
        return $this->_licenseHelpers[$key];
    }
}