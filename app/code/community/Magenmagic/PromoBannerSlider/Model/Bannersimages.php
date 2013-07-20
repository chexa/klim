<?php

class Magenmagic_PromoBannerSlider_Model_BannersImages extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('promobannerslider/bannersimages');
    }

    public function addImage($path, $thumb)
    {
        $date_create = date("Y-m-d H:i:s");
        $this->setPath($path)->setThumb($thumb)->setDateCreate($date_create)->save();
        return $this;
    }

    public function getCurrentCollection($collection_id, $rand = 0)
    {
        $collection = $this->getCollection();
		
		$order = $rand == 0 ? "date_create DESC" : "RAND()";
		
        $collection->getSelect()->joinRight( array('table_alias'=>Mage::getSingleton('core/resource')->getTableName('promobannerslider/links')), 'main_table.bannersimages_id = table_alias.photo_id', array('table_alias.*'))->where("table_alias.gallery_id = ?", $collection_id)->order($order);
        
		return $collection;
    }

    public function getImagesOfMainCollection ()
    {

        $collection = $this->getCollection();
        $collection->getSelect()->joinRight( array('table_alias'=>Mage::getSingleton('core/resource')->getTableName('promobannerslider/links')), 'main_table.bannersimages_id = table_alias.photo_id', array('table_alias.*'))->where("table_alias.gallery_id = ?", $collection_id)->order("main_table.date_create DESC");
        return $collection;
    }

}