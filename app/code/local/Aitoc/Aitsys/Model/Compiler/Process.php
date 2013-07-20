<?php

class Aitoc_Aitsys_Model_Compiler_Process extends Mage_Compiler_Model_Process
{
    
    public function run()
    {
        $this->_getSession()->addWarning('Class-batch compilation engine was disabled for stable work of the AITOC extensions. This won\'t impact the performance of your Magento.');
        return parent::run();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
    
    public function getCompileClassList()
    {
        $autoloader = Aitoc_Aitsys_Model_Rewriter_Autoload::instance();
        $arrFiles = parent::getCompileClassList();
        foreach ($arrFiles as $scope => $classes)
        {
            foreach ($classes as $index => $class)
            {
                if ($autoloader->hasClass($class))
                {
                    unset($arrFiles[$scope][$index]);
                }
            }
        }
        return $arrFiles;
    }
    
    protected function _compileFiles()
    {
        return $this;
    }

    protected function _getClassesSourceCode($classes, $scope)
    {
        $autoloader = Aitoc_Aitsys_Model_Rewriter_Autoload::instance();
        $sortedClasses = array();
        foreach ($classes as $className) {
            $implements = array_reverse(class_implements($className));
            foreach ($implements as $class) {
                if (!in_array($class, $sortedClasses) && !in_array($class, $this->_processedClasses) && strstr($class, '_')) {
                    $sortedClasses[] = $class;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $class;
                    }
                }
            }
            $extends    = array_reverse(class_parents($className));
            foreach ($extends as $class) {
                if (!in_array($class, $sortedClasses) && !in_array($class, $this->_processedClasses) && strstr($class, '_')) {
                    $sortedClasses[] = $class;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $class;
                    }
                }
            }
            if (!in_array($className, $sortedClasses) && !in_array($className, $this->_processedClasses)) {
                $sortedClasses[] = $className;
                    if ($scope == 'default') {
                        $this->_processedClasses[] = $className;
                    }
            }
        }

        $classesSource = "<?php\n";
        $rewritedClasses = $autoloader->getRewritedClasses();
        foreach ($sortedClasses as $className) {
            if (in_array($className,$rewritedClasses))
            {
                continue;
            }
            $file = $this->_includeDir.DS.$className.'.php';
            if (!file_exists($file)) {
                continue;
            }
            $content = file_get_contents($file);
            $content = ltrim($content, '<?php');
            $content = rtrim($content, "\n\r\t?>");
            $classesSource.= 
            "\n\rif (!class_exists('".$className."',false) && !interface_exists('".$className."',false)) {\n\r".$content."\n\r}\n\r";
        }
        return $classesSource;
    }
    
    protected function _copy($source, $target, $firstIteration = true)
    {
        parent::_copy($source,$target,$firstIteration);
        if (is_file($target) && file_exists($target))
        {
            $platform = Aitoc_Aitsys_Abstract_Service::get()->platform();
            $dirs = $platform->getModuleDirs();
            foreach ($dirs as $dir)
            {
                if ($item = strstr($source,$dir))
                {
                    if ($item != $dir)
                    {
                        file_put_contents(
                            $target ,
                            '<?php class_exists("Mage_Adminhtml_Controller_Action"); require_once "'.$source.'";?>'
                        );
                        return $this;
                    }
                }
            }
            $name = pathinfo($target,PATHINFO_FILENAME);
            $loader = Aitoc_Aitsys_Model_Rewriter_Autoload::instance();
            if ($loader->hasClass($name))
            {
                $path = $loader->getRewriteDir();
                $source = <<<SOURCE
<?php
class_exists("Mage_Adminhtml_Controller_Action");
function _{$name}_autoloader( \$class )
{
    foreach (glob('{$path}*') as \$path)
    {
        require_once \$path;
    }
}

if (defined('AITSYS_REWRITED')) return;
spl_autoload_register('_{$name}_autoloader');
_{$name}_autoloader(null);
spl_autoload_unregister('_{$name}_autoloader');
define('AITSYS_REWRITED',true);
SOURCE;
                file_put_contents($target,'');
            }
        }
        return $this;
    }
    
}