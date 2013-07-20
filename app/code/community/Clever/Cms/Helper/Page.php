<?php

class Clever_Cms_Helper_Page extends Mage_Cms_Helper_Page
{
    public function renderPage(Mage_Core_Controller_Front_Action $action, $pageId = null)
    {
        $storeId = Mage::app()->getStore()->getId();
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        if (! $this->isAllowed($storeId, $customerGroupId, $pageId)) {
            return false;
        }

        return parent::renderPage($action, $pageId);
    }

    public function isAllowed($storeId, $customerGroupId, $pageId)
    {
        if (! $this->isPermissionsEnabled($storeId)) {
            return true;
        }

        return Mage::getResourceModel('cms/page_permission')->exists($storeId, $customerGroupId, $pageId);
    }

    public function isPermissionsEnabled($store = null)
    {
        return Mage::getStoreConfigFlag('cms/clever/permissions_enabled', Mage::app()->getStore($store));
    }

    public function isCreatePermanentRedirects($store = null)
    {
        return Mage::getStoreConfigFlag('cms/clever/save_rewrites_history', Mage::app()->getStore($store));
    }

    public function hasParent()
    {
        $page = Mage::getSingleton('cms/page');

        if ($page->getParentPage()->getIdentifier() == Mage::getStoreConfig('cms/clever/root_cms_page', Mage::app()->getStore()->getId())) {
            return false;
        }

        return true;
    }

}

