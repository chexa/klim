<?php
class Jewellery_Jewellery_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SENDREGISTRATIONDATA_EMAIL_TEMPLATE      = 'jewellery/registration/sendregistrationdata_email_template';
    const XML_PATH_SENDREGISTRATIONDATA_EMAIL_IDENTITY      = 'jewellery/registration/sendregistrationdata_email_identity';

    public function getTopCategory() {
        $layer = Mage::getSingleton('catalog/layer');
        $category   = $layer->getCurrentCategory();

        $it = 10; //Amount of iterations before script gives up

        if ($category) {
            if ($category->getLevel() < 2) {
                return $category;
            }

            while($category->getLevel() != 2 && $it > 0) {
                $category = $category->getParentCategory();

                if (!$category) {
                    break;
                }
            }

            if ($category) {
                return $category;
            }
            else {
                return null;
            }
        }
    }

    public function getPreCreateUrl() {
        return $this->_getUrl('customer/account/storestep');
    }

    public function getToRegisterUrl()
    {
        return $this->_getUrl('customer/account/toregisterPost');
    }

    public function getLicensePostUrl()
    {
        return $this->_getUrl('customer/account/licensePost');
    }

    public function getOverviewUrl()
    {
        return $this->_getUrl('customer/account/overview');
    }

    public function getOverviewPostUrl()
    {
        return $this->_getUrl('customer/account/overviewPost');
    }

    public function getLicenseUrl()
    {
        return $this->_getUrl('customer/account/license');
    }

    public function getSuccessUrl()
    {
        return $this->_getUrl('customer/account/success');
    }

    public function getLicensefilename()
    {
        $jSession = Mage::getSingleton('jewellery/session');
        return $jSession->getCustomerLicenseFilename();
    }

    public function sendNewCustomerEmail()
    {
        $jSession = Mage::getSingleton('jewellery/session');

        if(!Mage::getStoreConfig(self::XML_PATH_SENDREGISTRATIONDATA_EMAIL_TEMPLATE) || !Mage::getStoreConfig(self::XML_PATH_SENDREGISTRATIONDATA_EMAIL_IDENTITY))  {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $email = Mage::getModel('core/email_template');

        if ($jSession->getCustomerLicenseUploadType() == 'upload') {
            print '</pre>';
            $path = Mage::getBaseDir('media') . DS . 'licenses' . DS . $jSession->getCustomerLicenseFilename();
            $fileContents = file_get_contents($path);
            $attachment = $email->getMail()->createAttachment($fileContents);
            $attachment->filename = 'License.' . pathinfo($path, PATHINFO_EXTENSION);
        }
        

        $email->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_SENDREGISTRATIONDATA_EMAIL_TEMPLATE),
            Mage::getStoreConfig(self::XML_PATH_SENDREGISTRATIONDATA_EMAIL_IDENTITY),
            'magentojewellery@mailinator.com', //$this->getEmail(),
            'test user', //$this->getName(),
            array(
                'customer' => $jSession->getCustomer(),
                'address'  => $jSession->getCustomer()->getAddress(),
                'address_street' => $jSession->getCustomer()->getAddress()->getData('street'),
                'license_info' => $jSession->getCustomerLicenseUploadType() == 'upload' ? 'License file is attached' : 'License will ne se'
            )
        );

        $translate->setTranslateInline(true);

        return $this;
    }

    public function getSelectedFilterValue()
    {
        return Mage::app()->getRequest()->getParam(Mage::helper('jewellery')->getMaterialAttributeCode(), 'all');
    }

    public function getChildProducts($product)
    {
        if ($product->getTypeId() == 'configurable') {
            //$childIds = Mage::getModel('catalog/product_type_configurable')
            //            ->getChildrenIds($product->getId());

            /**
             * Get children products (all associated children products data)
             */
            if ($product->getId()) {
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);
            } else {
                // for import only
                $childIds = array_keys($product->getConfigurableProductsData());

                $childProducts = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('legierung')
                    ->addAttributeToSelect('material')
                    ->addAttributeToSelect('unverbindliche_preisempfehlung')
                    ->addAttributeToFilter('entity_id', array('in' => $childIds));

            }

            return $childProducts;
        }

        return null;

    }

    public function getMaterialAttributeCode()
    {
        return 'material_kategorie';
    }
}
