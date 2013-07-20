<?php
class Aitoc_Aitsys_Model_Rewriter_Mage_Core_Block_Template extends Mage_Core_Block_Template
{
    
    public function fetchView($fileName)
    {
        $currentViewDir = $this->_viewDir;
        if (false !== strpos($fileName, 'aitcommonfiles'))
        {
            if (Mage::getStoreConfigFlag('aitsys/patches/use_dynamic') 
              || !file_exists($currentViewDir . DS . $fileName)) // if there is a file in app/, we should do nothing. will use it.
            {
                $newViewDir = Mage::getBaseDir('var') . DS . 'ait_patch' . DS . 'design';
                if (file_exists($newViewDir . DS . $fileName))
                {
                    $this->_viewDir = $newViewDir; // replacing view dir
                } 
                else
                {
                    // also trying with 'default' folder instead of 'base' (for compatibility with 1.3 and 1.4 in one version)
                    $fileNameDef = str_replace(DS . 'base' . DS, DS . 'default' . DS, $fileName);
                    if (file_exists($newViewDir . DS . $fileNameDef))
                    {
                        $this->_viewDir = $newViewDir; // replacing view dir
                        $fileName = $fileNameDef; // forcing use 'default' instead of 'base'
                    }
                }
            }
        }
        
        Varien_Profiler::start($fileName);

        extract ($this->_viewVars);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        if ($this->getShowTemplateHints()) {
            echo '<div style="position:relative; border:1px dotted red; margin:6px 2px; padding:18px 2px 2px 2px; zoom:1;"><div style="position:absolute; left:0; top:0; padding:2px 5px; background:red; color:white; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="'.$fileName.'">'.$fileName.'</div>';
            if (self::$_showTemplateHintsBlocks) {
                $thisClass = get_class($this);
                echo '<div style="position:absolute; right:0; top:0; padding:2px 5px; background:red; color:blue; font:normal 11px Arial; text-align:left !important; z-index:998;" onmouseover="this.style.zIndex=\'999\'" onmouseout="this.style.zIndex=\'998\'" title="'.$thisClass.'">'.$thisClass.'</div>';
            }
        }

        include $this->_viewDir.DS.$fileName;

        if ($this->getShowTemplateHints()) {
            echo '</div>';
        }

        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        Varien_Profiler::stop($fileName);
        
        #$html = parent::fetchView($fileName);
        
        $this->_viewDir = $currentViewDir; // returning default dir back
        return $html;
    }
}