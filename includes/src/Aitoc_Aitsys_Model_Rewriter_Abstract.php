<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
abstract class Aitoc_Aitsys_Model_Rewriter_Abstract extends Aitoc_Aitsys_Abstract_Model
{
    protected $_etcDir          = '';
    protected $_codeDir         = '';
    protected $_rewriteDir      = '';
    protected $_checkClassDir   = array();
    protected $_phpcli          = false;
    protected $_conn;
    protected $_localConfig;
    
    const REWRITE_CACHE_DIR = '/var/ait_rewrite/';
    
    public function __construct()
    {
        $this->_etcDir      = Mage::getRoot().'/etc/';
        $this->_codeDir     = Mage::getRoot().'/code/';
        $this->_rewriteDir  = dirname(Mage::getRoot()) . self::REWRITE_CACHE_DIR;
        
        $this->_checkClassDir[] = $this->_codeDir . 'local/';
        $this->_checkClassDir[] = $this->_codeDir . 'community/';
        $this->_checkClassDir[] = $this->_codeDir . 'core/';
        
        if (!file_exists($this->_rewriteDir))
        {
            @mkdir($this->_rewriteDir);
        }
        
        if (defined('STDIN') OR isset($_SERVER['argc']) OR isset($_SERVER['argv']))
        {
            $this->_phpcli = true;
        }
    }
    
    // last changes start
        
    public function grantAll( $path , $recursive = true )
    {
        if (function_exists('chmod'))
        {
            @chmod($path, 0777);
            if ($recursive = is_dir($path))
            {
                $items = new RecursiveDirectoryIterator($path);
                foreach ($items as $item)
                {
                    $this->grantAll((string)$item,false);
                }
            }
        }
        return $this;
    }
    
    public function isPhpCli()
    {
        return $this->_phpcli;
    }
    
    public function getDbConnection()
    {
        if (is_null($this->_conn)) {
            $config = $this->getLocalConfig();
            $this->_conn = new Varien_Db_Adapter_Pdo_Mysql(array(
                'host'     => (string)$config->global->resources->default_setup->connection->host,
                'username' => (string)$config->global->resources->default_setup->connection->username,
                'password' => (string)$config->global->resources->default_setup->connection->password,
                'dbname'   => (string)$config->global->resources->default_setup->connection->dbname ,
                'type'     => 'pdo_mysql' ,
                'model'    => 'mysql4' ,
                'active'   => 1
            ));
        }
        return $this->_conn;
    }
    
    public function getLocalConfig()
    {
        if (is_null($this->_localConfig)) {
            $path = $this->_etcDir.'local.xml';
            if (file_exists($path))
            {
                $this->_localConfig = new Zend_Config_Xml($path);
            }
        }
        return $this->_localConfig;
    }
    
    public function getConfigValue($path, $defaultValue = null)
    {
        $conn = $this->getDbConnection();
        $select = $conn->select()->from(
            $this->getLocalConfig()->global->resources->db->table_prefix.'core_config_data'
        )->where('path = ?',$path)->where('scope = ?','default');
        $data = $conn->fetchRow($select);

        //$conn->closeConnection();
        if ($data === false || !isset($data['value']) || $data['value'] === '') {
            $data = $defaultValue;
        }
        else {
            $data = $data['value'];
        }
        
        // before trying to unserialize we are replacing error_handler with another one to catch E_NOTICE run-time error
        Aitoc_Aitsys_Model_Exception::setErrorException();
        $tmpData = $data;
        try {
            $data = unserialize($data);
        }
        catch (ErrorException $e) {
            //restore old data value
            $data = $tmpData;
            unset($tmpData);
        }

        Aitoc_Aitsys_Model_Exception::restoreErrorHandler();
        return $data;
    }
}