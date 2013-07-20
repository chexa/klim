<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminCatalogProductGrid.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ BwrahUiZqRTZRsae('8fc6dab3fb73eb6e430e31cd53002f9a'); ?><?php
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/
class Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductGrid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('product');

        if ((Mage::helper('aitpermissions')->isScopeStore()   && Mage::getStoreConfig('admin/general/allowdelete')) 
         || (Mage::helper('aitpermissions')->isScopeWebsite() && Mage::getStoreConfig('admin/general/allowdelete_perwebsite')))
        {
        	$this->getMassactionBlock()->addItem('delete', array(
                'label'     => Mage::helper('catalog')->__('Delete'),
                'url'       => $this->getUrl('*/*/massDelete'),
                'confirm'   => Mage::helper('catalog')->__('Are you sure?')
                ));
        } 

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('catalog')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
            ));

        $this->getMassactionBlock()->addItem('attributes', array(
            'label' => Mage::helper('catalog')->__('Update attributes'),
            'url'   => $this->getUrl('*/catalog_product_action_attribute/edit', array('_current'=>true))
            ));
        
        return $this;
    }
    
    protected function _toHtml()
    {
        $AllowedWebistes = Mage::helper('aitpermissions')->getAllowedWebsites();
        if (count($AllowedWebistes) <= 1)
        {
            unset($this->_columns['websites']);
        }
        return parent::_toHtml();
    }
} } 