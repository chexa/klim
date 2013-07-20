<?php

class Magenmagic_PromoBannerSlider_Adminhtml_PromobannersliderController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('promobannerslider/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

    public function viewAction ()
    {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('promobannerslider/promobannerslider')->load($id);

        if ($model->getId() || $id == 0)
        {
            $this->loadLayout();
            $this->_setActiveMenu('promobannerslider/items');

        }
        else
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promobannerslider')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }


    public function getimagesAction ($return = false)
    {
        $page_id = (int) $this->getRequest()->getParam('page_id');
        $id = (int) $this->getRequest()->getParam('id');

        $page_id = $page_id  <= 0 ? 1 : $page_id;
        $pageSize = $page_id*20;

       // $this->_initAction();
        $model = Mage::getModel('promobannerslider/bannersimages');
        $collection = $model->getCollection();
        $collectionSize = $collection->count();
        $images = $model->getCollection()->setOrder("date_create", "DESC")->setCurPage(1)->setPageSize($pageSize);

        $currentCollection = $model->getCurrentCollection($id);

        if (  ! $return )
        {
            $this->loadLayout();
            $layout = $this->getLayout();
            $imagesBlock = $layout->getBlock("images_list");
            $imagesBlock->setData("images",   $images);
            $imagesBlock->setData("current_collection",   $currentCollection);
            $imagesBlock->setData("size",     $collectionSize);
            $imagesBlock->setData("currpage", $page_id);
            $this->getResponse()->setBody( $imagesBlock->toHtml() );
        }
        else
        {
           // $this->loadLayout();
            $layout = $this->getLayout();
            $imagesBlock = $layout->getBlock("images_list");
            $imagesBlock->setData("images", $images);
            $imagesBlock->setData("current_collection", $currentCollection);
            $imagesBlock->setData("currpage", $page_id);
            $imagesBlock->setData("size",   $collectionSize);
            return $imagesBlock->toHtml();
        }


    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('promobannerslider/promobannerslider')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('promobannerslider_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('promobannerslider/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_edit'))
				->_addLeft($this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_edit_tabs')->setData('images', $this->getimagesAction(true)));
            //var_dump($k);die;


          /*  $bl = $this->getLayout()->getBlock('promobannerslider/adminhtml_promobannerslider_edit_tabs')->setData('order', 123);

            var_dump($bl);*/

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promobannerslider')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

    //delete images
    public function deleteimagesAction ()
    {
        $params = $this->getRequest()->getParam("chkItem");

        $model      = Mage::getModel('promobannerslider/bannersimages');
        $modelLinks = Mage::getModel('promobannerslider/links');

        foreach ( $params as $val )
        {
            $file  = $model->load($val);
            $model = $file->delete();

            $linksCollection = $modelLinks->getCollection()->addFieldToFilter("photo_id", $val);

            foreach ( $linksCollection as $itemLink )
            {
                $itemLink->delete();
            }

            unlink( Mage::getBaseDir("media") . DS. $file->path );
        }

        Mage::getSingleton('core/session')->setBannersMessage('Current items was successfully removed');

        die();
    }

    public function removefromcollectionAction()
    {
        $itemId = $this->getRequest()->getParam("itemID");

        $modelLinks = Mage::getModel('promobannerslider/links');
        $modelLinks->load($itemId)->delete();

        Mage::getSingleton('core/session')->setBannersMessage('Current item was successfully removed from collection');

        die();
    }

    //add to collection
    public function addtocollectionAction ()
    {
        $params        = $this->getRequest()->getParam("chkItem");
        $collection_id = $this->getRequest()->getParam("collection_id");

        try
        {
            foreach ( $params as $val )
            {
                $model = Mage::getModel('promobannerslider/links');
                $exist = $model->getCollection()->addFieldToFilter("gallery_id", $collection_id)->addFieldToFilter("photo_id", $val);
                if ( $exist->count() != 0 ) continue;
                $model->setGalleryId($collection_id)->setPhotoId($val)->save();
            }
            Mage::getSingleton('core/session')->setBannersMessage('Current items was successfully added to collection');
        }
        catch ( Exception $e )
        {
            throw new Exception ( $e );
        }


        die();
    }

	public function newAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('promobannerslider/promobannerslider')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('promobannerslider_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('promobannerslider/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_edit'))
                ->_addLeft($this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_edit_tabs'));
            //var_dump($k);die;


          /*  $bl = $this->getLayout()->getBlock('promobannerslider/adminhtml_promobannerslider_edit_tabs')->setData('order', 123);

            var_dump($bl);*/

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promobannerslider')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
		//$this->_forward('edit');
	}

    //save links
    public function savelinksAction ()
    {
        $links = $this->getRequest()->getParam("link");

        try {
            foreach ( $links as $id => $item )
            {
                $item = trim($item);
                $item = $item == "" ? NULL : $item;
                $model = Mage::getModel('promobannerslider/bannersimages');
                $itemImage = $model->load($id);
                $itemImage->setLink($item)->save();
            }
        } catch ( Exception $e )
        {
            throw new Exception ($e);
        }

        Mage::getSingleton('core/session')->setBannersMessage('Links was successfully saved');
        die;
    }

    private function _removeAllFormHome ()
    {
        $model = Mage::getModel('promobannerslider/promobannerslider');
        $collection = $model->getCollection()->addFieldToFilter("is_home", 1);
        foreach ( $collection as $itemCollection )
        {
            $itemCollection->setIsHome(0)->save();
        }
    }

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
	  			
			$model = Mage::getModel('promobannerslider/promobannerslider');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));

            //clear other homepage
            if ( $model->getIsHome() == 1 )
            {
                $this->_removeAllFormHome();
            }

			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('promobannerslider')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promobannerslider')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}

    public function moveAction ()
    {
        if( ( $id = $this->getRequest()->getParam('id') ) > 0 ) {

            $item = Mage::getSingleton('promobannerslider/promobannerslider')->load( $id );

            $isHome = 0;
            if ( $item->getIsHome() == 0 )
            {
                $this->_removeAllFormHome();
                $isHome = 1;
            }

            $item->setIsHome( $isHome )->save();

        }
        $this->_redirect('*/*/');
    }

	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('promobannerslider/promobannerslider');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();

                $modelLinks = Mage::getModel('promobannerslider/links');

                $linksCollection = $modelLinks->getCollection()->addFieldToFilter("gallery_id", $this->getRequest()->getParam('id'));

                foreach ( $linksCollection as $itemLink )
                {
                    $itemLink->delete();
                }
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $promobannersliderIds = $this->getRequest()->getParam('promobannerslider');
        if(!is_array($promobannersliderIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($promobannersliderIds as $promobannersliderId) {
                    $promobannerslider = Mage::getModel('promobannerslider/promobannerslider')->load($promobannersliderId);
                    $promobannerslider->delete();

                    $modelLinks = Mage::getModel('promobannerslider/links');

                    $linksCollection = $modelLinks->getCollection()->addFieldToFilter("gallery_id", $promobannersliderId);

                    foreach ( $linksCollection as $itemLink )
                    {
                        $itemLink->delete();
                    }

                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($promobannersliderIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        return;
        $promobannersliderIds = $this->getRequest()->getParam('promobannerslider');
        if(!is_array($promobannersliderIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($promobannersliderIds as $promobannersliderId) {
                    $promobannerslider = Mage::getSingleton('promobannerslider/promobannerslider')
                        ->load($promobannersliderId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($promobannersliderIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'promobannerslider.csv';
        $content    = $this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'promobannerslider.xml';
        $content    = $this->getLayout()->createBlock('promobannerslider/adminhtml_promobannerslider_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    public function uploadAction()
    {
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);

            $path = Mage::getBaseDir('media') . DS . 'lexbrynov_banners';

            $result = $uploader->save(
                $path
            );
            $pathFile = 'lexbrynov_banners/'.$result['file'];
            $imageResized = Mage::getBaseDir('media') . DS. 'lexbrynov_banners/thumbs/'.$result['file'];
            $fullPath = $path. DS . $result['file'];

        // resize image only if the image file exists and the resized image file doesn'et exist
                // the image is resized proportionally with the width/height 135px
            try
            {
                if (file_exists( $fullPath )) {
                    $imageObj = new Varien_Image( $fullPath );
                    if ( $imageObj->getOriginalWidth() > 200 )
                    {
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        $imageObj->keepFrame(FALSE);
                        $imageObj->resize(200);
                        $imageObj->save($imageResized);
                        $imageResized = 'lexbrynov_banners/thumbs/'.$result['file'];
                    }
                    else
                    {
                        $imageResized = $pathFile;
                    } 
					// $imageResized = $pathFile;
                }

               /* if (file_exists( $fullPath )) {
                    $imageObj = new Varien_Image( $fullPath );
                    if ( $imageObj->getOriginalHeight() > 256 )
                    {
                        $imageObj->constrainOnly(TRUE);
                        $imageObj->keepAspectRatio(TRUE);
                        $imageObj->keepFrame(FALSE);
                        $imageObj->resize(null, 256);
                        $imageObj->save($fullPath);
                    }
                } */

            } catch ( Exception $e )
            {
                Mage::log($e->getMessage());
                throw new Exception ( $e );
            }


            //saveImage
            $model = Mage::getModel('promobannerslider/bannersimages');
            $model->addImage($pathFile, $imageResized);

            $result['url'] = Mage::getBaseUrl("media").'lexbrynov_banners/'.$result['file'];
            $result['path'] = $pathFile;
            $result['file'] = $result['file'] . '.tmp';
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
            Mage::getSingleton('core/session')->setBannersMessage('Images was successfully uploaded');
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
            Mage::log($e->getMessage());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }


}