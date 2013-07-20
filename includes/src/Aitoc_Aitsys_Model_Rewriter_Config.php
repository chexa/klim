<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 * @author Andrei
 */
class Aitoc_Aitsys_Model_Rewriter_Config extends Aitoc_Aitsys_Model_Rewriter_Abstract
{
    protected $_configFile = '';
    
    protected $_configContent = array();
    
    public function __construct()
    {
        parent::__construct();
        $this->_configFile = $this->_rewriteDir . 'config.php';
        $this->prepare();
    }
    
    public function prepare()
    {
        $this->_configContent = array();
    }
    
    public function add($mergedFilename, $rewriteClasses)
    {
        if (is_array($rewriteClasses))
        {
            foreach ($rewriteClasses as $class)
            {
                $this->_configContent[$class] = $mergedFilename;
            }
        } 
        elseif (is_string($rewriteClasses)) // will be string for abstract class rewrites
        {
            $this->_configContent[$rewriteClasses] = $mergedFilename;
        }
    }
    
    public function commit()
    {
        $content = $this->tool()->toPhpArray($this->_configContent,'rewriteConfig');
        $content = "<?php\n\n".$content;
        $chmod = !file_exists($this->_configFile);
        file_put_contents($this->_configFile,$content);
        if ($chmod)
        {
            $this->grantAll($this->_configFile);
        }
    }
}