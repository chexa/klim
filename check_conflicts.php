<?php

define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(__FILE__));

require_once(BP . DS . 'lib' . DS . 'Varien' . DS . 'Object.php');
require_once(BP . DS . 'lib' . DS . 'Varien' . DS . 'Simplexml' . DS . 'Config.php');
require_once(BP . DS . 'lib' . DS . 'Varien' . DS . 'Simplexml' . DS . 'Element.php');

require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Core' . DS . 'functions.php');


RikiTest::run();

class RikiTest
{
    protected $_codeDir     = '';
    protected $_etcDir      = '';
    protected $_designDir   = '';
    protected $_cacheDir    = '';

    protected $_localXml    = null;
    protected $_filePaths   = null;

    protected $_mysqlConnect = null;

    protected $_magentoVersion = null;

    protected $_messages    = null;

    public function __construct() {
        $this->_etcDir      = BP . DS . 'app' . DS . 'etc';
        $this->_codeDir     = BP . DS . 'app' . DS . 'code';
        $this->_designDir   = BP . DS . 'app' . DS . 'design';
        $this->_cacheDir    = BP . DS . 'var' . DS . 'cache';

        $this->_filePaths = array();

        $this->_messages = array();

        $this->mysqlConnect();
    }

    public function __destruct() {
        $this->mysqlDisconnect();
    }
    
