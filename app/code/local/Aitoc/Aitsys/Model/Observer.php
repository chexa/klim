<?php

class Aitoc_Aitsys_Model_Observer extends Aitoc_Aitsys_Abstract_Model
{
    /**
     * catalog_product_save_after
     * catalog_product_delete_after_done
     */
    public function clearCountCache(Varien_Event_Observer $observer)
    {
        foreach(array('product', 'store', 'admin') as $key)
        {
            $id = $this->tool()->getCountCacheId($key);
            Mage::app()->removeCache($id);
        }
    }
    
}