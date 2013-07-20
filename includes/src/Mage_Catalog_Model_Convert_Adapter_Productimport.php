<?php

class Mage_Catalog_Model_Convert_Adapter_Productimport extends Mage_Catalog_Model_Convert_Adapter_Product
{
    /**
    * Save product (import)
    * 
    * @param array $importData 
    * @throws Mage_Core_Exception
    * @return bool 
    */
    public function saveRow( array $importData )
    {
        $product = $this->getProductModel()->reset();

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'store');
                Mage::throwException($message);
                Mage::log(sprintf('Skip import row, required field "store" not defined', $message), null,'ce_product_import_export_errors.log');
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }
        
        if ($store === false) {
            $message = Mage::helper('catalog')->__('Skip import row, store "%s" field not exists', $importData['store']);
            Mage::throwException($message);
            Mage::log(sprintf('Skip import row, store "'.$importData['store'].'" field not exists', $message), null,'ce_product_import_export_errors.log');
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'sku');
            Mage::throwException($message);
            Mage::log(sprintf('Skip import row, required field "sku" not defined', $message), null,'ce_product_import_export_errors.log');
        }

        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);

        $new = true; // fix for duplicating attributes error

        $this->log('--------------');

        $this->log('Importing #' . $importData['sku']);

        if ($productId) {
            $this->log('Found');
            if ($this->getBatchParams('process_new_only')) {
                return true;
            }
            $product->load($productId);
            $new = false; // fix for duplicating attributes error
        }
        else {
            $this->log('NEW');

            $product->setSaveWhileImporting(true);

            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            /**
             * Check product define type
             */
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'type');
                Mage::throwException($message);
                Mage::log(sprintf('Skip import row, is not valid value "'.$value.'" for field type', $message), null,'ce_product_import_export_errors.log');
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);

            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__('Skip import row, is not valid value "%s" for field "%s"', $value, 'attribute_set');
                Mage::throwException($message);
                Mage::log(sprintf('Skip import row, is not valid value "'.$value.'" for field attribute_set', $message), null,'ce_product_import_export_errors.log');
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for new products not defined', $field);
                    Mage::throwException($message);
                }
            }
        }

        // delete disabled products
        // note "Disabled text should be converted to handle multi-lanugage values aka age::helper('catalog')->__(''); type deal
        
        if ('delete' == strtolower($importData['status'])) {
            $product = Mage::getSingleton('catalog/product')->load($productId);

            $this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'image' ) ) );
            $this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'small_image' ) ) );
            $this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $product -> getData( 'thumbnail' ) ) );

            $media_gallery = $product -> getData( 'media_gallery' );
            foreach ( $media_gallery['images'] as $image ) {
                $this -> _removeFile( Mage :: getSingleton( 'catalog/product_media_config' ) -> getMediaPath( $image['file'] ) );
            } 

            $product->delete();

            return true;
        } 

        if ($importData['type'] == 'configurable') {
            
            $product->setCanSaveConfigurableAttributes(true);
            $configAttributeCodes = $this->userCSVDataAsArray($importData['config_attributes']);
            $usingAttributeIds = array();

            /***
            * Check the product's super attributes (see catalog_product_super_attribute table), and make a determination that way.
            **/
            $cspa  = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
            $attr_codes = array();
            if(isset($cspa) && !empty($cspa)){ //found attributes
                foreach($cspa as $cs_attr){
                    $attr_codes[] = $cs_attr['attribute_id'];
                }
            }

            foreach($configAttributeCodes as $attributeCode) {
                $attribute = $product->getResource()->getAttribute(trim($attributeCode));
                if ($product->getTypeInstance()->canUseAttribute($attribute)) {
                    //if (!in_array($attributeCode,$attr_codes)) { // fix for duplicating attributes error
                    if ($new) { // fix for duplicating attributes error // <---------- this must be true to fill $usingAttributes
                        $usingAttributeIds[] = $attribute->getAttributeId();
                    }
                }
            }
            if (!empty($usingAttributeIds)) {
                $product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
                $updateconfigurablearray = array();
                $insidearraycount=0;
                $finalarraytoimport = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                $updateconfigurablearray = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                    
                    foreach($updateconfigurablearray as $eacharrayvalue) {    
                     if($this->getBatchParams('configurable_use_default') != "") {            
                         $finalarraytoimport[$insidearraycount]['use_default'] = $this->getBatchParams('configurable_use_default'); //added in 1.5.x 
                     }
                     $finalarraytoimport[$insidearraycount]['label'] = $eacharrayvalue['frontend_label'];
                     $insidearraycount+=1;
                    }
                $product->setConfigurableAttributesData($finalarraytoimport);
                $product->setCanSaveConfigurableAttributes(true);
                $product->setCanSaveCustomOptions(true);
            }
            if (isset($importData['associated'])) {
                $product->setConfigurableProductsData($this->skusToIds($importData['associated'], $product));
            }
        }

        if ( isset( $importData['category_ids'] ) ) {
            $product -> setCategoryIds( $importData['category_ids'] );
        } 

        if ( isset( $importData['categories'] ) && $importData['categories'] !="" ) {
            if (!empty($importData['store'])) {
                $cat_store = $this -> _stores[$importData['store']];
            } else {
                $message = Mage :: helper( 'catalog' ) -> __( 'Skip import row, required field "store" for new products not defined', $field );
                Mage :: throwException( $message );
            } 
            $categoryIds = $this -> _addCategories( $importData['categories'], $cat_store );
            if ( $categoryIds ) {
                $product -> setCategoryIds( $categoryIds );
            } 
        } 
        
        foreach ( $this -> _ignoreFields as $field ) {
            if ( isset( $importData[$field] ) ) {
                unset( $importData[$field] );
            } 
        } 
        
        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }
        
        if ( isset( $importData['websites'] ) ) {
            $websiteIds = $product -> getWebsiteIds();
            if ( !is_array( $websiteIds ) ) {
                $websiteIds = array();
            } 
            $websiteCodes = explode( ',', $importData['websites'] );
            foreach ( $websiteCodes as $websiteCode ) {
                try {
                    $website = Mage :: app() -> getWebsite( trim( $websiteCode ) );
                    if ( !in_array( $website -> getId(), $websiteIds ) ) {
                        $websiteIds[] = $website -> getId();
                    } 
                } 
                catch ( Exception $e ) {
                } 
            } 
            $product -> setWebsiteIds( $websiteIds );
            unset( $websiteIds );
        } 
        
        $custom_options = array();

        foreach ( $importData as $field => $value ) {
            //SEEMS TO BE CONFLICTING ISSUES WITH THESE 2 CHOICES AND DOESNT SEEM TO REQUIRE THIS IN ALL THE TESTING SO LEAVING COMMENTED
            //if ( in_array( $field, $this -> _inventoryFields ) ) { 
                //continue;
            //} 
            /*
            if (in_array($field, $this->_inventorySimpleFields))
            {
                continue;
            }
            */
            if ( in_array( $field, $this -> _imageFields ) ) {
                continue;
            } 
            
            $attribute = $this -> getAttribute( $field );

            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;
            
            if ( $attribute -> getFrontendInput() == 'multiselect' ) {
                $value = explode( self :: MULTI_DELIMITER, $value );
                $isArray = true;
                $setValue = array();
            } 
            
            if ( $value && $attribute -> getBackendType() == 'decimal' ) {
                $setValue = $this -> getNumber( $value );
            } 
            
            if ( $attribute -> usesSource() ) {
                $options = $attribute -> getSource() -> getAllOptions( false );
                
                if ( $isArray ) {
                    foreach ( $options as $item ) {
                        if ( in_array( $item['label'], $value ) ) {
                            $setValue[] = $item['value'];
                        } 
                    } 
                } 
                else {
                    $setValue = null;
                    foreach ( $options as $item ) {
                        if ( $item['label'] == $value ) {
                            $setValue = $item['value'];
                        } 
                    }

                    /**
                     * Modified by Nikita Chirkov
                     * Automatically add attribute value if it is empty
                     */
                    if (is_null($setValue) && trim($value) != '') {
                        $attributeOption['option'] = array($value, $value);
                        $attributeOptionResult = array('value' => $attributeOption); 
                        $attribute->setData('option', $attributeOptionResult); 
                        $attribute->save();

                        // reload collection and options (copypaste from Mage_Eav_Model_Entity_Attribute_Source_Table::getAllOptions
                        // we do not use this method call because it doesn't reload the collection
                        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                            ->setPositionOrder('asc')
                            ->setAttributeFilter($attribute->getId())
                            ->setStoreFilter($attribute->getStoreId())
                            ->load();

                        $options = $collection->toOptionArray();

                        foreach ( $options as $item ) {
                            if ( $item['label'] == $value ) {
                                $setValue = $item['value'];
                            }
                        }
                    }
                } 
            } 
            
            $product -> setData( $field, $setValue );
        } 
        
        if ( !$product -> getVisibility() ) {
            $product -> setVisibility( Mage_Catalog_Model_Product_Visibility :: VISIBILITY_NOT_VISIBLE );
        } 
        
        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array(); 
            
        foreach ( $inventoryFields as $field ) {
            if ( isset( $importData[$field] ) ) {
                if ( in_array( $field, $this -> _toNumber ) ) {
                    $stockData[$field] = $this -> getNumber( $importData[$field] );
                } 
                else {
                    $stockData[$field] = $importData[$field];
                } 
            } 
        } 
        $product -> setStockData( $stockData );
        
        
        if($new || $this->getBatchParams('reimport_images') == "true") {  //starts CHECK FOR IF REIMPORTING IMAGES TO PRODUCTS IS TRUE
            //this is a check if we want to delete all images before import of images from csv
            if($this->getBatchParams('deleteall_andreimport_images') == "true" && $importData["image"] != "" && $importData["small_image"] != "" && $importData["thumbnail"] != "") {
                $attributes = $product->getTypeInstance()->getSetAttributes();

                if (isset($attributes['media_gallery'])) {
                    $gallery = $attributes['media_gallery'];
                    //Get the images
                    $galleryData = $product->getMediaGallery();

                    if(!empty($galleryData)) {
                        foreach($galleryData['images'] as $image){
                            //If image exists
                            if ($gallery->getBackend()->getImage($product, $image['file'])) {
                                $gallery->getBackend()->removeImage($product, $image['file']);
                                //if ( file_exists(Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $image['file'] ) ) {
                                if ( file_exists( $image['file'] ) ) {
                                    $ext = substr(strrchr($image['file'], '.'), 1);
                                    //if( strlen( $ext ) == 3 ) { //maybe needs to be 3
                                    if( strlen( $ext ) == 4 ) {
                                        unlink (Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $image['file']);
                                    }
                                }
                            }
                        }
                    }
                }
            }


            if($importData["image"] != "" || $importData["small_image"] != "" || $importData["thumbnail"] != "" ) {
                $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();

                $arrayToMassAdd = array();
    
                foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
                    if (isset($importData[$mediaAttributeCode])) {
                        $file = $importData[$mediaAttributeCode];
                        if(file_exists(Mage :: getBaseDir( 'media' ) . DS . 'import' . $file)){
                            if (trim($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                                $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                            }
                        }
                    }
                }

                if($this->getBatchParams('exclude_images') == "true") {
                    $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes($product, $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import', false, true, !empty( $importData['gallery'] ));
                } else {
                    $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes($product, $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import', false, false, !empty( $importData['gallery'] ));
                }

                foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
                    $addedFile = '';
                    if (isset($importData[$mediaAttributeCode . '_label'])) {
                        $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                        if (isset($importData[$mediaAttributeCode])) {
                            $keyInAddedFile = array_search($importData[$mediaAttributeCode],
                                $addedFilesCorrespondence['alreadyAddedFiles']);
                            if ($keyInAddedFile !== false) {
                                $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                            }
                        }

                        if (!$addedFile) {
                            $addedFile = $product->getData($mediaAttributeCode);
                        }
                        if ($fileLabel && $addedFile) {
                            $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
                        }
                    }
                }
                                    
            } //end check on empty values
            
            if ( !empty( $importData['gallery'] ) ) {
                $galleryData = explode( ',', $importData["gallery"] );
                foreach( $galleryData as $gallery_img ) {
                    try {
                        if($this->getBatchParams('exclude_gallery_images') == "true") {
                          $product -> addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import' . $gallery_img, null, false, true );
                        } else {
                          $product -> addImageToMediaGallery( Mage :: getBaseDir( 'media' ) . DS . 'import' . $gallery_img, null, false, false );
                        }
                    } 
                    catch ( Exception $e ) {
                        Mage::log(sprintf('failed to import gallery images: %s', $e->getMessage()), null,'ce_product_import_export_errors.log');
                    } 
                } 
            } 
        }
        // this else is for check for if we can reimport products
        $product -> setIsMassupdate( true );
        $product -> setExcludeUrlRewrite( true );
        //PATCH FOR Fatal error: Call to a member function getStoreId() on a non-object in D:\web\magento\app\code\core\Mage\Bundle\Model\Selection.php on line 52
        if (!Mage::registry('product')) {
            Mage::register('product', Mage::getModel('catalog/product')->setStoreId(0));
            //Mage::register('product', $product); maybe this is needed for when importing multi-store bundle vs above
        }
        $product -> save();

        /* ADDED FIX FOR IMAGE LABELS */
        if(isset($imagelabeldataforimport)) {
    
            #echo "PROD ID: " . $product->getId() . "<br/>";
            #echo "LABELS: " . $imagelabeldataforimport . "<br/>";
            $resource = Mage::getSingleton('core/resource');
           $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
            $prefixlabels = Mage::getConfig()->getNode('global/resources/db/table_prefix');  
            $readlabels = $resource->getConnection('core_read');
            $writelabels = $resource->getConnection('core_write');
            $select_qry_labels =$readlabels->query("SELECT value_id FROM ".$prefixlabels."catalog_product_entity_media_gallery WHERE entity_id = '". $product->getId() ."'");
            $row_labels = $select_qry_labels->fetch();
            $value_id = $row_labels['value_id'];
            // now $write label to db
            $writelabels->query("UPDATE ".$prefix."catalog_product_entity_media_gallery_value SET label = '".$imagelabeldataforimport."' WHERE value_id = '".$value_id."'");  
            //this is for if you have flat product catalog enabled.. need to write values to both places
            #$writelabels->query("UPDATE ".$prefix."catalog_product_flat_1 SET image_label = '".$imagelabeldataforimport."' WHERE entity_id = '". $product->getId() ."'"); 
            #$writelabels->query("UPDATE ".$prefix."catalog_product_flat_1 SET image_label = '".$imagelabeldataforimport."' WHERE entity_id = '". $product->getId() ."'");
            #SELECT attribute_id FROM ".$prefixlabels."eav_attribute WHERE attribute_code = 'image_label';
            #$writelabels->query("UPDATE ".$prefix."catalog_product_entity_varchar SET value = '".$imagelabeldataforimport."' WHERE entity_id = '". $product->getId() ."' AND attribute_id = 101");  
                
        }

        if(isset($smallimagelabeldataforimport)) {
    
            #echo "PROD ID: " . $product->getId() . "<br/>";
            #echo "LABELS: " . $smallimagelabeldataforimport . "<br/>";
            $resource = Mage::getSingleton('core/resource');
           $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
            $prefixlabels = Mage::getConfig()->getNode('global/resources/db/table_prefix');  
            $readlabels = $resource->getConnection('core_read');
            $writelabels = $resource->getConnection('core_write');
            $select_qry_labels =$readlabels->query("SELECT value_id FROM ".$prefixlabels."catalog_product_entity_media_gallery WHERE entity_id = '". $product->getId() ."'");
            $row_labels = $select_qry_labels->fetch();
            $value_id = $row_labels['value_id']+1;
            // now $write label to db
            $writelabels->query("UPDATE ".$prefix."catalog_product_entity_media_gallery_value SET label = '".$smallimagelabeldataforimport."' WHERE value_id = '".$value_id."'"); 
                
        }

        if(isset($thumbnailimagelabeldataforimport)) {
    
            #echo "PROD ID: " . $product->getId() . "<br/>";
            #echo "LABELS: " . $smallimagelabeldataforimport . "<br/>";
            $resource = Mage::getSingleton('core/resource');
           $prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix'); 
            $prefixlabels = Mage::getConfig()->getNode('global/resources/db/table_prefix');  
            $readlabels = $resource->getConnection('core_read');
            $writelabels = $resource->getConnection('core_write');
            $select_qry_labels =$readlabels->query("SELECT value_id FROM ".$prefixlabels."catalog_product_entity_media_gallery WHERE entity_id = '". $product->getId() ."'");
            $row_labels = $select_qry_labels->fetch();
            $value_id = $row_labels['value_id']+2;
            // now $write label to db
            $writelabels->query("UPDATE ".$prefix."catalog_product_entity_media_gallery_value SET label = '".$thumbnailimagelabeldataforimport."' WHERE value_id = '".$value_id."'"); 
                
        }

        /* END FIX FOR IMAGE LABLES */


        return true;
    } 
    /**
     * Edit tier prices
     * 
     * Uses a pipe-delimited string of qty:price to set tiers for the product row and appends.
     * Removes if REMOVE is present.
     * 
     * @todo Prevent duplicate tiers (by qty) being set
     * @internal Magento will save duplicate tiers; no enforcing unique tiers by qty, so we have to do this manually
     * @param Mage_Catalog_Model_Product $product Current product row
     * @param string $tier_prices_field Pipe-separated in the form of qty:price (e.g. 0=250=12.75|0=500=12.00)
     */    
    private function _editTierPrices(&$product, $tier_prices_field = false, $store)
    {
        if (($tier_prices_field) && !empty($tier_prices_field)) {
            
            if(trim($tier_prices_field) == 'REMOVE'){
            
                $product->setTierPrice(array());
            
            } else {
                
                
                if($this->getBatchParams('append_tier_prices') == "true") { 
                        //get current product tier prices
                    $existing_tps = $product->getTierPrice();
                } else {
                    $existing_tps = array();
                }
                
                $etp_lookup = array();
                //make a lookup array to prevent dup tiers by qty
                foreach($existing_tps as $key => $etp){
                    $etp_lookup[intval($etp['price_qty'])] = $key;
                }
                
                //parse incoming tier prices string
                $incoming_tierps = explode('|',$tier_prices_field);
                                $tps_toAdd = array();  
                                $tierpricecount=0;              
                            foreach($incoming_tierps as $tier_str){
                                        //echo "t: " . $tier_str;
                    if (empty($tier_str)) continue;
                    
                    $tmp = array();
                    $tmp = explode('=',$tier_str);
                    
                    if ($tmp[1] == 0 && $tmp[2] == 0) continue;
                                        //echo ('adding tier');
                    //print_r($tmp);
                    $tps_toAdd[$tierpricecount] = array(
                                        'website_id' => 0, // !!!! this is hard-coded for now
                                        #'website_id' => $tmp[0], // !!!! this is hard-coded for now
                                        #'website_id' => $store->getWebsiteId(),
                                        'cust_group' => $tmp[0], // !!! so is this
                                        'price_qty' => $tmp[1],
                                        'price' => $tmp[2],
                                        'delete' => ''
                                    );
                                    
                    //drop any existing tier values by qty
                    if(isset($etp_lookup[intval($tmp[1])])){
                        unset($existing_tps[$etp_lookup[intval($tmp[1])]]);
                    }
                    $tierpricecount++;
                }

                //combine array
                $tps_toAdd =  array_merge($existing_tps, $tps_toAdd);
               
                                 //print_r($tps_toAdd);
                //save it
                $product->setTierPrice($tps_toAdd);
            }
            
        }
    }

    
    protected function userCSVDataAsArray( $data )
    {
        return explode( ',', str_replace( " ", " ", $data ) );
    } 
    
    protected function skusToIds( $userData, $product )
    {
        $productIds = array();
        foreach ( $this -> userCSVDataAsArray( $userData ) as $oneSku ) {
            if ( ( $a_sku = ( int )$product -> getIdBySku( $oneSku ) ) > 0 ) {
                parse_str( "position=", $productIds[$a_sku] );
            } 
        } 
        return $productIds;
    } 
    
     protected $_categoryCache = array();
   protected function _addCategories($categories, $store)
    {
        // $rootId = $store->getRootCategoryId();
        // $rootId = Mage::app()->getStore()->getRootCategoryId();
        //$rootId = 2; // our store's root category id
                if($this->getBatchParams('root_catalog_id') != "") {
                    $rootId = $this->getBatchParams('root_catalog_id');
                } else {
                  $rootId = 2; 
                }
        if (!$rootId) {
            return array();
        }
        $rootPath = '1/'.$rootId;
        if (empty($this->_categoryCache[$store->getId()])) {
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name');
            $collection->getSelect()->where("path like '".$rootPath."/%'");

            foreach ($collection as $cat) {
                $pathArr = explode('/', $cat->getPath());
                $namePath = '';
                for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
                    //if(!is_null($collection->getItemById($pathArr[$i]))) { }
                    $name = $collection->getItemById($pathArr[$i])->getName();
                    $namePath .= (empty($namePath) ? '' : '/').trim($name);
                }
                $cat->setNamePath($namePath);
            }
            
            $cache = array();
            foreach ($collection as $cat) {
                $cache[strtolower($cat->getNamePath())] = $cat;
                $cat->unsNamePath();
            }
            $this->_categoryCache[$store->getId()] = $cache;
        }
        $cache =& $this->_categoryCache[$store->getId()];
        
        $catIds = array();
          //->setIsAnchor(1)
          //Delimiter is ' , ' so people can use ', ' in multiple categorynames
        foreach (explode(' , ', $categories) as $categoryPathStr) {
            //Remove this line if your using ^ vs / as delimiter for categories.. fix for cat names with / in them
           $categoryPathStr = preg_replace('#\s*/\s*#', '/', trim($categoryPathStr));
            if (!empty($cache[$categoryPathStr])) {
                $catIds[] = $cache[$categoryPathStr]->getId();
                continue;
            }
            $path = $rootPath;
            $namePath = '';
             #foreach (explode('^', $categoryPathStr) as $catName) {
             foreach (explode('/', $categoryPathStr) as $catName) {
                $namePath .= (empty($namePath) ? '' : '/').strtolower($catName);
                if (empty($cache[$namePath])) {
                    $cat = Mage::getModel('catalog/category')
                        ->setStoreId($store->getId())
                        ->setPath($path)
                        ->setName($catName)
                        ->setIsActive(1)
                        ->save();
                    $cache[$namePath] = $cat;
                }
                $catId = $cache[$namePath]->getId();
                $path .= '/'.$catId;
            }
            if ($catId) {
                $catIds[] = $catId;
            }
        }
        return join(',', $catIds);
    }
    
    protected function _removeFile( $file )
    {
        if ( file_exists( $file ) ) {
        $ext = substr(strrchr($file, '.'), 1);
            if( strlen( $ext ) == 4 ) {
                if ( unlink( $file ) ) {
                    return true;
                } 
            }
        } 
        return false;
    }

    protected function log($str)
    {
        Mage::log($str, null, 'product_import.log');
        return $this;
    }
}