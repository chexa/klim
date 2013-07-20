<?php

class Aitoc_Aitsys_Model_Module_License_Upgrade  extends Aitoc_Aitsys_Abstract_Model
{
    
    /**
     * 
     * @var Aitoc_Aitsys_Model_Module_License
     */
    protected $_license;
    
    protected $_hasUpgrade = false;
    
    public function __construct( Aitoc_Aitsys_Model_Module_License $license )
    {
        $this->_license = $license;
        parent::__construct();
        $this->reset();
    }
    
    protected function _getUpgradePath()
    {
        return str_replace('.xml','.upgrade-license.xml',$this->_getInstallPath());
    }
    
    protected function _getInstallPath()
    {
        #echo 999;
        #Zend_Debug::dump($this->getModule()->getInstall()->getData());
        #echo $this->getModule()->getInstall()->getPath();
        return $this->getModule()->getInstall()->getPath();
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function reset()
    {
        $this->setData(array());
        $path = $this->_getUpgradePath();
        #echo 888; 
        #echo $path;
        if (file_exists($path))
        {
            $this->_loadFile($path);
        }
        return $this;
    }
    
    protected function _loadFile( $path )
    {
        $module = $this->getModule();
        $xml = simplexml_load_file($path);
        if ((string)$xml->serial == $this->_license->getPurchaseId())
        {
            return;
        }
        $key = (string)$xml->product->attributes()->key;
        $constraint = array();
        foreach ($xml->constraint->children() as $child)
        {
            /* @var $child SimpleXMLElement */
            $value = (string)$child;
            if ('' === $value || null === $value)
            {
                $value = null;
            }
            $constraint[$child->getName()] = array(
            	'value' => $value ,
                'label' => (string)$child['label']
            ); 
        }
        $this->addData(array(
            'id' => (int)$xml->product->attributes()->id ,
            'label' => (string)$xml->product ,
            'key' => $key ,
            'version' => $module->getVersion() ,
            'purchase_id' => (string)$xml->serial ,
            'license_id' => (int)$xml->product->attributes()->license_id ,
            'constraint' => $constraint
        ));
        $this->_hasUpgrade = true;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module
     */
    public function getModule()
    {
        return $this->_license->getModule();
    }
    
    public function canUpgrade()
    {
        if (!$this->_hasUpgrade)
        {
            return false;
        }
        $module = $this->getModule();
        $currentConstrain = $this->_license->getConstrain();
        $constraint = $this->getConstraint();
        if (sizeof($currentConstrain) != sizeof($constraint))
        {
            return false;
        }
        $canUpgrade = false;
        foreach ($constraint as $type => $value)
        {
            $currentConstrain[$type] = $currentConstrain[$type]['value'];
            $value = $value['value'];
            if (!isset($currentConstrain[$type]))
            {
                return false;
            }
            if (null !== $value)
            {
                if ($currentConstrain[$type] > $value)
                {
                    return false;
                }
                else
                {
                    $canUpgrade = true;
                }
            }
            else
            {
                $canUpgrade = true;
            }
        }
        if($canUpgrade)
        {
            return $this->_license->isInstalled();
        }
        return false;
    }
    
    public function hasUpgrade()
    {
        return $this->_hasUpgrade;
    }
    
    /**
     * 
     * @return Aitoc_Aitsys_Model_Module_License_Upgrade
     */
    public function upgrade()
    {
        if ($this->canUpgrade())
        {
            $upgrade = array(
            	'upgrade_purchaseid' => $this->getPurchaseId() ,
                'purchaseid' => $this->_license->getPurchaseId()
            );
            $service = $this->_license->getService();
            $service->connect();
            $service->upgradeLicense($upgrade);
            $service->disconnect();
            $installXML = simplexml_load_file($this->_getInstallPath());
            $upgradeXML = simplexml_load_file($this->_getUpgradePath());
            $installXML->serial = (string)$upgradeXML->serial;
            $installXML->product['license_id'] = (string)$upgradeXML->product['license_id'];
            foreach ($upgradeXML->constraint->children() as $child)
            {
                $installXML->constraint->{$child->getName()} = (string)$child;
            }
            $installXML->asXML($this->_getInstallPath());
            $this->tool()->filesystem()->rmFile($this->_getUpgradePath());
            $this->getModule()->reset();
        }
        else
        {
            throw new Aitoc_Aitsys_Model_License_Service_Exception("Can`t upgrade to this license!");
        }
        return $this;
    }
    
}