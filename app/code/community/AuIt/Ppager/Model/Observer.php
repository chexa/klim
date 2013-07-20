<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @package    AuIt_Ppager
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_Ppager_RAS extends Mage_CatalogSearch_Model_Mysql4_Advanced_Collection 
{
	public function getBinds()
	{
		return $this->_bindParams;
	}	
    public function addBindParam($name, $value)
    {
        $this->_bindParams[$name] = $value;
        return $this;
    }
    public function addFieldsToFilter($fields)
    {
    	return parent::addFieldsToFilter($fields);
    }
}
class AuIt_Ppager_AS extends Mage_CatalogSearch_Model_Advanced
{
    public function getProductCollection(){
        if (is_null($this->_productCollection)) {
        	/*Mage::getResourceModel('catalogsearch/advanced_collection')*/
            $this->_productCollection = new AuIt_Ppager_RAS();
        }
        return $this->_productCollection;
    }
	public function getBinds()
	{
		return $this->_productCollection->getBinds();
	}	
}
class AuIt_Ppager_Model_Observer
{
    protected function getBackUrl($pagerId,$action)
    {
    	if ( !$action )
    	{
			return array('url'=> '','name'=>'');
    	}
   		$req = $action->getRequest();
        $titleBlock = '';
        if ( $action->getLayout() )
        {	$titleBlock =$action->getLayout()->getBlock('head');
	        if ($titleBlock) 
	        	$titleBlock=$titleBlock->getTitle();
        }
        /**
         * Updated by Nikita Chirkov, Anakreon
         */

        $urlPostfix = $req->getRequestUri();
        if ($requestAlias = $req->getAlias('rewrite_request_path')) {
            $urlPostfix = '/' . $requestAlias;
        }
        return array(
   			'url' => $req->getScheme() . '://' . $req->getHttpHost() . $urlPostfix,
   			'name'=>$titleBlock?$titleBlock:''
   		); 
    }
    protected function saveCollection($pagerId,$action,$collection,$binds=array(),$option=array())
    {
        $collection->addUrlRewrite();
		$sql = (string)$collection->getSelect();
	    if ( ($pos=strpos($sql,'LIMIT')) > 0)
	   		$sql =substr($sql, 0,$pos);
		Mage::helper('auit_ppager')->setPageInfo($pagerId,
			array(	
					'sql'=>$sql,
					'binds'=>$binds,
					'back'=>isset($option['backurl'])?array('url'=>$option['backurl'],'name'=>''):$this->getBackUrl($pagerId,$action),
					'result'=>''
			)
		);	   		
    }
    protected function resetFilter($pagerId='')
    {
    	Mage::helper('auit_ppager')->resetPageInfo($pagerId);
    }
    public function controllerActionPostdispatch($observer)
    {
    	if ( !Mage::getStoreConfigFlag('auit_ppager/general/enabled'))
    		return $this;
    	$this->resetFilter();
    	$action = $observer->getEvent()->getControllerAction();
    	$layer = Mage::getSingleton('catalog/layer');
    	if ( $layer && ($collection=$layer->getProductCollection()) )
    	{
    		$category = $layer->getCurrentCategory();
    		if ( $category && ($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PRODUCT || $category->getDisplayMode()==Mage_Catalog_Model_Category::DM_MIXED))
	    		$this->saveCollection('',$action,$collection);
    	} 
        return $this;
    }	
    public function controllerActionAjaxPostdispatch($observer)
    {
    	if ( !Mage::getStoreConfigFlag('auit_ppager/general/enabled'))
    		return $this;
    	$this->resetFilter();
    	$action = $observer->getEvent()->getControllerAction();
    	$layer = Mage::getSingleton('catalog/layer');
    	if ( $layer && ($collection=$layer->getProductCollection()) )
    	{
    		$this->saveCollection('',null,$collection);
   			
    	} 
        return $this;
    }	
    
    public function controllerActionPostdispatchCatalogsearch($observer)
    {
    	if ( !Mage::getStoreConfigFlag('auit_ppager/general/enabled'))
    		return $this;
    	$this->resetFilter();
    	$action = $observer->getEvent()->getControllerAction();
    	if ( ($collection= Mage::getSingleton('catalogsearch/layer')->getProductCollection()) )
    	{
    		$this->saveCollection('',$action,$collection);
    	}
        return $this;
    }	
    public function controllerActionPostdispatchCatalogSeoSitemap($observer)
    {
    	if ( !Mage::getStoreConfigFlag('auit_ppager/general/enabled'))
    		return $this;
    	$this->resetFilter();
    	$action = $observer->getEvent()->getControllerAction();
    	$block = $action->getLayout()->createBlock('catalog/seo_sitemap_product');
    	if ( $block && ($collection = $block->getCollection()) )
    	{
    		$this->saveCollection('',$action,$collection);
    	}
        return $this;
    }	
    public function controllerActionPostdispatchCatalogsearchAdvanced($observer)
    {
    	if ( !Mage::getStoreConfigFlag('auit_ppager/general/enabled'))
    		return $this;
    	$this->resetFilter();
    	$action = $observer->getEvent()->getControllerAction();
    	if ( ($collection= Mage::getSingleton('catalogsearch/advanced')->getProductCollection()) )
    	{
    		$d = new AuIt_Ppager_AS();
    		$d->addFilters($action->getRequest()->getQuery());
    		$this->saveCollection('',$action,$collection,$d->getBinds());
	 		
    	}
        return $this;
    }	
    public function externCollection($observer)
    {
    	$pagerId = $observer->getEvent()->getPagerId();
    	$this->resetFilter($pagerId);
    	if ( ($collection = $observer->getEvent()->getCollection()) )
    	{
    		$action = Mage::app()->getFrontController();
    		$option = $observer->getEvent()->getOption();
    		if ( !$option )
    			$option=array();
    		
    		$this->saveCollection($pagerId,$action,$collection,array(),$option);
    	}
    }
    public function controllerActionPostdispatch2($observer)
    {
    	//
    	//Mage_Catalog_Seo_SitemapController
    	//
    	//
    	$action = $observer->getEvent()->getControllerAction();
    	Mage::log(get_class($action));
    	Mage::log($action->getRequest()->getRouteName());
    	Mage::log($action->getFullActionName());
    	
        return $this;
    }	
}

