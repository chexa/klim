<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Advancedrole.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ yojeihpBwcCBcsem('5dfd587af29eb7b3d8180f087bcbc137'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Model_Advancedrole extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('aitpermissions/advancedrole');
    }

    /**
     * 
     * @return array
     */
    public function getAllowedStoreViews()
    {
        $storeviewIds = array();
        if ($this->getWebsiteId())
        {
            $storeCollection = Mage::getModel('core/store')->getCollection()
                ->addWebsiteFilter($this->getWebsiteId());

            foreach ($storeCollection as $store)
            {
                $storeviewIds[] = $store->getStoreId();
            }
        }
        else
        {
            $storeviewIds = $this->getStoreviewIdsArray();
        }

        return $storeviewIds;
    }

    /**
     * 
     * @return array
     */
    public function getAllowedStores()
    {
        $storeIds = array();
        if ($this->getWebsiteId())
        {
            $storeCollection = Mage::getModel('core/store_group')->getCollection()->addWebsiteFilter($this->getWebsiteId());
            foreach ($storeCollection as $store)
            {
                $storeIds[] = $store->getStoreId();
            }
        }
        else
        {
            $storeIds[] = $this->getStoreId();
        }
        return $storeIds;
    }

    /**
     * 
     * @return array
     */
    public function getStoreviewIdsArray()
    {
        if (!$this->getStoreviewIds() || '0' == $this->getStoreviewIds())
        {
            return array();
        }
        return explode(',', $this->getStoreviewIds());
    }

    /**
     * 
     * @return array
     */
    public function getCategoryIdsArray()
    {
        if (!$this->getCategoryIds() || '0' == $this->getCategoryIds())
        {
            return array();
        }
        return explode(',', $this->getCategoryIds());
    }

    /**
     * 
     * @return array
     */
    public function getAllowedCategories()
    {
        $categoryIds = array();
        if ($this->getWebsiteId())
        {
            $categoryIds = $this->getWebsiteCategories($this->getWebsiteId());
        }
        else
        {
            $RootCategories = $this->getCategoryIdsArray();
            if (!empty($RootCategories)) 
            {
            	$categoryIds = $RootCategories;
            
                foreach ($RootCategories as $RootCategory)
                {
                    $ChildCategoryIds = Mage::getModel('catalog/category')->load($RootCategory)->getAllChildren(true);
                    $categoryIds = array_merge($categoryIds, $ChildCategoryIds);
                }
            }
        }
        return $categoryIds;
    }

    /**
     * 
     * @return array
     */
    public function getWebsiteCategories()
    {
        $groupCollection = Mage::getModel('core/store_group')->getCollection()->addWebsiteFilter($this->getWebsiteId())->load();
        $categoryIds = array();
        foreach ($groupCollection as $group)
        {
            $categoryIds[] = $group->getRootCategoryId();
        }

        if ($categoryIds)
        {
            $categories = $categoryIds;
            foreach ($categories as $rootCaregoryId)
            {
                $rootCategory = Mage::getModel('catalog/category')->load($rootCaregoryId);

                $childCategories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToSelect('entity_id')
                    ->addAttributeToFilter('path', array('like' => $rootCategory->getPath().'/%'))
                    ->load();
                foreach ($childCategories as $childCategory)
                {
                    $categoryIds[] = $childCategory->getId();
                }
            }
        }

        return array_unique($categoryIds);
    }
} } 