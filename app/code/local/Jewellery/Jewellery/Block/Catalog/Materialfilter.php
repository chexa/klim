<?php

class Jewellery_Jewellery_Block_Catalog_Materialfilter extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getMaterialFilter()
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', Mage::helper('jewellery')->getMaterialAttributeCode());
        $options = $attribute->getSource()->getAllOptions(false);

        return $options;
    }

    public function getSelectedFilterValue()
    {
        return Mage::helper('jewellery')->getSelectedFilterValue();
    }

    public function getFilterUrl($materialId = null)
    {
        $params = Mage::app()->getRequest()->getParams();
        if ($materialId) {
            $params['_query'][Mage::helper('jewellery')->getMaterialAttributeCode()] = $materialId;
        } else {
            $params['_query'][Mage::helper('jewellery')->getMaterialAttributeCode()] = null;
        }
        $params['_use_rewrite'] = true;
        $params['_current'] = true;

        return Mage::getUrl('*/*', $params);
    }

    public function getRemoveUrl()
    {
        $query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue());
        $params['_current']     = true;
        $params['_use_rewrite'] = true;
        $params['_query']       = $query;
        $params['_escape']      = true;
        return Mage::getUrl('*/*/*', $params);
    }





}