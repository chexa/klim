<?php
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Jewellery_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
//            $postData = $this->getRequest()->getPost('billing', array());
//            $data = $this->_filterPostData($postData);
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                $result = $this->_saveShippingMethodAndPayment();
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

     protected function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    protected function getQuote()
    {
        return  Mage::getSingleton('checkout/session')->getQuote();
    }

    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    protected function getAddress()
    {
        if (is_null($this->_address)) {
            if ($this->isCustomerLoggedIn()) {
                $this->_address = $this->getQuote()->getBillingAddress();
            } else {
                $this->_address = Mage::getModel('sales/quote_address');
            }
        }

        return $this->_address;
    }

    /**
     * @return bool
     */
    protected function prepareAndSaveBilling()
    {
        $address = $this->getAddress();
        $params = array(
            "address_id" => $address->getId(),
            "company" => $address->getCompany(),
            "prefix" => $address->getPrefix(),
            "firstname" => $address->getFirstname(),
            "lastname" => $address->getLastname(),
            "street" => array($address->getStreet(1)),
            "street_additional" => $address->getStreetAdditional(),
            "postcode" => $address->getPostcode(),
            "city" => $address->getCity(),
            "telephone" => $address->getTelephone(),
            "country_id" => $address->getCountryId(),
            "region_id" => $address->getCompany(),
            "region" => $address->getRegionId(),
            "use_for_shipping" => 1
        );

        $data = $params;
        $customerAddressId = $params["address_id"];

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }
        $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

        if (!isset($result['error'])) {
            $result = $this->_saveShippingMethodAndPayment(true);
            return true;
        }
        return false;
    }

    /**
     * Checkout page
     */
    public function indexAction()
    {
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }

        //save billing
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getOnepage()->initCheckout();
        $result = $this->prepareAndSaveBilling();
        $this->loadLayout();

        //$this->loadLayout('checkout_onepage_review');
       // $block = $this->getLayout()->createBlock("checkout/onepage_stephelper", "onepage_stephelper")->setData("html", $this->getLayout()->getBlock('onepage_review_info')->toHtml());
        $block = $this->getLayout()->createBlock('core/template', "onepage_stephelper")
            ->setTemplate('checkout/onepage/payment/info.phtml')
            ->setData("html", $this->getLayout()->getBlock('onepage_review_info')->toHtml());

        $this->getLayout()->getBlock("checkout.onepage")->append($block);

        $this->getLayout()->removeOutputBlock("onepage_stephelper");

        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            /*
            $result will have erro data if shipping method is empty
            */
            if(!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
                    array('request'=>$this->getRequest(),
                        'quote'=>$this->getOnepage()->getQuote()));

                /**
                 * PAYMENT
                 */
                $result = $this->_saveShippingMethodAndPayment();



//                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

//                $result['goto_section'] = 'review';
//                $result['update_section'] = array(
//                    'name' => 'payment-method',
//                    'html' => $this->_getPaymentMethodsHtml()
//                );
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }


    protected function _saveShippingMethodAndPayment($avoidRedirect = false)
    {
        $result = array();
        try {
            // set payment to quote

            $data = $this->getRequest()->getPost('payment', array('method' => 'checkmo'));
            $result = $this->getOnepage()->savePayment($data);
            if ( $avoidRedirect )
            {
                return $result;
            }

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

        return $result;
    }
}


