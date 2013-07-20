<?php

/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
class Aitoc_Aitsys_Model_Rewriter_Merge extends Aitoc_Aitsys_Model_Rewriter_Abstract
{
    
    protected $_latestMergedFiles = array();
    
    /**
    * Clearing current rewrites
    */
    public function clear()
    {
        $filesystem = new Aitoc_Aitsys_Model_Aitfilesystem();
        $filesystem->rmFile($this->_rewriteDir . 'config.php');
    }
    
    public function getLatestMergedFiles()
    {
        return $this->_latestMergedFiles;
    }
    
    public function makeDirStructure( $path , $isFile = true )
    {
        $path = str_replace('\\','/',$path);
        $basePath = dirname(Mage::getRoot()).'/';
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
    // last changes end
    
    /**
    * Merging one group of classed (for one alias)
    * 
    * @param array $classes
    * @return boolean
    */
    public function merge($classes)
    {
        $this->_latestMergedFiles = array();
        if (!is_array($classes) || empty($classes))
        {
            return false;
        }
        $filename = md5($classes[count($classes)-1]['child']);
        $filepath = $this->_rewriteDir . $filename . '.php';
        
        $fileContent = '';
        $fileContent .= '<?php' . "\r\n";
        $fileContent .= '/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */' . "\r\n";
        $rewriteClass = new Aitoc_Aitsys_Model_Rewriter_Class();
        $bOmitRewriteChain = false;
        $iEncodedCount = 0;
        
        //set count of encoded classes
        foreach ($classes as $parent => $class)
        {
            if (isset($class['encoded']) && $class['encoded']) {
                $iEncodedCount++;
            }
        }
        
        foreach ($classes as $parent => $class)
        {
            if ($bOmitRewriteChain) {
                continue;
            }
            $bEncodedCurrent = false;
            /**
            * Different strategy for normal and abstract rewrites
            */
            if (is_array($class))
            {
                // for abstract rewrites
                $contentFrom = $class['contents'];
                $parent      = $class['parent'];
                $child       = $class['child'];
                $bEncodedCurrent = isset($class['encoded']) && $class['encoded'] ? true : false;
                if ($bEncodedCurrent) {
                    $iEncodedCount--;
                }
            }
            else 
            {
                // parent is parent
                $contentFrom = $parent;
                $child       = $class;
            }
            
            /**
            * The last $child is always magento base class
            */
            $contents = $rewriteClass->getContents($contentFrom);
            $this->_latestMergedFiles[$contentFrom] = $rewriteClass->getClassPath($contentFrom);
            if ($child) // if child is empty - need to keep current child
            {
                $contents = preg_replace('/' . $contentFrom . '(\s+)(extends)?(\s+)?([^\s{]+)?/', "$parent $2 $child", $contents, 1);
            } 
            else 
            {
                $contents = preg_replace('/' . $contentFrom . '(\s+)(extends)?(\s+)?([^\s{]+)?/', "$parent $2 $4", $contents, 1);
            }
            if ($bEncodedCurrent) {
                if ($contentFrom != $parent) {
                    $bOmitRewriteChain = true;
                    continue;
                }
                else {
                    $contents = ($iEncodedCount > 0 ? '//':'') .'require_once("'.$rewriteClass->getClassPath($parent).'");';
                }
            }
            $fileContent .= $contents;
            $fileContent .= "\r\n\r\n";
        }
        if ($bOmitRewriteChain) {
            return false;
        }
        
        // last changes start
        $this -> makeDirStructure(dirname($filepath),false);
        // last changes end
        $chmod = !file_exists($filepath);
        $result = file_put_contents($filepath, $fileContent);
        if ($chmod)
        {
            $this->grantAll($filepath);
        }
        
        return $filename;
    }
}