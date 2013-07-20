<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @package    AuIt_Ppager
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_Ppager_Helper_Data extends Mage_Core_Helper_Url
{
	function getRequestPageId()
	{
		return Mage::app()->getFrontController()->getRequest()->getParam('pagerId','');
	}
	function setPageResult($pages,$pagerId='')
	{
		$info = $this->getPageInfo($pagerId);
		$info['result']=$pages;
		$this->setPageInfo($pagerId,$info);
	}
	function setResultFrom($pages,$pagerId='')
	{
		$info = $this->getPageInfo($pagerId);
		$this->resetAll();
		$info['result']=$pages;
		$this->setPageInfo($pagerId,$info);
	}
	function resetAll()
	{
		$data = Mage::getSingleton('auit_ppager/session')->getData();
		foreach ($data as $key => $v )
		{
			if ( strpos($key,'auit_pagerinfo_') === 0 )
			{
				Mage::getSingleton('auit_ppager/session')->unsetData($key);
			}
		} 
	}
	function resetPageInfo($pagerId)
	{
    	Mage::helper('auit_ppager')->setPageInfo($pagerId,array(
    		'sql'=>'','binds'=>'','back'=>'','result'=>''
    	));
	}
	function setPageInfo($pagerId,$info='')
	{
		Mage::getSingleton('auit_ppager/session')->setData('auit_pagerinfo_'.$pagerId,$info);
	}
	function getPageInfo($pagerId)
	{
		$info = Mage::getSingleton('auit_ppager/session')->getData('auit_pagerinfo_'.$pagerId);
		if ( !$info || !is_array($info))
			$info = array(    		'sql'=>'','binds'=>'','back'=>'','result'=>'');
		return $info;
	}
	
	function getPageInfoReq($key,$default='',$pagerId='')
	{
		$info = $this->getPageInfo($pagerId);
		if ( isset($info[$key]))
		{
			return $info[$key];
		}
		return $default;
	}
}