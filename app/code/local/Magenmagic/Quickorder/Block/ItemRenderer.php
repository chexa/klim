<?php
class Magenmagic_Quickorder_Block_ItemRenderer extends Mage_Checkout_Block_Cart_Item_Renderer
{

	protected $_configProduct = null;

	public function __construct()
	{
		parent::__construct();
		$this->setDefaultTemplate();
		//$this->setTemplate('checkout/cart/item/default.phtml');
	}
	
	public function setDefaultTemplate()
	{
		$this->setTemplate('magenmagic/quickorder/item.phtml');
	}
	
	public function setShortTemplate()
	{
		$this->setTemplate('magenmagic/quickorder/itemChoose.phtml');
	}
	
	 protected function getConfigurableProductParentId()
    {

        return $this->getConfigProduct()->getId();
    }
	
    public function setItem($item)
    {
        $this->_item = $item;
		$this->setConfigProduct();
        return $this;
    }
	
	public function getProduct()
	{
		return $this->_item;
	}
	
	public function getConfigProduct()
	{
		if ( $this->_configProduct !== null ) return $this->_configProduct;
		$this->setProduct();
		return $this->_configProduct;
	}
	
	public function setConfigProduct()
	{
		$this->_configProduct = null;
		$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($this->_item->getId()); 
		if ( ! count( $parentIds ) ) return false;
		$this->_configProduct =  Mage::getModel('catalog/product')->load($parentIds[0]);
	}
	
	 public function getQty()
    {
        return 1;
    }
	
	
	
	 public function getOptionList()
    {
        $options = false;
        if (Mage::getStoreConfig('SCP_options/cart/show_custom_options')) {
           // $options = parent::getOptionList();
        }
		
        if (Mage::getStoreConfig('SCP_options/cart/show_config_product_options')) {
		
            if ($this->getConfigProduct()) {
                $attributes = $this->getConfigProduct()
                    ->getTypeInstance()
                    ->getUsedProductAttributes();
					
                foreach($attributes as $attribute) {
				
                    $options[] = array(
                        'label' => $attribute->getFrontendLabel(),
                        'value' => $this->getProduct()->getAttributeText($attribute->getAttributeCode()),
                        'option_id' => $attribute->getId(),
                    );
                }
            }
        }
        return $options;
    }
	
	public function getProductThumbnail ()
	{
		$product = $this->getConfigProduct();
        return $this->helper('catalog/image')->init($product, 'thumbnail');
	}
	
}