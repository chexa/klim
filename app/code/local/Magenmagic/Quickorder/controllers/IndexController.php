<?php
ini_set('display_errors', 1);

class Magenmagic_Quickorder_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
			
		$this->loadLayout();     
		$this->renderLayout();
    }

    public function showFormAction ()
    {

        //if ( ! $this->getRequest()->isXmlHttpRequest() ) return false;

        $formBlock = $this->getLayout()->createBlock("quickorder/quickorderForm");

        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setBody($formBlock->toHtml());
        $response->sendResponse();
        die();
    }

    protected function _getSession()
        {
            return Mage::getSingleton('catalog/session');
        }

    /*Full search*/
    public function searchFullAction ()
    {

        $query = Mage::helper('catalogsearch')->getQuery();

        /* @var $query Mage_CatalogSearch_Model_Query */
        $query->setStoreId(Mage::app()->getStore()->getId());

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

            Mage::helper('catalogsearch')->checkNotes();

        $this->loadLayout();
        $resBlock = $this->getLayout()->getBlock("search.result");
        $collection = $resBlock->getListBlock()->getLoadedProductCollection();

        echo $collection->count();

        die();
    }

   public function searchBySkuAction ()
   {
       $q = $this->getRequest()->getParam("q");
       if (empty($q)) return false;
       $q = str_replace("/", "", $q);
	   $q = preg_replace("/\s+/", "", $q);
	/*   $l = 2386;
	   $pModel = Mage::getModel("catalog/product");
	   $tmpProduct = $pModel->load( $l );
	   $bl = $this->getLayout()->createBlock("quickorder/itemRenderer");
	   $bl->setItem($tmpProduct);
		   var_dump ( $bl->toHtml() ); die('1'); */
	   
       $resource = Mage::getSingleton('core/resource');
       $conn = $resource->getConnection('core_read');
       $passParam = $conn->quote("%$q%");

       $sql = "SELECT `e`.* FROM `catalog_product_entity` AS `e` WHERE (REPLACE(REPLACE(e.sku, '/', ''), ' ', '') like $passParam ) AND `type_id` = 'simple' LIMIT 10";
      //  echo $sql; die;
       $collection = $conn->query($sql)->fetchAll();

       $productArray = array();
       $iterator = 0;
       $pModel = Mage::getModel("catalog/product");
       $catalogHelper = Mage::helper('catalog/output');

       $view = new Mage_Core_Block_Template();
	   $itemBlock = $this->getLayout()->createBlock("quickorder/itemRenderer");
	   
       foreach ( $collection as $item )
       {
           $tmpProduct = $pModel->load( $item['entity_id'] );
		   if (! $tmpProduct->isSaleable() || ! $tmpProduct->isAvailable() || ! $tmpProduct->getIsInStock()) continue;

           $invData = Mage::helper('quickorder')->getInventoryData($tmpProduct);
		   $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($tmpProduct);

		   if ($stockItem->getQty() < 7) continue;

		   $itemBlock->setItem($tmpProduct);
           //$productArray[$iterator]["name"]            = $tmpProduct->getName();
           //$productArray[$iterator]["image"]           = (string) $itemBlock->getProductThumbnail()->resize(57,57);
             $productArray[$iterator]["id"]              = $tmpProduct->getId();
             $productArray[$iterator]["sku"]             = $tmpProduct->getSku();
           //$productArray[$iterator]["in_stock"]        = $tmpProduct->getIsInStock();
           //$productArray[$iterator]["price"]           = number_format($catalogHelper->productAttribute($tmpProduct, $tmpProduct->getPrice(), 'price'), 2, ',', '');
           //$productArray[$iterator]["status"]          = '<span class="lager '.$invData['status'].'" title="'.$view->__($invData['status']).'"></span>';
           //$productArray[$iterator]["status"]          = $invData['status'];
           //$productArray[$iterator]["htmlQuantity"]    = Mage::helper('quickorder')->getHtmlQuantity($tmpProduct);
		   $itemBlock->setDefaultTemplate();
		   $productArray[$iterator]["html"]            = $itemBlock->toHtml();
		   $itemBlock->setShortTemplate();
		   $productArray[$iterator]["chooseHtml"]      = $itemBlock->toHtml();
		   
		//   $('quickOrderLink').click();
           $iterator++;
       }
       
       $response = $this->getResponse();
       $response->setHeader('HTTP/1.1 200 OK','');
       $response->setHeader('Content-type', 'application/json');
       $response->setBody(Mage::helper('core')->jsonEncode($productArray));
       $response->sendResponse();
       die;
   }

   public function addToCartAction ()
   {
       $products = $this->getRequest()->getParam("product");
       $qty      = $this->getRequest()->getParam("qty");
       $cart = Mage::getModel('checkout/cart');

       foreach ( $products as $item )
       {
           $cart->addProduct((int) $item, $qty[$item]);
       }
       //$cart->addProductsByIDs($products);
       $cart->save();
      $this->_redirect('checkout/cart');
   }

}