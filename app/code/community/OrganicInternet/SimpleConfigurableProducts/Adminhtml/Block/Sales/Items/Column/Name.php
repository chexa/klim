<?php

	class OrganicInternet_SimpleConfigurableProducts_Adminhtml_Block_Sales_Items_Column_Name
		extends Mage_Adminhtml_Block_Sales_Items_Column_Name
	{
		/**
		 * Loop through the configurable parents custom options and append them to the Sku
		 
		 * @return string
		 */
		public function getSku() {

			$sku = parent::getSku();

			if ( Mage::getStoreConfig('SCP_options/setup/show_custom_options_sku') ) {
			
				$item = $this->getItem();
				
				$_request = $item->getProductOptionByCode('info_buyRequest');
				
				$configurable = Mage::getModel('catalog/product')->load( $_request['cpid'] );
				
				foreach ($configurable->getOptions() as $o) {

					$_option_id = $o->getId();
					$values = $o->getValues();

					foreach ($values as $v) {
						$option = $v->getData();

						if ( $_request['options'][$_option_id] == $option['option_type_id'] && $option['sku'] )
							$sku .= "-".$option['sku'];
					}
				}
			}

			return $sku;
		}
	}