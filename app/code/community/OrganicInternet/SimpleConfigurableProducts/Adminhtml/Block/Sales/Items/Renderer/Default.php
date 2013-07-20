<?php

	class OrganicInternet_SimpleConfigurableProducts_Adminhtml_Block_Sales_Items_Renderer_Default
		extends Mage_Adminhtml_Block_Sales_Items_Renderer_Default
	{
		/**
		 * Over-ridden, added getItemDetails() to retrieve configurables custom options
		 * Retrieve rendered column html content
		 *
		 * @param Varien_Object $item
		 * @param string $column the column key
		 * @param string $field the custom item field
		 * @return string
		 */
		public function getColumnHtml(Varien_Object $item, $column, $field = null)
		{
			$product = $item->getOrderItem();

			if ($product)
				$block = $this->getColumnRenderer($column, $item->getOrderItem()->getProductType());
			else
				$block = $this->getColumnRenderer($column, $item->getProductType());

			if ($block) {
				$block->setItem($item);
				if (!is_null($field)) {
					$block->setField($field);
				}

				if ($product && $column == "name" && Mage::getStoreConfig('SCP_options/setup/show_custom_options_admin'))
					return $block->toHtml() . $this->getItemDetails($product);
				else
					return $block->toHtml();
			}
			return '&nbsp;';
		}

		/**
		 * Loop through the configurable parents custom options and display title and value
		 *
		 * @param Varien_Object $item
		 * @return string
		 */
		public function getItemDetails(Varien_Object $item) {
		
			$buffer = null;
			$configurable = null;
			$simple = null;
		
			$_request = $item->getProductOptionByCode('info_buyRequest');
			
			$configurable = Mage::getModel('catalog/product')->load( $_request['cpid'] );
			$simple = Mage::getModel('catalog/product')->load( $_request['product'] );
			
			$options = array();
			
			foreach ($_request['super_attribute'] as $attribute_id => $attribute_value)
			{
				$_attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attribute_id);
				
				$buffer .= "<dt>".$_attribute->getFrontendLabel()."</dt><dd>".$simple->getAttributeText( $_attribute->getName() )."</dd>";
			}
			
			foreach ($configurable->getOptions() as $o)
			{
				$_option_id = $o->getId();
			
				$values = $o->getValues();
				
				foreach ($values as $v)
				{
					$option = $v->getData();
					
					if ( $_request['options'][$_option_id] == $option['option_type_id'] )
					{
						$buffer .= "<dt>".$o->getTitle()."</dt><dd>".$option['title']."</dd>";
					}
				}
			}

			return '<dl  class="item-options">' . $buffer . '</dl>';
		}
	}
