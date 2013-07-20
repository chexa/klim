<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */

class Aitoc_Aitsys_Model_Aitfilesystem extends Aitoc_Aitsys_Abstract_Model
{
    /**
     * Makes temporary file in var/ folder
     *
     * @param string $sFromFile
     * @return string file path
     */
    public function makeTemporary($sFromFile)
    {
        $oConfig = Mage::getConfig();
        $sFileType = substr($sFromFile, strrpos($sFromFile, '.'));
        $sTemp = $oConfig->getOptions()->getVarDir() . DS . uniqid(time()) . $sFileType;
        copy($sFromFile, $sTemp);
        return $sTemp;
    }
    
    public function getLocalDir()
    {
        return Mage::getConfig()->getOptions()->getCodeDir().'/local/';
    }
    
    public function getEtcDir()
    {
        return Mage::getConfig()->getOptions()->getEtcDir().'/modules';
    }
    
    public function getBaseDir()
    {
        return Mage::getConfig()->getOptions()->getBaseDir().DS;
    }
    
    public function getInstallDir()
    {
        return $this->tool()->platform()->getInstallDir();
    }
    
    public function getAitsysDir()
    {
        return dirname($this->tool()->platform()->getInstallDir(true)).'/';
    }
    
    public function mkDir($sPath)
    {
        return $this->makeDirStructure($sPath,false);
    }
    
    public function makeDirsDiff($sOrigDir, $sChangedDir, $sPatchFilePath)
    {
        $sCmd = 'diff -aurBb ' . $sOrigDir . ' ' . $sChangedDir . ' > ' . $sPatchFilePath;
        exec($sCmd);
        @chmod($sPatchFilePath, 0777);
        return $this;
    }
    
