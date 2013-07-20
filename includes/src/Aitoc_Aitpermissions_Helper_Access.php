<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Helper/Access.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ mpDjoCqeiIceIsjM('1d2f8779075e70019b6e2ebc32e40d31'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Helper_Access extends Mage_Core_Helper_Abstract
{
    /**
    * Sets store_id's for cms object, keeping in mind that unavailable stores are 
    * not visible in multiselect, but should not dissapear after save
    * 
    * @param object $objectToModify
    * @param object $objectCurrent
    */
    public function setCmsObjectStores($object)
    {
        if (!$object->hasDataChanges()) {
            return;
        }
        
        $origData = $object->getOrigData();
        $saveData = $object->getData();
        
        $objectIsNew = empty($origData);
        
        $allowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
        
        switch (get_class($object))
        {
            case 'Mage_Cms_Model_Page':
            {
                $tosaveStoreIds = $saveData['stores'];
                
                if (!$objectIsNew)
                {
                    $originalStoreIds = $origData['store_id'];
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                    
                $object->setData('stores', $tosaveStoreIds);
                
                break;
            }
            case 'Mage_Cms_Model_Block':
            {
                $tosaveStoreIds = $saveData['stores'];

                if (!$objectIsNew)
                {
                    $originalStoreIds = $origData['store_id'];
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                
                $object->setData('stores', $tosaveStoreIds);
                
                break;
            }
            case 'Mage_Widget_Model_Widget_Instance':
            {
                $tosaveStoreIds = explode(',', $saveData['store_ids']);
                
                if (!$objectIsNew)
                {
                    $originalStoreIds = explode(',', $origData['store_ids']);
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                
                $object->setData('store_ids', implode(',', $tosaveStoreIds));
                
                break;
            }
            case 'Mage_Poll_Model_Poll':
            {
                $tosaveStoreIds = $saveData['store_ids'];
                
                if (!$objectIsNew)
                {
                    $originalStoreIds = Mage::getModel('poll/poll')->load($object->getPollId())->getStoreIds();
                    $preserveStoreIds = array_diff($originalStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_intersect($tosaveStoreIds, $allowedStoreviews);
                    $tosaveStoreIds = array_unique(array_merge($preserveStoreIds, $tosaveStoreIds));
                }
                
                $object->setData('store_ids', $tosaveStoreIds);
                
                break;
            }
            default: 
            {
                break;
            }
        }
    }
    
    /**
    * Checks if specified website id is allowed for access
    * 
    * @param integer $websiteId
    */
    public function isWebsiteAllowed($websiteId)
    {
        if (!$websiteId)
        {
            return true;
        }
        if (Mage::helper('aitpermissions')->isScopeStore())
        {
            return false;
        }
        if (!in_array($websiteId, Mage::helper('aitpermissions')->getAllowedWebsites()))
        {
            return false;
        }
        return true;
    }
    
    /**
    * Checks if specified group id is allowed for access
    * 
    * @param integer $groupId
    */
    public function isGroupAllowed($groupId)
    {
        if (!$groupId)
        {
            return true;
        }
        $AllowedStores = Mage::helper('aitpermissions')->getAllowedStores();
        if (!in_array($groupId, $AllowedStores)) 
        {
        	return false;
        }
        return true;
    }
    
    /**
    * Checks if specified store id(s) allowed
    * 
    * @param integer|array $storeId
    */
    public function isStoreIdAllowed($storeId)
    {
        if (!$storeId || (array_key_exists(0, $storeId) && !$storeId[0]))
        {
            return true;
        }
        if (!is_array($storeId))
        {
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if (!in_array($storeId, $AllowedStoreviews)) 
            {
            	return false;
            }
        }
        return true;
    }
} } 