<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Adminhtml/Store/Switcher.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ZqWmUNhEoufyAsPa('539b343f2351b843828d299c70301923'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

/**
 * @todo VERY DIRTY CODE. Needs complete Refactoring
 * @author yahnenko@aitoc.com
 * @author ksenevich@aitoc.com
 */
class Aitoc_Aitpermissions_Block_Adminhtml_Store_Switcher extends Mage_Adminhtml_Block_Catalog_Category_Tree // Mage_Adminhtml_Block_Store_Switcher
{
    /**
     * @var array
     */
    protected $_storeIds;
    protected $_storeCategories = null;
    protected $_selectedNodes = null;
    private $_rootNodeTreesStorage = array();
    private $_storeViewStores = array();
    
    /**
     * @var bool
     */
    protected $_hasDefaultOption = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aitpermissions/store_switcher.phtml');
        $this->setUseConfirm(true);
        $this->setUseAjax(true);
        $this->setDefaultStoreName($this->__('All Store Views'));
    }

    /**
     * Get websites
     *
     * @return array
     */
    public function getWebsites()
    {
        $websites = Mage::app()->getWebsites();
        if ($websiteIds = $this->getWebsiteIds()) {
            foreach ($websites as $websiteId => $website) {
                if (!in_array($websiteId, $websiteIds)) {
                    unset($websites[$websiteId]);
                }
            }
        }
        return $websites;
    }

    /**
     * Get store groups for specified website
     *
     * @param Mage_Core_Model_Website $website
     * @return array
     */
    public function getStoreGroups($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::app()->getWebsite($website);
        }
        return $website->getGroups();
    }

    /**
     * Get store views for specified store group
     *
     * @param Mage_Core_Model_Store_Group $group
     * @return array
     */
    public function getStores($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) 
        {
            $group = Mage::app()->getGroup($group);
        }
        $stores = $group->getStores();
  
        return $stores;
    }
    
    public function getAllStores()
    {
        $stores = array();
        foreach ($this->getWebsites() as $website)
        {
            foreach ($website->getGroups() as $group)
            {
                $stores[$group->getId()] = $group;
                $rootcategory = Mage::getModel('catalog/category')->load($group->getRootCategoryId());
                $stores[$group->getId()]['RootCategory'] = $rootcategory;
            }
        }
        return $stores;
    }
    
    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current' => true, 'store' => null));
    }

    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    public function getStoreIds()
    {
        if (!isset($this->_storeIds)) 
        {
            $this->_storeIds = array();
            $currentRoles = Mage::getModel('aitpermissions/advancedrole')->getCollection()->loadByRoleId(Mage::app()->getRequest()->getParam('rid'));
            foreach ($currentRoles as $role)
            {
                $this->_storeIds = array_merge($this->_storeIds, $role->getStoreviewIdsArray());
            }
        }
        return $this->_storeIds;
    }

    public function isShow()
    {
        return true;
    }

    protected function _toHtml()
    {
        return Mage_Adminhtml_Block_Template::_toHtml();
    }
    
    public function isReadonly()
    {
        return false;
    }
    
    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function hasDefaultOption($hasDefaultOption = null)
    {
        if (null !== $hasDefaultOption) 
        {
            $this->_hasDefaultOption = $hasDefaultOption;
        }
        return $this->_hasDefaultOption;
    }

    /**  
     * @param int $storeGroupId
     */
    protected function getCategoryIds($storeGroupId)
    {
        if (!$this->_storeCategories)
        {
            $storeCategories = Mage::registry('store_categories');
            $this->_storeCategories = array();
            foreach ($storeCategories->getItems() as $item)
            {
                $this->_storeCategories[$item->getStoreId()] = explode(',', $item->getCategoryIds());
            }
        }

        if (isset($this->_storeCategories[$storeGroupId]))
        {
            return $this->_storeCategories[$storeGroupId];
        }

        return array();
    }

    public function getIdsString($storeGroupId = null)
    {
        return join(',', $this->getCategoryIds($storeGroupId));
    }

    // tree functions
    
    public function getRootNode($storeId = null)
    {
        $storeGroupId = $this->_getStoreGroupId($storeId);
        $root = $this->getRoot(null, 3, $storeId);
        $root->setIsVisible(true);
        if ($root && in_array($root->getId(), $this->getCategoryIds($storeGroupId))) 
        {
            $root->setChecked(true);
        }
        return $root;
    }

    public function getRoot($parentNodeCategory=null, $recursionLevel=3, $storeId = null)
    {
        if (!is_null($parentNodeCategory) && $parentNodeCategory->getId()) 
        {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }

        $rootId       = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        $storeGroupId = $this->_getStoreGroupId($storeId);
        if ($storeId) 
        {
            $store  = Mage::getModel('core/store_group')->load($storeGroupId);
            $rootId = $store->getRootCategoryId();
        }

        $ids = $this->getSelectedCategoriesPathIds($rootId, $storeId);
        $tree = Mage::getResourceSingleton('catalog/category_tree')->loadByIds($ids, false, false);

        if ($this->getCategory()) 
        {
            $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
        }
        
        $tree->addCollectionData($this->getCategoryCollection());
        
        $root = $tree->getNodeById($rootId);

        if ($root && $rootId != Mage_Catalog_Model_Category::TREE_ROOT_ID) 
        {
            $root->setIsVisible(true);
            if ($this->isReadonly()) 
            {
                $root->setDisabled(true);
            }
        }
        elseif($root && $root->getId() == Mage_Catalog_Model_Category::TREE_ROOT_ID) 
        {
            $root->setName(Mage::helper('catalog')->__('Root'));
        }
        return $root;
    }

    public function getTreeJson($parenNodeCategory = null, $storeId = null) // begin
    {
        $storeGroupId = $this->_getStoreGroupId($storeId);
        $rootArray    = $this->_getNodeJson($this->getRoot($parenNodeCategory, 3, $storeId), 1, $storeId);
        $json         = Zend_Json::encode(isset($rootArray['children']) ? $rootArray['children'] : array());

        return $json;
    }
    
    /**
     * Get category name
     *
     * @param Varien_Object $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->htmlEscape($node->getName());
        if ($this->_withProductCount) {
             $result .= ' (' . $node->getProductCount() . ')';
        }
        return $result;
    }
    
    public function getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        return Mage::app()->getStore($storeId);
    }
    
    protected function _getNodeJson($node, $level = 0, $storeId = null)
    {
        $storeGroupId = $this->_getStoreGroupId($storeId);

        // create a node from data array
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $item = array();
        $item['text'] = $this->buildNodeName($node);

        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
        $item['store']  = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = false; //$allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = false; //$allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = $this->_isParentSelectedCategory($node, $storeId);

        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level+1, $storeId);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        $aCategories = array();
        if ($storeId) 
        {
        	$currentRoles = Mage::getModel('aitpermissions/advancedrole')->getCollection()->loadByRoleId($this->getRequest()->getParam('rid'));
            foreach ($currentRoles as $role)
            {
                if ($role->getStoreId() == $storeGroupId) 
                {
                    $aCategories = array_merge($aCategories, $role->getCategoryIdsArray());
                }
            }
        }

        if (in_array($node->getId(), $aCategories)) 
        {
            $item['checked'] = true;
        }
        
        return $item;
    }

    protected function _isParentSelectedCategory($node, $storeId = null)
    {
        foreach ($this->_getSelectedNodes($storeId) as $selected) 
        {
            if ($selected) 
            {
                $pathIds = explode('/', $selected->getPathId());
                if (in_array($node->getId(), $pathIds)) 
                {
                    return true;
                }
            }
        }
        return false;
    }

    protected function _getSelectedNodes($storeId = null)
    {
        $this->_selectedNodes = array();
        $storeGroupId = $this->_getStoreGroupId($storeId);
        foreach ($this->getCategoryIds($storeGroupId) as $categoryId) 
        {
            if (!isset($this->_rootNodeTreesStorage[$storeGroupId]))
            {
                $this->_rootNodeTreesStorage[$storeGroupId] = $this->getRoot(null, 3, $storeId)->getTree(null, $storeId);
            }
            $this->_selectedNodes[] = $this->_rootNodeTreesStorage[$storeGroupId]->getNodeById($categoryId);
        }

        return $this->_selectedNodes;
    }

    public function getTree($parenNodeCategory=null, $storeId = null)
    {
       	$rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory, 3, $storeId), 1, $storeId); //2
        $tree = isset($rootArray['children']) ? $rootArray['children'] : array();
        return $tree;
    }

    public function getCategoryChildrenJson($categoryId, $storeId = null) // main tree renderer
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1, $storeId)->getTree(null, $storeId)->getNodeById($categoryId);
        if (!$node || !$node->hasChildren()) {
            return '[]';
        }
        $children = array();
        foreach ($node->getChildren() as $child) {
            $children[] = $this->_getNodeJson($child);
        }
        return Zend_Json::encode($children);
    }

    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('aitpermissions/adminhtml_categories/categoriesJson', array('_current'=>true));
    }
    
    public function getSelectedCategoriesPathIds($rootId = false, $storeId = null)
    {
        $ids = array();
        $collection = Mage::getModel('catalog/category')->getCollection();
        
        if ($storeId) 
        {
            $group = Mage::getModel('core/store_group')->load($this->_getStoreGroupId($storeId));
            $RootCategoryId = $group->getRootCategoryId();
            $collection->addFieldToFilter('parent_id', $RootCategoryId);
        }
        
        foreach ($collection as $item) 
        {
            if ($rootId && !in_array($rootId, $item->getPathIds())) 
            {
                continue;
            }
            foreach ($item->getPathIds() as $id) 
            {
                if (!in_array($id, $ids)) 
                {
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    /** Gets store Id by store view Id
     * 
     * @param int $storeId store-view ID
     * @return int Store group Id
     */
    protected function _getStoreGroupId($storeId)
    {
        if (!isset($this->_storeViewStores[$storeId]))
        {
            $this->_storeViewStores[$storeId] = Mage::getModel('core/store')->load($storeId)->getGroupId();
        }

        return $this->_storeViewStores[$storeId];
    }
} } 