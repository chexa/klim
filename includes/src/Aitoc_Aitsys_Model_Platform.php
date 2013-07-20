<?php

final class Aitoc_Aitsys_Model_Platform extends Aitoc_Aitsys_Abstract_Model
{
    
    const PLATFORMFILE_SUFFIX = '.platform.xml';
    const INSTALLATION_DIR = 'ait_install';
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Platform
     */
    static protected $_instance;
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    static public function getInstance()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self();
            try
            {
                try
                {
                    self::$_instance->init();
                }
                catch (Exception $exc)
                {
                    self::$_instance->block();
                    throw $exc;
                }
            }
            catch (Aitoc_Aitsys_Model_Aitfilesystem_Exception $exc)
            {
                $admin = Mage::getSingleton('admin/session');
                if ($admin->isLoggedIn())
                {
                    $msg = "Error in the file: %s. Probably it does not have write permissions.";
                    $session = Mage::getSingleton('adminhtml/session');
                    /* @var $session Mage_Adminhtml_Model_Session */
                    $session->addError(Mage::helper('aitsys')->__($msg,$exc->getMessage()));
                }
            }
        }
        return self::$_instance;
    }
    
    protected $_block = false;
    
    protected $_modules = array();
    
    protected $_version;
    
    protected $_installDir;
    
    protected $_licenseDir; // rastorguev fix
    
    protected $_copiedPlatformFiles = array();
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_License_Service
     */
    protected $_service = array();
    
    protected $_moduleIgnoreList = array('Aitoc_Aitinstall', 'Aitoc_Aitsys', 'Aitoc_Aitprepare');
    
    protected $_aitocPrefixList = array('Aitoc_','AdjustWare_');
    
    protected $_moduleDirs = array( 'Aitoc' , 'AdjustWare' );
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function block()
    {
        $this->_block = true;
        return $this;
    }
    
    public function getModuleDirs()
    {
        return $this->_moduleDirs;
    }
    
    public function isAitocNamespace( $namespace , $compare = false )
    {
        if ($compare)
        {
            return in_array($namespace,$this->_moduleDirs);
        }
        foreach ($this->_moduleDirs as $dir)
        {
            if (false !== strstr($namespace,$dir))
            {
                return true;
            }
        }
        return false;
    }
    
    public function isBlocked()
    {
        return $this->_block;
    }
    
    public function getModules()
    {
        if (!$this->_modules)
        {
            $this->_generateModuleList();
        }
        return $this->_modules;
    }
    
    public function getModuleKeysForced()
    {
        $modules = array();
        $path = Mage::getBaseDir('code').DS.'local'.DS;
        foreach ($this->_moduleDirs as $dir)
        {
            $aModuleDirs = glob($path.$dir.DS.'*');
            if (!$aModuleDirs)
                continue;
            foreach ($aModuleDirs as $tmpPath)
            {
                $name = pathinfo($tmpPath,PATHINFO_FILENAME);
                $key = $dir.'_'.$name;
                if ('Aitoc_Aitsys' == $key)
                {
                    continue;
                }
                $modules[$key] = 'true' == (string)Mage::getConfig()->getNode('modules/'.$key.'/active');
            }
        }
        return $modules;
    }
    
    /**
     * 
     * @param $key
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule( $key )
    {
        if (!$this->_modules)
        {
            $this->_generateModuleList();
        }
        return isset($this->_modules[$key]) ? $this->_modules[$key] : null;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_License_Service
     */
    public function getService( $for = 'default' )
    {
        if (!isset($this->_service[$for]))
        {
            $notExcluded = (!$this->hasDebugExclude() || !in_array($for,$this->debug_exclude));
            if ($this->isDebug() && $notExcluded)
            {
                $this->tool()->testMsg('Use debug service');
                $this->_service[$for] = new Aitoc_Aitsys_Model_License_Service_Debug();
            }
            else
            {
                $this->tool()->testMsg('Use real service');
                $this->_service[$for] = new Aitoc_Aitsys_Model_License_Service();
            }
            $this->_service[$for]->setServiceUrl($this->getServiceUrl());
        }
        return $this->_service[$for];
    }
    
    public function isDebug()
    {
        return $this->getData('debug');
    }
    
    public function isDebugingAllowed()
    {
        return $this->getData('debuging_allowed') ? true : false;
    }
    
    public function getServiceUrl()
    {
        if ($url = $this->tool()->getApiUrl())
        {
            return $url;
        }
        if ($url = $this->getData('_service_url'))
        {
            return $url;
        }
        $url = $this->getData('service_url');
        return $url ? $url : Mage::getStoreConfig('aitsys/service/url');
    }
    
    public function getVersion()
    {
        if (!$this->_version)
        {
            $this->_version = (string)Mage::app()->getConfig()->getNode('modules/Aitoc_Aitsys/version'); 
        }
        return $this->_version;
    }
    
    /**
     * 
     * @param $mode
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function setTestMode( $mode = true )
    {
        if (!$this->isModePresetted())
        {
            $this->setData('mode',$mode ? 'test' : 'live');
        }
        return $this;
    }
    
    public function isModePresetted()
    {
        return $this->hasData('mode');
    }
    
    public function isTestMode() 
    {
        return $this->getData('mode') == 'test';
    }
    
    public function getInstallDir( $base = false )
    {
        if (!$this->_installDir)
        {
            $this->_installDir = dirname(dirname(__FILE__)).'/install/';
/**            
            if (!$this->tool()->filesystem()->isWriteable($this->_installDir))
            {
                throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($this->_installDir." should be writeable.");
            }
**/            
        }
        if ($base)
        {
            return $this->_installDir;
        }
        return rtrim($this->_installDir,'/').'/';
    }
    
    // rastorguev fix
    public function getLicenseDir( $base = false )
    {
        if (!$this->_licenseDir)
        {
            $this->_licenseDir = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/var/'.self::INSTALLATION_DIR.'/';
            if (!$this->tool()->filesystem()->isWriteable($this->_licenseDir))
            {
                throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($this->_licenseDir." should be writeable.");
            }
            
            if (!file_exists($this->_licenseDir))
            {
                $this->tool()->filesystem()->mkDir($this->_licenseDir);
            }
        }
        if ($base)
        {
            return $this->_licenseDir;
        }
        return rtrim($this->_licenseDir.$this->getPlatformId(),'/').'/';
    }
    
    public function getPlatformId()
    {
        return $this->getData('platform_id');
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function setPlatformId( $platformId )
    {
        return $this->setData('platform_id',$platformId);
    }
    
    public function init()
    {
        $this->_fixOldPlatform();
        if (!$this->_loadConfigFile()->_loadPlatformData()->getPlatformId())
        {
            $license = $this->getAnyLicense();
            $service = $license ? $license->getService() : $this->getService();
            
            if ($this->tool()->cleanDomain($service->getServiceUrl()) 
             == $this->tool()->cleanDomain(Mage::getBaseUrl()))
            {
                $this->reset();
                return;
            }
            
            $this->tool()->testMsg('begin register platform');
            
            try
            {
                $service->connect();
                
                $data = array(
                	'purchaseid' => $license ? $license->getPurchaseId() : '' ,
                    'initial_module_list' => $this->getModulePurchaseIdQuickList()
                );
                $platformId = $service->registerPlatform($data);
                
                $service->disconnect();
                $this->tool()->testMsg('Generated platform id: '.$platformId);
                $this->setPlatformId($platformId);
                $this->setServiceUrl($service->getServiceUrl());
                $this->_savePlatformData();
                $this->_copyToPlatform($platformId);
                $this->unsPlatformId();
                $this->_loadPlatformData();
            }
            catch (Exception $exc)
            {
                $this->tool()->testMsg($exc);
            }
        }
        $this->reset();
    }

    protected function _fixOldPlatform()
    {
        $installDir = $this->getInstallDir(true);
        if ($platforms = glob($installDir.'*.platform.xml'))
        {
            foreach ($platforms as $platformFile)
            {
                $platformId = $this->_castPlatformId($platformFile);
                $platformDir = $this->getLicenseDir().$platformId;
                $this->tool()->filesystem()->makeDirStructure($platformDir);
                $oldPlatformDir = $this->getInstallDir().$platformId;
                if ($pathes = glob($oldPlatformDir.'/*'))
                {
                    foreach ($pathes as $path)
                    {
                        $fileinfo = pathinfo($path);
                        if ('xml' == $fileinfo['extension'])
                        {
                            $to = $this->getInstallDir().$fileinfo['basename'];
                        }
                        else
                        {
                            $to = $this->getLicenseDir().$platformId."/".$fileinfo['basename'];
                        }
                        $this->tool()->filesystem()->moveFile($path,$to);
                    }
                }
                $this->tool()->filesystem()->moveFile($platformFile,$platformDir.'.platform.xml');
                $this->tool()->filesystem()->rmFile($oldPlatformDir);
            }
        }
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function save()
    {
        return $this->_savePlatformData();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    public function reset()
    {
        $this->_generateModuleList();
        foreach ($this->_modules as $module) 
        {
            $this->tool()->testMsg('Update module '.$module->getLabel().' status after generating');
            $module->updateStatuses();
        }
        return $this;
    }
    
    public function isAitocModule( $module )
    {
        foreach ($this->_aitocPrefixList as $prefix)
        {
            if (0 === strpos($module,$prefix))
            {
                return true;
            }
        }
    }
    
    public function isIgnoredModule( $module )
    {
        foreach ($this->_moduleIgnoreList as $ignoredModule)
        {
            if (false !== strstr($module,$ignoredModule))
            {
                return true;
            }
        }
    }
    
    public function isPlatformFileName( $filename )
    {
        return preg_match('/'.preg_quote(self::PLATFORMFILE_SUFFIX).'$/',$filename);
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License | null
     */
    public function getAnyLicense()
    {
        $path = $this->getInstallDir().'*.xml';
        if ($pathes = glob($path))
        {
            foreach ($pathes as $path)
            {
                if (!$this->isPlatformFileName($path))
                {
                    $module = $this->_makeModuleByInstallFile($path);
                    return $module->getLicense();
                }
            }
        }
    }
    
    public function getModulePurchaseIdQuickList()
    {
        $list = array();
        $path = $this->getInstallDir().'*.xml';
        if ($pathes = glob($path))
        {
            foreach ($pathes as $path)
            {
                if (!$this->isPlatformFileName($path))
                {
                    $module = $this->_makeModuleByInstallFile($path);
                    $list[$module->getKey()] = $module->getLicense()->getPurchaseId(); 
                }
            }
        }
        return $list;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadConfigFile()
    {
        $path = dirname($this->getInstallDir(true)).'/config.php';
        $this->tool()->testMsg('check config path: '.$path);
        if (file_exists($path))
        {
            include $path;
            if (isset($config) && is_array($config))
            {
                $this->tool()->testMsg('loaded config:');
                $this->tool()->testMsg($config);
                $this->setData($config);
            }
        }
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _generateModuleList()
    {
        $this->tool()->testMsg('Try to generate module list!'); 
        
        $this->_loadLicensedModules()->_loadAllModules();

        $this->tool()->event('aitsys_generate_module_list_after');
        $this->tool()->testMsg('Module list generated');
        return $this;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadLicensedModules()
    {
        if (!file_exists($this->getInstallDir()))
        {
            return $this;
        }
        
        $dir = new DirectoryIterator($this->getInstallDir());
            
        
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */
            if ($item->isFile())
            {
                $filename = $item->getFilename();
                if (false !== strstr($filename,'.xml'))
                {
                    if ($this->isPlatformFileName($filename))
                    {
                        continue;
                    }
                    if ($this->isUpgradeFilename($filename))
                    {
                        continue;
                    }
                    $this->tool()->testMsg("Try load licensed module");
                    $module = $this->_makeModuleByInstallFile($item->getPathname());
                    
                    if ((!$this->_addEntHash() && $module->getLicense()->getEntHash()) || ($this->_addEntHash() && !$module->getLicense()->getEntHash()))
                    {
                        $this->_moduleIgnoreList[] = $module->getKey();
                        continue;
                    }
                                     
                    $key = $module->getKey();
                    $this->tool()->testMsg("Try load licensed module finished: ".$key);
                    if (!isset($this->_modules[$key]))
                    {
                        $this->tool()->testMsg("Add new module");
                        $this->_modules[$key] = $module;
                    }
                    else
                    {
                        $this->tool()->testMsg("Reset existed module");
                        $this->_modules[$key]->reset();
                    }
                }
            }
        }
        return $this;
    }
    
    protected function _addEntHash()
    {
        $val = Mage::getConfig()->getNode('modules/Enterprise_Enterprise/active');
        return ((string)$val == 'true');
    }
    
    public function isUpgradeFilename( $filename )
    {
        return false !== strstr($filename,'.upgrade-license.xml');
    }
    
    /**
     * 
     * @param $path
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _makeModuleByInstallFile( $path )
    {
        $module = new Aitoc_Aitsys_Model_Module();
        $module->loadByInstallFile(str_replace('.php','.xml',$path));
        $this->tool()->testMsg(get_class($module->getLicense()));
        $this->tool()->event('aitsys_create_module_after',array('module' => $module));
        return $module;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadAllModules()
    {
        $filesystem = $this->tool()->filesystem();
        $dir = new DirectoryIterator($filesystem->getEtcDir());
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */
            if ($this->isIgnoredModule($item->getFilename()))
            {
                continue;
            }
            if ($item->isFile() && $this->isAitocModule($item->getFilename()))
            {
                $this->_makeModuleByModuleFile($item->getPathname());
            }
        }
        return $this;
    }
    
	/**
     * 
     * @param $path
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _makeModuleByModuleFile( $path )
    {
        $moduleFile = new SplFileInfo($path);
        $file = $moduleFile->getFilename();
        
        list($key) = explode('.',$file);
        $this->tool()->testMsg('Check: '.$key.' -- '.$file);
        if ($module = (isset($this->_modules[$key]) ? $this->_modules[$key] : null))
        {
            return $module;
        }
        $this->tool()->testMsg('Create: '.$key);
        $module = new Aitoc_Aitsys_Model_Module();
        $module->loadByModuleFile($path,$key);
        return $this->_modules[$key] = $module;
    }

    protected function _castPlatformId( $file )
    {
        if ($file instanceof SplFileInfo)
        {
            $file = $file->getFilename();
        }
        $fileinfo = pathinfo($file);
        list($platformId) = explode('.',$fileinfo['basename'],2);
        return $platformId;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _loadPlatformData()
    {
        $this->_copiedPlatformFiles = array();
//        $dir = new DirectoryIterator($this->getInstallDir());
        $dir = new DirectoryIterator($this->getLicenseDir()); // rastorguev fix
        foreach ($this->getPlatforms() as $item)
        {
            /* @var $item DirectoryIterator */
            $platformId = $this->_castPlatformId($item);
            // start rastorguev fix
            if (!file_exists($item->getPathname()))
            {
                $this->tool()->testMsg("Platform id broken or superfluous: ".$platformId);
                continue;
            }
            // finish rastorguev fix
            if ($this->getPlatformId() || !$this->_checkPlatformId($platformId,$item->getPathname()))
            {
                $this->tool()->testMsg("Platform id broken or superfluous: ".$platformId);
                $this->_removePlatform($platformId);
                continue;
            }
            $dom = new DOMDocument('1.0');
            $dom->load($item->getPathname());
            $platform = $dom->getElementsByTagName('platform');
            if ($platform->length)
            {
                $platform = $platform->item(0);
                foreach ($platform->childNodes as $item)
                {
                    if ('location' == $item->nodeName)
                    {
                        continue;
                    }
                    #$this->tool()->debug($item->nodeName.': '.$item->nodeValue);
                    if (!$this->hasData($item->nodeName))
                    {
                        
                        $this->setData($item->nodeName,$item->nodeValue);
                    }
                }
            }
            $this->setPlatformId($platformId);
            $this->tool()->testMsg("Platform id:".$platformId);
        }
        if ($platformId = $this->getPlatformId())
        {
            $this->_copyToPlatform($platformId);
        }
        return $this;
    }
    
    /**
     * 
     * @return SplFileInfo[]
     */
    public function getPlatforms()
    {
        $result = array();
//        $dir = new DirectoryIterator($this->getInstallDir(true));
        $dir = new DirectoryIterator($this->getLicenseDir(true)); // rastorguev fix
        foreach ($dir as $item)
        {            
            /* @var $item DirectoryIterator */
            if ($item->isFile() && $this->isPlatformFileName($item->getFilename()))
            {
                $result[] = $item->getFileInfo();
            }
        }
        return $result;
    }
    
    public function getPlatformPathes()
    {
        $pathes = array();
        foreach ($this->getPlatforms() as $item)
        {
            $platformId = $this->_castPlatformId($item);
            $pathes[] = dirname($item->getPathname()).'/'.$platformId.'/';
        }
        return $pathes;
    }
    
    protected function _checkPlatformId( $platformId , $path ) 
    {
        $dom = new DOMDocument('1.0');
        $dom->load($path); 
        if ($location = $dom->getElementsByTagName('location')->item(0))
        {
            /* @var $location DOMElement */
            if ($location->getAttribute('domain') == $this->tool()->getRealBaseUrl()
             && $location->getAttribute('path') == $this->_getLocationPath())
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _savePlatformData()
    {
        if ($platformId = $this->getPlatformId())
        {
//            $defaultInstallDir = $this->getInstallDir(true);
            $defaultInstallDir = $this->getLicenseDir(true); // rastorguev fix
            $path = $defaultInstallDir.$platformId.self::PLATFORMFILE_SUFFIX;
            $this->tool()->testMsg("Save platform path: ".$path);
            
            
            $dom = $this->getPlatformDom();
            $dom->save($path);
            if (!file_exists($path))
            {
                $msg = 'Write permissions required for: '.$defaultInstallDir.' and all files included.';
                throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($msg);
            }
        }
        return $this;
    }
    
    /**
     * 
     * Genarate platform DOM structure
     * @param $configData custom configuration data
     * @return DOMDocument
     */
    public function getPlatformDom($configData = array())
    {
        $data = array(
            'domain' => $this->tool()->getRealBaseUrl(),
            'path'   => $this->_getLocationPath(),
        ); 
        if ($configData) {
            $data = array_merge($data, $configData);
        }
        $dom = new DOMDocument('1.0');
        $platform = $dom->createElement('platform');
        $dom->appendChild($platform);
        $this->tool()->testMsg(array('try to save',$this->getData()));
        foreach ($this->getData() as $key => $value)
        {
            if (is_array($value))
            {
                continue;
            }
            $platform->appendChild($dom->createElement($key,$value));
        }
        $location = $dom->createElement('location');
        /* @var $location DOMElement */
        $location->setAttribute('domain',$data['domain']);
        $location->setAttribute('path',$data['path']);
        $platform->appendChild($location);
        
        return $dom;
    }
    
    private function _getLocationPath()
    {
        return $this->getInstallDir(true); 
        //return $this->getLicenseDir(true); // rastorguev fix 
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _copyToPlatform($platformId)
    {
//        $path = $this->getInstallDir(true);
        $path = $this->getLicenseDir(true); // rastorguev fix
        $platformPath = $path.$platformId.DS;
        if (!file_exists($platformPath))
        {
            $this->tool()->filesystem()->makeDirStructure($platformPath);
        }
        $dir = new DirectoryIterator($path);
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */ 
            $filename = $item->getFilename();
            if ($item->isFile() && !$this->isPlatformFileName($filename) 
             && $item->getFilename() != $this->tool()->getUrlFileName())
            {
                if (!$this->tool()->filesystem()->isWriteable($item->getPathname()))
                {
                    throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception("File does not have write permissions: ".$item->getPathname());
                }
                $to = $platformPath.$filename;
                if (file_exists($to) && $this->_isCopiedPlatformFile($item->getFilename()))
                {
                    $this->tool()->filesystem()->rmFile($item->getPathname());
                }
                else
                {
                    $this->tool()->filesystem()->moveFile($item->getPathname(),$to);
                }
            }
        }
        return $this;
    }
    
    private function _isCopiedPlatformFile( $file )
    {
        return in_array($file,$this->_copiedPlatformFiles);
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _removePlatform( $platformId )
    {
        $this->_modules = array();
        return $this->_copyFromPlatform($platformId);
    }
    
    /**
     * 
     * @param $platformId
     * @return Aitoc_Aitsys_Model_Platform
     */
    protected function _copyFromPlatform( $platformId )
    {
//        $path = $this->getInstallDir(true);
        $path = $this->getLicenseDir(true); //rastorguev fix
        $dir = new DirectoryIterator($path.$platformId);
        foreach ($dir as $item)
        {
            /* @var $item DirectoryIterator */
            $filename = $item->getFilename();
            if ($item->isFile() && '.php' !== substr($filename, -4))
            {
                $this->tool()->filesystem()->cpFile($item->getPathname(),$path.$filename);
                $this->_copiedPlatformFiles[] = $filename;
            }
        }
        return $this;
    }
    
}
