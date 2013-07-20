<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Model/Observer.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ MqZDpchjoUIjUsDg('d76c96f3fb3071fdff85ef4c9fa96180'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

class Aitoc_Aitpermissions_Model_Observer
{
    /**
     * @var Aitoc_Aitpermissions_Helper_Data
     */
    protected $_helper;

    public function __construct()
    {
        $this->_helper = Mage::helper('aitpermissions');
    }

    public function saveAdvancedRole($observer)
    {
        $role = $observer->getObject();
        $request = Mage::app()->getRequest();
        $roleId = $role->getId();

        if ($roleId)
        {
            // Cleaning
            $advancedroleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection();
            $advancedroleCollection->addFieldToFilter('role_id', $roleId)->load();
            foreach ($advancedroleCollection as $advancedrole) 
            {
            	$advancedrole->delete();
            }
            
         	/**
            * Scope set to "Limit Access to Store Views/Categories"
            */
        	if ($request->getPost('access_scope') == 'store')
            {
           	    $SelectedStoreIds = $request->getPost('store_switcher');
            	$StoreCategoryIds = $request->getPost('store_category_ids');

                foreach ($SelectedStoreIds as $store_id => $storeview)
                {
                    $StoreViewIds = implode(',', $storeview);

                    $CategoryIds = '';
                    if (isset($StoreCategoryIds[$store_id]))
                    {
                        $CategoryIds = implode(',', array_diff(array_unique(explode(',', $StoreCategoryIds[$store_id])), array('')));
                    }

                    $advancedrole = Mage::getModel('aitpermissions/advancedrole');

                    $advancedrole->setData('role_id', $roleId);
                	$advancedrole->setData('store_id', $store_id);
                	$advancedrole->setData('storeview_ids', $StoreViewIds);
                	$advancedrole->setData('category_ids', $CategoryIds);
                	$advancedrole->setData('website_id', 0);
                	
                    $advancedrole->save();
                }
            }
            
            /**
            * Scope set to "Limit Access to Websites"
            */
            if ($request->getPost('access_scope') == 'website') 
            {  
                foreach ($request->getPost('website_switcher') as $website_id)
                {
                    $advancedrole = Mage::getModel('aitpermissions/advancedrole');
                    
                	$advancedrole->setData('role_id', $roleId);
                	$advancedrole->setData('website_id', $website_id);
                	$advancedrole->setData('store_id', '');
                    $advancedrole->setData('category_ids', '');
                    
                    $advancedrole->save();
                }
            }
        }
    }
    
    public function validateAdvancedRole(Varien_Event_Observer $observer)
    {
        $role    = $observer->getObject();
        $request = Mage::app()->getRequest();
        $roleId  = $role->getId();
     	/**
         * Scope set to "Limit Access to Store Views/Categories"
         */
    	if ('store' == $request->getPost('access_scope'))
        {
            $storeIds      = $request->getPost('store_switcher');
            $categoryIds   = $request->getPost('store_category_ids');
            $errorStoreIds = array();

            foreach ($storeIds as $storeId => $storeviewIds)
            {
                if (empty($categoryIds[$storeId]))
                {
                    $errorStoreIds[] = $storeId;
                }
            }

            if ($errorStoreIds)
            {
                $storesCollection = Mage::getModel('core/store_group')->getCollection()
                    ->addFieldToFilter('group_id', array('in' => $errorStoreIds));

                $storeNames = array();
                foreach ($storesCollection as $store)
                {
                    $storeNames[] = $store->getName();
                }

                Mage::throwException(Mage::helper('aitpermissions')->__('Please, select allowed categories for the following stores: %s', join(', ', $storeNames)));
            }
        }
    }

    public function onAdminRolesDeleteAfter($observer)
    {
    	$role = $observer->getObject();

        if ($role)
        {
            $advancedroleCollection = Mage::getModel('aitpermissions/advancedrole')->getCollection();
            $advancedroleCollection->addFieldToFilter('role_id', $role->getId())->load();
            foreach ($advancedroleCollection as $advancedrole) 
            {
            	$advancedrole->delete();
            }
        }
    }
    
    public function onCatalogEditAction($observer)
    {
        if ($this->_helper->isPermissionsEnabled()) 
        {
            $product = $observer->getProduct();
            /* $var $product Mage_Catalog_Model_Product */
            
      	    if (!Mage::getStoreConfig('admin/general/showallproducts')) 
            {
                $bAllow = false;

                $allowedWebsites = $this->_helper->getAllowedWebsites();
                if (array_intersect($allowedWebsites, $product->getWebsiteIds()))
                {
                    if ($this->_helper->isScopeWebsite())
                    {
                        $bAllow = true;
                    }
                    else 
                    {
                        $allowedCategories = $this->_helper->getAllowedCategories();
                        if (array_intersect($allowedCategories, $product->getCategoryIds()))
                        {
                            $bAllow = true;
                        }
                    }
                }

            	if (!$bAllow)
            	{
            	    /* @var $session Mage_Adminhtml_Model_Session */
            	    $session = Mage::getSingleton('adminhtml/session');
            	    $session->addError($this->_helper->__('Sorry, you have no permissions to edit this product. For more details please contact site administrator.'));

            	    $controller = Mage::app()->getFrontController();
            	    $controller->getResponse()
            	        ->setRedirect(Mage::getModel('adminhtml/url')->getUrl('*/*/', array('store' => $controller->getRequest()->getParam('store', 0))))
            	        ->sendResponse();
            		exit;
            	}
            }

            if (($this->_helper->isScopeStore() && !Mage::getStoreConfig('admin/general/allowdelete')) 
             || ($this->_helper->isScopeWebsite() && !Mage::getStoreConfig('admin/general/allowdelete_perwebsite')))
            {
            	$product->setIsDeleteable(false);
            }
        }
    }
    
    public function onCatalogProductPrepareSave($observer)
    {
    	$StoreIds = Mage::helper('aitpermissions')->getStoreIds();
        
        if (!$StoreIds)
        {
        	// should check if the product is a new one
            $product = $observer->getProduct();
            $request = $observer->getRequest();
            
            $aProductPostData = $request->getPost('product');
            
            if (!$product->getId())
            {
            	// new product
            	Mage::getSingleton('catalog/session')->setIsNewProduct(true);
            	Mage::getSingleton('catalog/session')->setSelectedVisibility($aProductPostData['visibility']);
            	$product->setData('visibility', Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            }
        }
    }
    
    public function onCatalogProductSaveAfter($observer)
    {
    	$StoreviewIds = Mage::helper('aitpermissions')->getStoreviewIds();
        
    	$product = $observer->getProduct();
    	
    	if (Mage::getSingleton('catalog/session')->getIsNewProduct() and $StoreviewIds)
    	{
    	    // setting selected visibility for allowed store views
    	    
    		$collection = Mage::getResourceModel('catalog/product_collection');
    		
    		foreach ($StoreviewIds as $StoreviewId)
    		{
        		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
        		
        		$sSql = ' INSERT INTO ' . $collection->getTable('catalog_product_entity_int') .
        		        ' (entity_type_id, attribute_id, store_id, entity_id, value) ' . 
        		        ' VALUES ' . 
        		        ' ( "' . $product->getEntityTypeId() . '", "' . Mage::getModel('eav/entity_attribute')->load('visibility', 'attribute_code')->getId() . '", "' . $StoreviewId . '", "' . $product->getId() . '", "' . Mage::getSingleton('catalog/session')->getSelectedVisibility() . '" ) ';
        		$write->query($sSql);
    		}
    		// setting visibility: "Nowhere" for all other store views
    		$storeCollection = Mage::getModel('core/store')->getCollection();
            foreach($storeCollection as $store) 
            {
            	if (0 != $store->getId() && (!in_array($store->getId(), $StoreviewIds)))
            	{
            		$sSql = ' INSERT INTO ' . $collection->getTable('catalog_product_entity_int') .
		                    ' (entity_type_id, attribute_id, store_id, entity_id, value) ' . 
		                    ' VALUES ' . 
		                    ' ( "' . $product->getEntityTypeId() . '", "' . Mage::getModel('eav/entity_attribute')->load('visibility', 'attribute_code')->getId() . '", "' . $store->getId() . '", "' . $product->getId() . '", "' . Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE . '" ) ';
		            $write->query($sSql);
            	}
            }

            Mage::getSingleton('catalog/session')->setIsNewProduct(false);
            Mage::getSingleton('catalog/session')->setSelectedVisibility(null);
    	}
    }
    
    public function onCatalogProductCollectionLoadBefore($observer)
    {
    	if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml')
    	 || false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'bundle'))
    	{
            if ($this->_helper->isPermissionsEnabled() && !Mage::getStoreConfig('admin/general/showallproducts')) 
            {
            	$collection = $observer->getCollection();
    	        if ($this->_helper->isScopeStore())
    	        {
    	            //$storeviewIds = $this->_helper->getStoreviewIds();
    	        	$categories = $this->_helper->getCategoryIds();
                    if (!empty($categories))
                    {
                        $where = 
                        	' e.entity_id IN ( '.
                            ' SELECT product_id '.
                            ' FROM '.$collection->getTable('catalog_category_product').' '.
                            ' WHERE category_id IN ('.join(',', $categories).') '.
                            ' ) ';
    	    	        $collection->getSelect()->where($where);
                    }
    	        }
    	        
                if ($this->_helper->isScopeWebsite())
                {
                    $websiteIds = $this->_helper->getAllowedWebsites();
                    $scopeStoreId = Mage::app()->getFrontController()->getRequest()->getParam('store');

                    if ($scopeStoreId)
                    {
                        $scopeWebsiteId = Mage::getModel('core/store')->load($scopeStoreId)->getWebsiteId();

                        if (in_array($scopeWebsiteId, $websiteIds))
                        {
                            $websiteIds = array($scopeWebsiteId);
                        }
                    }
                    $collection->addWebsiteFilter($websiteIds);
                }
            }
    	}
    }
    
    /**
    * Add limit to collection (order, invoice, shipment...)
    * 
    * @param mixed $collection
    */
    protected function _limitCollectionByStore($collection)
    {
        if (!$collection->getFlag('permissions_processed'))
        {
            if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml'))
            {
                if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
                {
                	$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
                	$collection->addAttributeToFilter('store_id', array('in' => $AllowedStoreviews));
                }
            }
            $collection->setFlag('permissions_processed', true);
        }
    }
    
    public function onEavCollectionAbstractLoadBefore($observer)
    {
    	$collection = $observer->getCollection();

	    if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Collection)
	    {
	        $this->_limitCollectionByStore($collection);
	    }
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Invoice_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Shipment_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Creditmemo_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
	    
	    if ($collection instanceof Mage_Customer_Model_Entity_Customer_Collection)
	    {
            if (!Mage::getStoreConfig('admin/general/showallcustomers') && Mage::helper('aitpermissions')->isPermissionsEnabled())
            {
                $AllowedWebsites = $this->_helper->getAllowedWebsites();
                $collection->addAttributeToFilter('website_id', array('in' => $AllowedWebsites));
            }
	    }
    }
    
    public function onCoreCollectionAbstractLoadBefore($observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Invoice_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }        
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Shipment_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Creditmemo_Grid_Collection)
        {
            $this->_limitCollectionByStore($collection);
        }
    }
    
    public function onSalesOrderLoadAfter($observer)
    {
    	$order = $observer->getOrder();
    	
        if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml'))
        {
            if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
            {
            	$AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
    	        if (!in_array($order->getStoreId(), $AllowedStoreviews))
    	        {
	        		Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/sales_order'));
    	        }
            }
        }
    }
    
    public function onCustomerLoadAfter($observer)
    {
    	$customer = $observer->getCustomer();
    	
    	if (false !== strpos(Mage::app()->getFrontController()->getRequest()->getRouteName(), 'adminhtml') && $customer->getData())
        {
            if (Mage::helper('aitpermissions')->isPermissionsEnabled() && !Mage::getStoreConfig('admin/general/showallcustomers'))
            {
                $AllowedWebsites = Mage::helper('aitpermissions')->getAllowedWebsites();

                if (!in_array($customer->getWebsiteId(), $AllowedWebsites))
                {
                    Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
                }
            }
        }
    }
    
    public function onCmsPageLoadAfter($observer)
    {
        $model = $observer->getObject();
        if ($model instanceof Mage_Cms_Model_Page)
        {
            if (!Mage::helper('aitpermissions')->isPermissionsEnabled())
            {
                return true;
            }

            if (!$model->getData('store_id'))
            {
                return true;
            }

            if (is_array($model->getData('store_id')) && in_array(0, $model->getData('store_id')))
            {
                // allow, if admin store (all store views) selected
                return true;
            }

            if (is_array($model->getData('store_id')) && array_intersect($model->getData('store_id'), Mage::helper('aitpermissions')->getAllowedStoreviews()))
            {
                return true;
            }

            // if no permissions - redirect
            Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
        }
    }
    
    public function onAdminhtmlCmsPageEditTabMainPrepareForm($observer)
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $page = Mage::registry('cms_page');
            $pageStoreviews = (array)$page->getStoreId();

            $allowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();

            /* if page assigned to some storeview admin don't have access to - forbid enabled/disabled setting changes */
            if (array_diff($pageStoreviews, $allowedStoreviews))
            {
                $fieldset = $observer->getForm()->getElement('base_fieldset');
                $fieldset->removeField('is_active');
            }
        }
    }
    
    public function onCmsPagePrepareSave($observer)
    {
        $page = $observer->getPage();
        if ($page->getId() && Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            // should keep in mind we may have store views from another websites (not visible on edit form) assigned
            Mage::helper('aitpermissions/access')->setCmsObjectStores($page);
        }
    }
    
    public function onModelSaveBefore($observer)
    { 
        $model = $observer->getObject();
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            if ($model instanceof Mage_Cms_Model_Block)
            {
                Mage::helper('aitpermissions/access')->setCmsObjectStores($model);
            }
            if ($model instanceof Mage_Widget_Model_Widget_Instance)
            {
                Mage::helper('aitpermissions/access')->setCmsObjectStores($model);
            }
            if ($model instanceof Mage_Poll_Model_Poll)
            {
                Mage::helper('aitpermissions/access')->setCmsObjectStores($model);
            }
        }
    }
    
    public function onReviewDeleteBefore($observer)
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $ReviewId = $observer->getObject()->getId();
            $ReviewStoreId = Mage::getModel('review/review')->load($ReviewId)->getData('store_id');
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            
            if (!in_array($ReviewStoreId, $AllowedStoreviews)) 
            {
                Mage::throwException(Mage::helper('aitpermissions')->__('Review could not be deleted due to insufficent permissions.'));
            }
        }
    }
    
    public function onAdminhtmlCatalogProductReviewMassDeletePredispatch($observer)
    {
        if (Mage::helper('aitpermissions')->isPermissionsEnabled()) 
        {
            $ReviewIds = $observer->getData('controller_action')->getRequest()->getParam('reviews');
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            
            $NotAllowedReviewIds = array();
            
            foreach ($ReviewIds as $id => $ReviewId) 
            {
                $ReviewStoreId = Mage::getModel('review/review')->load($ReviewId)->getData('store_id');
                if (!in_array($ReviewStoreId, $AllowedStoreviews)) 
                {
                	unset($ReviewIds[$id]);
                	$NotAllowedReviewIds[] = $ReviewId;
                }
            }
            if (!empty($NotAllowedReviewIds)) 
            {
               	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('aitpermissions')->__('Some review(s) could not be deleted due to insufficent permissions.'));
               	$observer->getData('controller_action')->getRequest()->setParam('reviews', $ReviewIds);
            }
        }
    }
} } 