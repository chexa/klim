<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @package    AuIt_Ppager
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_Ppager_Block_Article_Pager extends AuIt_Ppager_Block_Catalog_Product_View_Pager
{
	public function getPager($article=null)
	{
		$articleId=0;
		if ( !$article )
		{
			$article = Mage::registry('auit_article_detail');
		}
		if ( $article )
			$articleId=$article->getId();
		$pages = $this->_pages = $this->buildPager();
		if ( !is_array($pages))
			$pages=array();

		$this->_currentNum=-1;
	 	$count = count($pages);
	 	foreach ( $pages as $idx => $page)
	 	{
	 		if ( $page['id'] == $articleId )
	 		{
	 			$this->_currentNum=$idx;
	 			break;	
	 		}
	 	}
	 	if ( $this->_currentNum < 0 )
	 	{
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
			$article = Mage::getModel('auit_editor/article');
			if ( isset($pages[$idx]['id']) )
			{
				$article->load($pages[$idx]['id']);
				$pages[$idx]['name']=$article->getHeadline1();
				Mage::helper('auit_ppager')->setPageResult($pages,$this->getPageId());
			}
		}
		return $pages[$idx];
	}
	protected  function buildPager() 
	{
		$pagerId=$this->getPageId();
		$lastResult = Mage::helper('auit_ppager')->getPageInfoReq('result',array(),$pagerId );
		if ( !$lastResult || !is_array($lastResult) )
		{
   			$lastSql = Mage::helper('auit_ppager')->getPageInfoReq('sql','',$pagerId );
   			if ( !$lastSql )
   				return false;
	   		$binds = Mage::helper('auit_ppager')->getPageInfoReq('binds','',$pagerId );
   			if ( !$binds || !is_array($binds))
   				$binds=array();
	   		$db = Mage::getSingleton('core/resource')->getConnection('auit_editor/article');
    		$result = $db->fetchAll($lastSql,$binds);
    		$article = Mage::getModel('auit_editor/article');
			$category  = Mage::registry('current_category');
    		$pages=array();
			foreach ( $result as $item )
			{
				$article->setData($item);
				$pages[] = array(	'id'=>$article->getId(),
									'url'=>$article->getDetailUrl($category,'&pagerId='.$pagerId),
									'name'=>$article->getHeadline1());
			}
			
			Mage::helper('auit_ppager')->setResultFrom($pages,$pagerId);
			$infoData = Mage::helper('auit_ppager')->getPageInfo($pagerId);
			if ( 1|| empty($infoData) || !isset($infoData['back']) || !isset($infoData['back']['url']) || empty($infoData['back']['url']))
			{
    			$path  = Mage::helper('catalog')->getBreadcrumbPath();
    			$item = @array_shift ( $path );
				$infoData['back']= array('url'=> $item['link'],'name'=>$item['label']);
				$category  = Mage::registry('current_category');
		    	if ( $category )
		    	{
		    		$infoData['back']= array('url'=> $category->getUrl(),'name'=>$category->getName());
		    	}
				Mage::helper('auit_ppager')->setPageInfo($pagerId,$infoData);
			}	
		}else { 
			$pages = $lastResult;
		}
		return $pages;
	}
}