<?php
class Jewellery_Jewellery_Block_Rewrite_CatalogProductListToolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChild('product_list_toolbar_pager');

        if ($pagerBlock instanceof Varien_Object) {

            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $customShowAmounts = $this->getCustomShowAmounts();
            if (!$customShowAmounts) {
                $showAmounts = false;
            } else {
                $showAmounts = $customShowAmounts;
            }

            //var_dump($showAmounts);

            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts($showAmounts)
                ->setLimitVarName($this->getLimitVarName())
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getLimit())
                ->setFrameLength(Mage::getStoreConfig('design/pagination/pagination_frame'))
                ->setJump(Mage::getStoreConfig('design/pagination/pagination_frame_skip'))
                ->setCollection($this->getCollection());

            return $pagerBlock->toHtml();
        }

        return '';
    }
}