    public static function run()
    {
        try {
            $testObj = new RikiTest();
            $testObj->outHtmlBegin();

            $testObj->checkPostForm();

            $testObj->outMessages();

            $testObj->checkRewriteConflicts();
            $testObj->outMagentoVersion();
            $testObj->checkDisableLocalModules();
            $testObj->getCacheConfig();
            $testObj->checkCrontabConfig();
            
            $testObj->outHtmlEnd();
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    protected function checkPostForm() {
        if( isset($_POST['submit']) ) {
            $actionType = $_POST['action'];

            switch($actionType) {
                case 'flush_cache' : $this->flushMagentoCache(); break;
            }
        }
    }

    protected function flushMagentoCache() {
        $this->emptyDir($this->_cacheDir);
        $this->_messages[] = 'Magento cache successfully flushed';
    }


    protected function checkRewriteConflicts() {
        $conflicts = $this->_getPossibleConflictsList();

        if(FALSE !== $conflicts) {
            $this->outConflicts($conflicts);
        }
    }

    /**
     * Retrive possible conflicts list
     *
     * @return array
     */
    protected  function _getPossibleConflictsList()
    {
        $moduleFiles = glob($this->_etcDir . DS . 'modules' . DS . '*.xml');

        if (!$moduleFiles) {
            return false;
        }
        
        // load file contents
        $unsortedConfig = new Varien_Simplexml_Config();
        $unsortedConfig->loadString('<config/>');
        $fileConfig = new Varien_Simplexml_Config();

        foreach($moduleFiles as $filePath) {
            $fileConfig->loadFile($filePath);
            $unsortedConfig->extend($fileConfig);
        }

        // create sorted config [only active modules]
        $sortedConfig = new Varien_Simplexml_Config();
        $sortedConfig->loadString('<config><modules/></config>');
        
        foreach ($unsortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            if('true' === (string)$moduleNode->active) {
                $sortedConfig->getNode('modules')->appendChild($moduleNode);
            }
        }

        $fileConfig = new Varien_Simplexml_Config();

        $_finalResult = array();

        foreach($sortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            $codePool = (string)$moduleNode->codePool;
            $configPath = $this->_codeDir . DS . $codePool . DS . uc_words($moduleName, DS) . DS . 'etc' . DS . 'config.xml';

            $fileConfig->loadFile($configPath);

            $rewriteBlocks = array('blocks', 'models', 'helpers');

            foreach($rewriteBlocks as $param) {
                if(!isset($_finalResult[$param])) {
                    $_finalResult[$param] = array();
                }

                if($rewrites = $fileConfig->getXpath('global/' . $param . '/*/rewrite')) {
                    foreach ($rewrites as $rewrite) {
                        $parentElement = $rewrite->xpath('../..');
                        
                        foreach($parentElement[0] as $moduleKey => $moduleItems) {
                            if($moduleItems->rewrite) {
                                $_finalResult[$param] = array_merge_recursive($_finalResult[$param], array($moduleKey => $moduleItems->asArray()));
                            }
                        }
                    }
                }
            }
        }

        foreach(array_keys($_finalResult) as $groupType) {

            foreach(array_keys($_finalResult[$groupType]) as $key) {
                // remove some repeating elements after merging all parents 
                foreach($_finalResult[$groupType][$key]['rewrite'] as $key1 => $value) {
                    if(is_array($value)) {
                        $_finalResult[$groupType][$key]['rewrite'][$key1] = array_unique($value);
                    }
                    
                    // if rewrites count < 2 - no conflicts - remove
                    if( 
                        (gettype($_finalResult[$groupType][$key]['rewrite'][$key1]) == 'array' && count($_finalResult[$groupType][$key]['rewrite'][$key1]) < 2) 
                        ||
                        gettype($_finalResult[$groupType][$key]['rewrite'][$key1]) == 'string'
                    ) {
                        unset($_finalResult[$groupType][$key]['rewrite'][$key1]);
                    }
                } 
                
                // clear empty elements
                if(count($_finalResult[$groupType][$key]['rewrite']) < 1) {
                    unset($_finalResult[$groupType][$key]);
                }
            }
            
            // clear empty elements
            if(count($_finalResult[$groupType]) < 1) {
                unset($_finalResult[$groupType]);
            }

        }

        return $_finalResult;
    }


    protected function getLocalXmlConfig() {
        if(is_null($this->_localXml)) {
            $this->_localXml = new Varien_Simplexml_Config();
            //$this->_localXml->loadFile($this->_etcDir . DS . 'local.xml');
            $this->_localXml->loadFile($this->_etcDir . DS . 'config.xml');
        }
        return $this->_localXml;
    }
    
    protected function checkDisableLocalModules() {
        $isDisabled = (string)$this->getLocalXmlConfig()->getNode('global/disable_local_modules');

        $this->outDisableLocalModules($isDisabled);
    }

    protected function mysqlConnect() {
        if(!$this->_mysqlConnect) {
            $dbParams = $this->getLocalXmlConfig()->getNode('global/resources/' . $_GET['area'] . '/connection');
	 if (false !== strpos($dbParams->host, '/')){
    	     $dbParams->host = ':' . $dbParams->host;
	 }
            $this->_mysqlConnect = mysql_connect(
                (string)$dbParams->host, 
                (string)$dbParams->username, 
                (string)$dbParams->password) or die(mysql_error());
            mysql_select_db((string)$dbParams->dbname);
        }
        return $this->_mysqlConnect;
    }

    protected function mysqlDisconnect() {
        if($this->_mysqlConnect) {
            mysql_close($this->_mysqlConnect);
        }
    }

    protected function mysqlPrepareQuery($query) {
        return str_replace('~tablePrefix~', $this->getMysqlTablePrefix(), $query);
    }

    protected function getMysqlTablePrefix() {
        return (string)$this->getLocalXmlConfig()->getNode('global/resources/db/table_prefix');
    }

    protected function getMagentoVersion() {
        if(is_null($this->_magentoVersion)) {
            $this->_magentoVersion = '1.3';
            
            $str = file_get_contents(BP . DS . 'app' . DS . 'Mage.php'); 
            if (preg_match('/\'minor\'\s+=>\s+\'(\d)\'/', $str, $m)) {
                $this->_magentoVersion = '1.'.$m[1];
            }
        }

        return $this->_magentoVersion;
    }

    protected function getCacheConfig() {
        $cacheData = array();

        // for Magento 1.3 cache config stores in use_cache.ser file
        if($this->getMagentoVersion() == '1.3') {
            $cacheData = unserialize(file_get_contents($this->_etcDir . DS . 'use_cache.ser'));
        }
        // for Magento 1.4 cache config stores in db
        else {
            $query = "SELECT * FROM ~tablePrefix~core_cache_option";
            $query = str_replace('~tablePrefix~', $this->getMysqlTablePrefix(), $query);
            $result = mysql_query($query, $this->mysqlConnect()) or die (mysql_error());

            if($result && mysql_num_rows($result)) {
                while($row = mysql_fetch_assoc($result)) {
                    $cacheData[$row['code']] = $row['value'];
                }
            }
        }

        $this->outCacheConfig($cacheData);
    }

    protected function checkCrontabConfig() {
        $returnValue = array();

        if(function_exists('exec')) {
            exec('crontab -l', $returnValue);
            if(!count($returnValue)) {
                $returnValue[] = 'No cron jobs found';
            }
        }
        else {
            $returnValue[] = 'EXEC is disabled';
        }
        $this->outCrontabConfig($returnValue);
    }


    /**
    * Output functions
    */
    protected function outConflicts(&$conflicts) {
        echo '<h3>Modules conflicts</h3>';
        if(count($conflicts)) {
            $this->outHasConflicts($conflicts);
        }
        else {
            $this->outNoConflicts();
        }
        echo '<hr/>';
    }

    protected function outNoConflicts() {
        echo 'No Module Conflicts Found';
    }

    protected function outHasConflicts(&$conflicts) {
        echo '<strong>Found Conflicts:</strong>';
        echo '<table width="100%" border="1" cellpadding="5">';
        echo '<tr><th>Magento Class</th><th>Rewrite Classes</th></tr>';
        foreach($conflicts as $groupType => $modules) {
            echo '<tr><td colspan="2"><center><strong>' . ucwords($groupType) . '</strong></center></td></tr>';
            foreach($modules as $moduleName => $moduleRewrites) {
                foreach($moduleRewrites['rewrite'] as $moduleClass => $rewriteClasses) {
                    echo '<tr>';
                    echo '<td>' . uc_words($moduleName . '_' . $moduleClass) . '</td>';
                    echo '<td>' . implode('<br/>', $rewriteClasses) . '</td>';
                    echo '</tr>';
                }
            }
        }
        echo '</table>';
    }


   protected function outFilePermissions() {
        echo '<h3>File Permissions</h3>';
        if(count($this->_filePaths)) {
            $this->outHasFilePermissionsErrors();
        }
        else {
            $this->outNoFilePermissionsErrors();
        }
        echo '<hr/>';
    }

    protected function outNoFilePermissionsErrors() {
        echo 'All files has right permissions';
    }

    protected function outHasFilePermissionsErrors() {
        echo '<strong>These files should have write permissions:</strong>';
        echo '<ul>';
        foreach($this->_filePaths as $filePath => $filePerms) {
            echo '<li>' . $filePath . '</li>';
        }
        echo '</ul>';
    }

    protected function outDisableLocalModules($isDisabled) {
        echo '<h3>Disable Local Modules</h3>';
        echo $isDisabled;
        echo '<hr/>';
    }

    protected function outDisableModulesOutput($data) {
        echo '<h3>Disable Modules Output</h3>';
        if(count($data)) {
            $this->outHasDisableModulesOutput($data);
        }
        else {
            $this->outNoDisableModulesOutput();
        }
        echo '<hr/>';
    }

    protected function outHasDisableModulesOutput($data) {
        echo '<strong>These modules output disabled:</strong>';
        echo '<ul>';
        foreach($data as $moduleName) {
            echo '<li>' . $moduleName . '</li>';
        }
        echo '</ul>';
    }

    protected function outCacheConfig($data) {
        echo '<h3>Cache Configuration</h3>';
        $this->outHasCacheConfig($data);
        echo '<hr/>';
    }

    protected function outHasCacheConfig($data) {
        echo '<ul>';
        foreach($data as $cacheTag => $cacheStatus) {
            echo '<li><strong>' . $cacheTag . ' : </strong>' . ($cacheStatus ? 'enabled' : 'disabled') . '</li>';
        }
        echo '</ul>';

        // show Clear Cache Button
        echo '<form method="POST" onsubmit="return confirm(\'Are you sure?\')">';
        echo '<input type="hidden" name="action" value="flush_cache" />';
        echo '<input type="submit" name="submit" value="Flush Magento Cache" />';
        echo '</form>';
    }

    protected function outMagentoVersion() {
        echo '<h3>Magento version</h3>';
        echo $this->getMagentoVersion();
        echo '<hr/>';
    }

    protected function outCrontabConfig(&$data) {
        echo '<h3>Crontab Configuration</h3>';
        
        echo '<ul>';
        foreach($data as $cronCommand) {
            echo '<li>' . $cronCommand . '</li>';
        }
        echo '</ul>';
        
        echo '<hr/>';
    }






    protected function outHtmlBegin() {
        echo '<html><head>';
        echo '<title>Riki Test Script</title>';
        echo '<style>html,body{font-family:Arial;}</style>';
        echo '</head><body>';
    }

    protected function outHtmlEnd() {
        echo '</body><html>';
    }

    protected function outMessages() {
        if(count($this->_messages)) {
            echo '<ul style="border: 1px solid Black; padding-top: 10px; padding-bottom: 10px; background:#00CCCC">';
            foreach($this->_messages as $mKey => $mText) {
                echo '<li><strong>' . $mText . '</strong></li>';
                unset($this->_messages[$mKey]);
            }
            echo '</ul>';
        }
    }





    /**
    * Check if file or directory is writeable [code from Aitsys]
    */
    protected function isWriteable($sPath, $bCheckParentDirIfNotExists = true) {
        clearstatcache();
        if (file_exists($sPath) and is_file($sPath))
        {
            return $this->isFileWritable($sPath);
        }
        if (file_exists($sPath) and is_dir($sPath))
        {
            return $this->isDirWritable($sPath);
        }
        if (!file_exists($sPath))
        {
            if (!$bCheckParentDirIfNotExists)
            {
                return false;
            }
            $sDirname = dirname($sPath);
            while (strlen($sDirname) > 0 AND !file_exists($sDirname))
            {
                $sDirname = dirname($sDirname);
            }
            return $this->isDirWritable($sDirname);
        }
        return false;
    }
    
    protected function isFileWritable($sPath) {
        if (!$sPath)
        {
            return false;
        }
        if (stristr(PHP_OS, "win"))
        {
            // trying to append
            $fp = @fopen($sPath, 'a+');
            if (!$fp)
            {
                return false;
            }
            fclose($fp);
            return true;
        } else 
        {
            return is_writable($sPath);
        }
    }
    
    protected function isDirWritable($sPath) {
        if (!$sPath)
        {
            return false;
        }
        if ('/' != $sPath[strlen($sPath)-1])
        {
            $sPath .= DIRECTORY_SEPARATOR;
        }
        if (stristr(PHP_OS, "win"))
        {
            /**
             * Trying to create a new file
             */
            $sFilename = uniqid(time());
            $fp = @fopen($sPath . $sFilename, 'w');
            if (!$fp) 
            {
                return false;
            }
            if (!@fwrite($fp, 'test'))
            {
                return false;
            }
            fclose($fp);
            /**
             * clean up after ourselves
             */
            unlink($sPath . $sFilename);
            return true;
        } else 
        {
            return is_writable($sPath);
        }
    } 


    protected function emptyDir($dirname = null)
    {
        if(!is_null($dirname)) {
            if (is_dir($dirname)) {
                if ($handle = @opendir($dirname)) {
                    while (($file = readdir($handle)) !== false) {
                        if ($file != "." && $file != "..") {
                            $fullpath = $dirname . '/' . $file;
                            if (is_dir($fullpath)) {
                                $this->emptyDir($fullpath);
                                @rmdir($fullpath);
                            }
                            else {
                                @unlink($fullpath);
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

}
?>


