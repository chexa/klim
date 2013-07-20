<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @package    AuIt_Ppager
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_Ppager_Block_Catalog_Product_View_Pager extends Mage_Core_Block_Template
{
	protected $_pages;
	protected $_showFrameItems=true;
	protected $_currentNum;
	protected $_frameStart;
	protected $_frameEnd;
	protected $_backData='';
	protected $_frameLength = 10;		
	protected $_pageId;
	public function getPageId()
	{
		if ( is_null($this->_pageId) )
			$this->_pageId = (string)Mage::helper('auit_ppager')->getRequestPageId();
		return $this->_pageId;
	}
	
    protected function _prepareLayout()
    {
    	return parent::_prepareLayout();
    }
    protected function _toHtml()
    {
        if (  Mage::getStoreConfigFlag('auit_ppager/general/enabled')  ) {
    		$this->getPager();
        	return parent::_toHtml();
        }
        return '';
    }
    public function getTotalNum()
    {
        return count($this->_pages);
    }
	public function getCurrentNum()
	{
		return $this->_currentNum+1;	
	}	

	public function isFirstItem()
	{
		return $this->_currentNum == 0;
	}
	public function isLastItem()
	{
		return ($this->_currentNum+1) == count($this->_pages);
	}
	
	public function isItemCurrent($_item)
	{
		return $_item == $this->getCurrentNum();
	}
	public function showFrameItems($bshow)
	{
		$this->_showFrameItems=$bshow;
	}
	public function getFrameItems()
	{
		if ( $this->_showFrameItems )
			return range($this->_frameStart, $this->_frameEnd);
		return array();
	}
	
	public function getItemTitle($_item)
	{
		$this->checkInfo($this->_pages,$_item-1);
		return $this->_pages[$_item-1]['name'];
	}
	public function getItemUrl($_item)
	{
		if ( isset($this->_pages[$_item-1]) && isset($this->_pages[$_item-1]['url']))
			return $this->_pages[$_item-1]['url'];
		return '';
	}
	public function getFirstItemTitle()
	{
		return $this->getItemTitle(1);
	}
	public function getFirstItemUrl()
	{
		return $this->getItemUrl(1);
	}
	public function getLastItemTitle()
	{
		return $this->getItemTitle($this->getTotalNum());
	}
	public function getLastItemUrl()
	{
		return $this->getItemUrl($this->getTotalNum());
	}
	public function getBackItemTitle()
	{
		return $this->_backData['name'];
	}
	public function getBackItemUrl()
	{
		return $this->_backData['url'];
	}
	public function getPreviousItemTitle()
	{
		return $this->getItemTitle($this->_currentNum);
	}
	public function getPreviousItemUrl()
	{
		return $this->_pages[$this->_currentNum-1]['url'];
	}
	public function getNextItemTitle()
	{
		return $this->getItemTitle($this->_currentNum+2);
	}
	public function getNextItemUrl()
	{
		return $this->_pages[$this->_currentNum+1]['url'];
	}
    public function getFrameLength()
    {
    	return Mage::getStoreConfig('auit_ppager/general/frame_length');
    }
    public function getAnchorTextForPrevious()
    {
        return Mage::getStoreConfig('design/pagination/anchor_text_for_previous');
    }
    public function getAnchorTextForNext()
    {
        return Mage::getStoreConfig('design/pagination/anchor_text_for_next');
    }
	public function getPager($product=null)
	{
		if ( !$product )
		{
			$product = Mage::registry('current_product');
		}
		$productId=0;
		if ( $product )
			$productId=$product->getId();
		
			
		$pages = $this->_pages = $this->buildPager();
		if ( !is_array($pages))
			$pages=array();
		$this->_currentNum=-1;
	 	$count = count($pages);
	 	foreach ( $pages as $idx => $page)
	 	{
	 		if ( $page['id'] == $productId )
	 		{
	 			$this->_currentNum=$idx;
	 			break;	
	 		}
	 	}
	 	if ( $this->_currentNum < 0 )
	 	{
	 		if ( $this->buildDirectPager($product) )
	 		{
				$pages = $this->_pages = $this->buildPager();
				if ( !is_array($pages))
					$pages=array();
				$this->_currentNum=-1;
			 	$count = count($pages);
			 	foreach ( $pages as $idx => $page)
			 	{
			 		if ( $page['id'] == $productId )
			 		{
			 			$this->_currentNum=$idx;
			 			break;	
			 		}
			 	}
	 		}
	 		if ( $this->_currentNum < 0 ) {	 			
	 			$this->_frameStart = null;
	        	$this->_frameEnd = null;
		 		$this->_backData=null;
		 		$this->_pages=array();
	 			return;
	 		}
	 	}
	 	$this->_backData = Mage::helper('auit_ppager')->getPageInfoReq('back',false,$this->getPageId());
	 	if ( !$this->_backData ||  !is_array($this->_backData) )
	 		$this->_backData=array('url'=>'','name'=>'');
			
		$start = 0;
		$end = 0;
		if ($count <= $this->getFrameLength()) {
			$start = 1;
			$end = $count;
		}
		else {
			$half = ceil($this->getFrameLength() / 2);
			if ($this->_currentNum >= $half && $this->_currentNum <= $count - $half) {
				$start  = ($this->_currentNum - $half) + 1;
				$end = ($start + $this->getFrameLength()) - 1;
			}
			elseif ($this->_currentNum < $half) {
				$start  = 1;
				$end = $this->getFrameLength();
			}
			elseif ($this->_currentNum > ($count - $half)) {
				$end = $count;
				$start  = $end - $this->getFrameLength() + 1;
			}
		}
        
	 	$this->_frameStart = $start;
        $this->_frameEnd = $end;
	}
	protected  function checkInfo(&$pages,$idx)
	{
		if ( !$pages[$idx]['name'] )
		{
			$product = Mage::getModel('catalog/product');
			if ( isset($pages[$idx]['id']) )
			{
				$product->load($pages[$idx]['id']);
				$pages[$idx]['name']=$product->getNameLong();
				Mage::helper('auit_ppager')->setPageResult($pages,$this->getPageId());
			}
		}
		return $pages[$idx];
	}
	protected  function buildDirectPager($product) 
	{
		if ( !$product ) return false;
		$_ccats = $this->helper('catalog/data')->getProduct()->getCategoryIds();
		if ( !is_array($_ccats) || count($_ccats) == 0 )
			return false;
		
		$category = Mage::getModel('catalog/category')->load($_ccats[0]);
		if ( !$category->getId() )
			return false;
        $layer = Mage::registry('current_layer');
        if (!$layer) 
            $layer = Mage::getSingleton('catalog/layer');

        $origCategory = $layer->getCurrentCategory();
        $layer->setCurrentCategory($category);
        $collection = $layer->getProductCollection();
		Mage::dispatchEvent('auit_ppager_extern_collection', 
				array(	'collection'=>$collection,
						'pager_id'=>$this->_pagerID,
						'option'=>array('backurl'=>$category->getUrl())));
		return true;
	}
	protected  function buildPager() 
	{
		$pagerId=$this->getPageId();
		//Mage::helper('auit_ppager')->resetAll();
		$lastResult = Mage::helper('auit_ppager')->getPageInfoReq('result',array(),$pagerId );
		if (  !$lastResult || !is_array($lastResult) )
		{
			
   			$lastSql = Mage::helper('auit_ppager')->getPageInfoReq('sql','',$pagerId );
   			if ( !$lastSql )
   				return false;
	   		$binds = Mage::helper('auit_ppager')->getPageInfoReq('binds','',$pagerId );
   			if ( !$binds || !is_array($binds))
   				$binds=array();
	   		$db = Mage::getSingleton('core/resource')->getConnection('catalog_product_entity');
    		$result = $db->fetchAll($lastSql,$binds);
    		$product = Mage::getModel('catalog/product');
    		$pages=array();
			foreach ( $result as $item )
			{
				$product->setData($item);
				$pages[] = array(	'id'=>$product->getId(),
									//'url'=>$product->getProductUrl(),
									'url' => Mage::getModel('catalog/product_url')->getUrl($product, array('_nosid' => true, '_ignore_category' => true)),
									'name'=>'');
			}
			Mage::helper('auit_ppager')->setResultFrom($pages,$pagerId);
			$infoData = Mage::helper('auit_ppager')->getPageInfo($pagerId);
			if ( empty($infoData) || !isset($infoData['back']) || !isset($infoData['back']['url']) || empty($infoData['back']['url']))
			{
    			$path  = Mage::helper('catalog')->getBreadcrumbPath();
				$item = @array_shift ( $path );
				$item = @array_shift ( $path );
				$infoData['back']= array('url'=> $item['link'],'name'=>$item['label']);
				Mage::helper('auit_ppager')->setPageInfo($pagerId,$infoData);
			}		
		}else { 
			$pages = $lastResult;
		}
		//Mage::log($pages);
		return $pages;
	}
}