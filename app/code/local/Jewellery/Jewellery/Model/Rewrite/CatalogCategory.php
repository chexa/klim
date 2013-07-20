<?php
class Jewellery_Jewellery_Model_Rewrite_CatalogCategory extends Mage_Catalog_Model_Category
{
    public function getThumbnailUrl()
    {
        $url = false;
        if ($image = $this->getThumbnail()) {
            $url = Mage::getBaseUrl('media').'catalog/category/'.$image;
        }
        return $url;
    }
}