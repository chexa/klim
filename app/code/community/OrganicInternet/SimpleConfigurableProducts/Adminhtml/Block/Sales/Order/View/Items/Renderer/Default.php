<?php
    class OrganicInternet_SimpleConfigurableProducts_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default
        extends Mage_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default
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
            if ($item->getOrderItem()) {
                $block = $this->getColumnRenderer($column, $item->getOrderItem()->getProductType());
            } else {
                $block = $this->getColumnRenderer($column, $item->getProductType());
            }

            if ($block) {
                $block->setItem($item);
                if (!is_null($field)) {
                    $block->setField($field);
                }

                if ($column == "name" && Mage::getStoreConfig('SCP_options/setup/show_custom_options_admin'))
                    return $block->toHtml() . $this->getItemDetails($item);
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
        public function getItemDetails(Varien_Object $item) 
        {
            $buffer = null;
            $configurable = null;
            $simple = null;

            $_request = $item->getProductOptionByCode('info_buyRequest');

            $configurable = Mage::getModel('catalog/product')->load( $_request['cpid'] );
            $simple = Mage::getModel('catalog/product')->load( $_request['product'] );

            $attributes = $configurable->getTypeInstance()->getUsedProductAttributes();
            foreach($attributes as $attribute) {
                $buffer .= "<dt>" . $attribute->getFrontendLabel() . "</dt>";
                $buffer .= "<dd>" . $simple->getAttributeText( $attribute->getName() ) . "</dd>";
            }

            return '<dl  class="item-options">' . $buffer . '</dl>';
        }
    }
