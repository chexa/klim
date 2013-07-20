<?php

require_once 'Mage/Cms/controllers/IndexController.php';

class Jewellery_Cms_IndexController extends Mage_Cms_IndexController
{
    public function indexAction($coreRoute = null)
    {
        if (Mage::helper('customer')->isLoggedIn()) {
            $pageId = Mage::getStoreConfig(Jewellery_Cms_Helper_Page::XML_PATH_HOME_PAGE_AUTH);
        } else {
            $pageId = Mage::getStoreConfig(Jewellery_Cms_Helper_Page::XML_PATH_HOME_PAGE);
        }

        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultIndex');
        }
    }
}
