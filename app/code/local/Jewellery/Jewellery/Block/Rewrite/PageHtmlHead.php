<?php
class Jewellery_Jewellery_Block_Rewrite_PageHtmlHead extends Mage_Page_Block_Html_Head
{
    public function getTitle()
    {
        if (empty($this->_data['title'])) {
            $this->_data['title'] = $this->getDefaultTitle();
        }

        // show additional prefix and suffix on product page
        if (Mage::registry('current_product')) {
            $this->_data['title'] = Mage::getStoreConfig('design/head/product_title_prefix') . ' ' . $this->_data['title']
            . ' ' . Mage::getStoreConfig('design/head/product_title_suffix');
        }
        
        return htmlspecialchars(html_entity_decode(trim($this->_data['title']), ENT_QUOTES, 'UTF-8'));
    }
}
 
