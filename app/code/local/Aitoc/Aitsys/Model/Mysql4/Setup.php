<?php

class Aitoc_Aitsys_Model_Mysql4_Setup extends Aitoc_Aitsys_Abstract_Resource_Setup
{
    
    public function applyAitocModuleUninstall($moduleName)
    {
        $localDir = Aitoc_Aitsys_Abstract_Service::get()->filesystem()->getLocalDir();
        $moduleDir = $localDir . str_replace('_', '/', $moduleName);
        
        $configFile = $moduleDir.DS.'etc'.DS.'config.xml';
                
        if (file_exists($configFile))
        {
            $config = simplexml_load_file($configFile);
        }
 
        if (!isset($config))
        {
            return false;
        }

        foreach ($config->global->resources->children() as $key => $object)
        {
            if ($object->setup)
            {
                $resourceName = $key;
                break;
            }
        }
            
        if (!isset($resourceName))
        {
            return false;
        }

        $sqlFilesDir = $moduleDir.DS.'sql'.DS.$resourceName;

        if (!is_dir($sqlFilesDir) || !is_readable($sqlFilesDir)) {
            return false;
        }
        // Read resource files
        $arrAvailableFiles = array();
        $sqlDir = dir($sqlFilesDir);
        while (false !== ($sqlFile = $sqlDir->read())) {
            $matches = array();
            if (preg_match('#^mysql4-uninstall-(.*)\.(sql|php)$#i', $sqlFile, $matches)) {
                $arrAvailableFiles[$matches[1]] = $sqlFile;
            }
        }
        $sqlDir->close();
        
        if (empty($arrAvailableFiles)) {
            return false;
        }

        
        foreach ($arrAvailableFiles as $resourceFile) {
            $sqlFile = $sqlFilesDir.DS.$resourceFile;
            $fileType = pathinfo($resourceFile, PATHINFO_EXTENSION);
            // Execute SQL
            if ($this->_conn) {
                if (method_exists($this->_conn, 'disallowDdlCache')) {
                    $this->_conn->disallowDdlCache();
                }
                try {
                    switch ($fileType) {
                        case 'sql':
                            $sql = file_get_contents($sqlFile);
                            if ($sql!='') {
                                $result = $this->run($sql);
                            } else {
                                $result = true;
                            }
                            break;
                        case 'php':
                            $conn = $this->_conn;
                            $result = include($sqlFile);
                            break;
                        default:
                            $result = false;
                    }
                } catch (Exception $e){
                    echo "<pre>".print_r($e,1)."</pre>";
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Error in file: "%s" - %s', $sqlFile, $e->getMessage()));
                }
                if (method_exists($this->_conn, 'allowDdlCache')) {
                    $this->_conn->allowDdlCache();
                }
            }
        }
        self::$_hadUpdates = true;
        return true;
    }
    
    public function applyAitocModuleActivate($moduleName)
    {
        $localDir = Aitoc_Aitsys_Abstract_Service::get()->filesystem()->getLocalDir();
        $moduleDir = $localDir . str_replace('_', '/', $moduleName);
        
        $configFile = $moduleDir.DS.'etc'.DS.'config.data.xml';
        if (file_exists($configFile))
        {
            $config = simplexml_load_file($configFile);
        }
        else 
        {
            $configFile = $moduleDir.DS.'etc'.DS.'config.xml';
            if (file_exists($configFile))
            {
                $config = simplexml_load_file($configFile);
            }
        }

        if (!isset($config))
        {
            return false;
        }

        //list($curVersion) = (array)$config->modules->$moduleName->version;

        if (isset($config->global) && isset($config->global->resources))
        {
            foreach ($config->global->resources->children() as $key => $object)
            {
                if ($object->setup)
                {
                    $resourceName = $key;
                    break;
                }
            }
        }
        
        if (!isset($resourceName))
        {
            return false;
        }

        $dbVersion = Mage::getResourceModel('core/resource')->getDBVersion($resourceName);
                
        $sqlFilesDir = $moduleDir.DS.'sql'.DS.$resourceName;

        if (!is_dir($sqlFilesDir) || !is_readable($sqlFilesDir)) {
            return false;
        }
        // Read resource files
        $arrAvailableFiles = array();
        $sqlDir = dir($sqlFilesDir);
        
        while (false !== ($sqlFile = $sqlDir->read())) {
            $matches = array();
            if (preg_match('#^mysql4-activate-(.*)\.(sql|php)$#i', $sqlFile, $matches)) {
                $arrAvailableFiles[$matches[1]] = $sqlFile;
            }
        }
        
        if (empty($arrAvailableFiles)) {
            return false;
        }

        foreach ($arrAvailableFiles as $version => $resourceFile) {
            if (version_compare($version, $dbVersion) > 0)
            {
                break;
            }
            $sqlFile = $sqlFilesDir.DS.$resourceFile;
            $fileType = pathinfo($resourceFile, PATHINFO_EXTENSION);
            
            // Execute SQL
            if ($this->_conn) {
                if (method_exists($this->_conn, 'disallowDdlCache')) {
                    $this->_conn->disallowDdlCache();
                }
                try {
                    switch ($fileType) {
                        case 'sql':
                            $sql = file_get_contents($sqlFile);
                            if ($sql!='') {
                                $result = $this->run($sql);
                            } else {
                                $result = true;
                            }
                            break;
                        case 'php':
                            $conn = $this->_conn;
                            $result = include($sqlFile);
                            break;
                        default:
                            $result = false;
                            
                    }
                } catch (Exception $e){
                    echo "<pre>".print_r($e,1)."</pre>";
                    throw Mage::exception('Mage_Core', Mage::helper('core')->__('Error in file: "%s" - %s', $sqlFile, $e->getMessage()));
                }
                if (method_exists($this->_conn, 'allowDdlCache')) {
                    $this->_conn->allowDdlCache();
                }
            }
        }
        
        self::$_hadUpdates = true;
        return true;
    }
    
}