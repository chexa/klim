<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Search Controller
 */

require_once 'Mage/CatalogSearch/controllers/ResultController.php';
class Jewellery_CatalogSearch_ResultController extends Mage_CatalogSearch_ResultController
{
    /**
     * Retrieve catalog session
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('catalog/session');
    }
    /**
     * Display search result
     */
    public function indexAction()
    {
		$catalogSearch = clone Mage::helper('catalogsearch');
		$paramName = $catalogSearch->getQueryParamName();
		$nativeQueryText = $this->getRequest()->getParam($paramName);
		$this->loadLayout();
		$this->getRequest()->setParam($paramName, '\'"' . $nativeQueryText . '"\'');

        $query = $catalogSearch->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */
        $query->setStoreId(Mage::app()->getStore()->getId());
		
        if ($query->getQueryText()) {
          	$this->_handleQuery($query);

			$this->_initLayoutMessages('catalog/session');
			$this->_initLayoutMessages('checkout/session');

			$resultBlock = $this->getLayout()->getBlock("search_result_list");
			$resultsProduct = $resultBlock->getLoadedProductCollection();

			Mage::helper('catalogsearch')->cleanQueryText();

			$this->getRequest()->setParam($paramName, $nativeQueryText);
            $catalogSearch->setQueryText($nativeQueryText);
			$query2 = Mage::helper('catalogsearch')->getQuery();

			if (sizeof($resultsProduct->getItems()) == 0) {
				$this->_handleQuery($query2);

				$resultsProduct =  $resultBlock->getProductCollection();

				/*	$productIds = array_merge($resultsProduct->getAllIds(), $resultsProduct2->getAllIds());

					$coll = Mage::getResourceModel('catalog/product_collection')
						->addFieldToFilter('entity_id', $productIds)
						->addAttributeToSelect('*');

					if (! empty($productIds)) {
						$coll->getSelect()->order('FIELD(entity_id, ' . \implode(',', $productIds) . ')');
					}*/

				/*foreach($resultsProduct2 as $item)
				{
					if(! $resultsProduct->getItemById($item->getId()))
					{
						$resultsProduct->addItem($item);
					}
				}*/
			}

			$resultBlock->setCollection($resultsProduct);

			if ( sizeof($resultsProduct->getItems()) == 1 )
			{
				foreach($resultsProduct->getItems() as $item)
				{
					Mage::getSingleton('core/session')->setProductSearchQuery($catalogSearch->getEscapedQueryText());
					return $this->_redirectUrl($item->getProductUrl());
				}
				exit();
			}

            $this->renderLayout();

        }
        else {
            $this->_redirectReferer();
        }
    }

	protected function _handleQuery($query)
	{
		if (Mage::helper('catalogsearch')->isMinQueryLength()) {
			$query->setId(0)
				->setIsActive(1)
				->setIsProcessed(1);
		}
		else {
			if ($query->getId()) {
				$query->setPopularity($query->getPopularity()+1);
			}
			else {
				$query->setPopularity(1);
			}

			if ($query->getRedirect()){
				$query->save();
				$this->getResponse()->setRedirect($query->getRedirect());
				return;
			}
			else {
				$query->prepare();
			}
		}

		if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
			$query->save();
		}

		Mage::helper('catalogsearch')->checkNotes();
	}

}
