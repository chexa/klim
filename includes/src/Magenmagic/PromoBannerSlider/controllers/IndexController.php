<?php
class Magenmagic_PromoBannerSlider_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/promobannerslider?id=15
    	 *  or
    	 * http://site.com/promobannerslider/id/15
    	 */
    	/* 
		$promobannerslider_id = $this->getRequest()->getParam('id');

  		if($promobannerslider_id != null && $promobannerslider_id != '')	{
			$promobannerslider = Mage::getModel('promobannerslider/promobannerslider')->load($promobannerslider_id)->getData();
		} else {
			$promobannerslider = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($promobannerslider == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$promobannersliderTable = $resource->getTableName('promobannerslider');
			
			$select = $read->select()
			   ->from($promobannersliderTable,array('promobannerslider_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$promobannerslider = $read->fetchRow($select);
		}
		Mage::register('promobannerslider', $promobannerslider);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}