    public function grantAll( $path , $recursive = true )
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
        return $this;
    }
    
    /**
     * Removes file
     *
     * @param string $sPath
     */
    public function rmFile($sPath)
    {
        if (file_exists($sPath) && $this->isWriteable($sPath))
        {
            if (is_file($sPath))
            {
                @unlink($sPath);
            }
            else
            {
                @rmdir($sPath);
            }
            return !file_exists($sPath);
        }
    }
    
    public function moveFile($source, $destination) 
    {
        $this->cpFile($source,$destination);
        return $this->rmFile($source);
    }
    
    /**
     * Copy file. Makes directory structure if not exists.
     *
     * @param string $sSource
     * @param string $sDestination
     */
    public function cpFile($sSource, $sDestination,$exc=false)
    {
        $this->makeDirStructure($sDestination);
        $res = @copy($sSource, $sDestination);
        if(false ===  $res && $exc)
        {
            $msg = "Can't copy ".$sSource." to ".$sDestination;
            if (file_exists($sDestination) && !$this->isWriteable($sDestination,true))
            {
                $msg .= ' - desitnation path is not writeable';
            }
            $msg .= '.';
            throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($msg);
        }
        else
        {
            $this->grantAll($sDestination);
        }
        return $res;
    }
    
    /**
     * Makes directory structure and sets permissions
     *
     * @param string $sPath
     */
    public function makeDirStructure( $path , $isFile = true )
    {
        $path = str_replace('\\','/',$path);
        $basePath = str_replace('\\','/',$this->getBaseDir());
        $pathItems = explode('/',substr($path,strlen($basePath)));
        if ($isFile)
        {
            array_pop($pathItems);
        }
        foreach ($pathItems as $dir)
        {
            if (!$dir) 
            {
                continue;
            }
            $basePath .= $dir.'/';
            if (!@file_exists($basePath) or (@file_exists($basePath) and !@is_dir($basePath)))
            {
                @mkdir($basePath, 0777);
                $this->grantAll($basePath);
            }
        }
        return $this;
    }
    
    public function getAitocModulesDir()
    {
        if (!$this->hasData('aitoc_modules_dir'))
        {
            $dir = dirname(dirname(dirname(__FILE__))).DS;
            $this->setAitocModulesDir($dir);
        }
        return $this->getData('aitoc_modules_dir');
    }
    
    public function getAitocModulesDirs()
    {
        if (!$this->hasData('aitoc_modules_dirs'))
        {
            $result = array();
            $base = dirname($this->getAitocModulesDir()).DS;
            foreach ($this->tool()->platform()->getModuleDirs() as $dir)
            {
                $result[] = $base.$dir.DS;
            }
            $this->setData('aitoc_modules_dirs',$result);
        }
        return $this->getData('aitoc_modules_dirs');
    }
    
    public function checkWriteable( $path , $exception = false )
    {
        if (!$path) return false;

        if (file_exists($path))
        {
            if (!$this->isWriteable($path))
            {
                if ($exception)
                {
                    if (is_file($path))
                    {
                        $this->_exception($path);
                    }
                    else 
                    {
                        $this->_exception($path);
                    }
                }
                return false;
            }
        }
        else 
        {
            if (!$this->isWriteable($path))
            {
                if ($exception)
                {
                    $this->_exception($path);
                }
                return false;
            }
        }
        
        return true;
    }
    
    protected function _exception( $msg )
    {
        throw new Aitoc_Aitsys_Model_Aitfilesystem_Exception($msg);
    }
    
    public function isWriteable($sPath, $bCheckParentDirIfNotExists = true)
    {
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
    
    public function isFileWritable($sPath)
    {
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
    
    public function isDirWritable($sPath)
    {
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
    
    public function emptyDir($dirname = null)
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

	/**
	 * Gets all the subdirectories off the $directoryPath
	 *
	 * @param string $directoryPath
	 * @return array
	 */
	public function getSubDir($directoryPath)
	{
		if (!is_dir($directoryPath))
		{
			return array();
		}

		$directories = array();
        $directoryPath = rtrim($directoryPath,'/\\').'/';
		$dir = dir($directoryPath);

		while (false !== ($file = $dir->read()))
		{
			if (!in_array($file,array('.','..','.svn')) && is_dir($directoryPath.$file))
			{
				$directories[] = $file;
			}
		}

		$dir->close();

		return $directories;
	}

	/**
	 * Searches for most suitable patch file in the directory ("PATH_TO_MODULE/data/" as a rule)
	 *
	 * @param string $fileName
	 * @param string $directory
	 * @return Varien_Object 
	 */
	public function getPatchFilePath($fileName, $directory)
    {
		$data = array();

        if (!$fileName || !$directory)
		{			
            $data['is_error'] = true;
			$data['file_path'] = __('Unknown');
            return new Varien_Object($data);
		}
        
        $directory = rtrim($directory,'/\\').'/';

		$lastSuccessIndex = null;	

		$subDirectories = $this->getSubDir($directory);        

		if ($subDirectories)
		{
			uasort($subDirectories, array($this, 'sortSubdirectories'));

			// Array bounds added for convenience
			array_unshift($subDirectories, 0);
			array_push($subDirectories, 100000);

			for ($i = 0; $i < count($subDirectories); $i++)
			{
				$result = version_compare($subDirectories[$i], Mage::getVersion());
				$currentFile = $directory . $subDirectories[$i] . DIRECTORY_SEPARATOR . $fileName;

				if (0 == $result)
				{
					if (is_file($currentFile))
					{
						$lastSuccessIndex = $i;
						break;
					}
				}
				elseif ((-1) == $result)
				{
					if (is_file($currentFile))
					{
						$lastSuccessIndex = $i;
					}
				}
				elseif (1 == $result)
				{
					if (is_null($lastSuccessIndex) && is_file($currentFile))
					{
						$lastSuccessIndex = $i;
						break;
					}
				}
			}
		}
        elseif (is_file($directory . $fileName))
        {            
            $data['is_error'] = false;
            $data['file_path'] = $directory . $fileName;
            return new Varien_Object($data);
        }

		if (is_null($lastSuccessIndex))
		{
			$data['is_error'] = true;
			$data['file_path'] = $directory . $fileName;
		}
		else
		{
			$data['is_error'] = false;
			$data['file_path'] = $directory . $subDirectories[$lastSuccessIndex] . DIRECTORY_SEPARATOR . $fileName;
		}

		return new Varien_Object($data);
	}

	/**
	 * Wrapper for version_compare
	 *
	 * @param string $directoryA
	 * @param string $directoryB
	 * @return mixed
	 */

	public function sortSubdirectories($directoryA, $directoryB)
	{
		return version_compare($directoryA, $directoryB);
	}
}
