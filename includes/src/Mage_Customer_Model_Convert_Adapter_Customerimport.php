<?php


class Mage_Customer_Model_Convert_Adapter_Customerimport
    extends Mage_Customer_Model_Convert_Adapter_Customer
{
    /*
     * saveRow function for saving each customer data
     *
     * params args array
     * return array
     */
    public function saveRow($importData)
    {
        $customer = $this->getCustomerModel();
        $customer->setId(null);

        if (empty($importData['website'])) {
            $message = Mage::helper('customer')->__('Skipping import row, required field "%s" is not defined.', 'website');
            Mage::throwException($message);
        }

        $website = $this->getWebsiteByCode($importData['website']);

        if ($website === false) {
            $message = Mage::helper('customer')->__('Skipping import row, website "%s" field does not exist.', $importData['website']);
            Mage::throwException($message);
        }
//        if (empty($importData['email'])) {
//            $message = Mage::helper('customer')->__('Skipping import row, required field "%s" is not defined.', 'email');
//            Mage::throwException($message);
//        }

        if (empty($importData['customer_number'])) {
            $message = Mage::helper('customer')->__('Skipping import row, required field "%s" is not defined.', 'email');
            Mage::throwException($message);
        }

//        $customer->setWebsiteId($website->getId())
//            ->loadByEmail($importData['email']);
        $customer->setWebsiteId($website->getId())
            ->loadByCustomerNumber($importData['customer_number']);

        if (!$customer->getId()) {
            $customerGroups = $this->getCustomerGroups();
            /**
             * Check customer group
             */
            if (empty($importData['group']) || !isset($customerGroups[$importData['group']])) {
                $value = isset($importData['group']) ? $importData['group'] : '';
                $message = Mage::helper('catalog')->__('Skipping import row, the value "%s" is not valid for the "%s" field.', $value, 'group');
                Mage::throwException($message);
            }
            $customer->setGroupId($customerGroups[$importData['group']]);

            foreach ($this->_requiredFields as $field) {
                if (!isset($importData[$field])) {
                    $message = Mage::helper('catalog')->__('Skip import row, required field "%s" for the new customer is not defined.', $field);
                    Mage::throwException($message);
                }
            }

            $customer->setWebsiteId($website->getId());

            if (empty($importData['created_in']) || !$this->getStoreByCode($importData['created_in'])) {
                $customer->setStoreId(0);
            }
            else {
                $customer->setStoreId($this->getStoreByCode($importData['created_in'])->getId());
            }

            if (empty($importData['password_hash'])) {
                if (isset($importData['password'])) {
                    //$customer->setPasswordHash($customer->hashPassword($importData['password']));
                    $customer->setPasswordHash(md5($importData['password']));
                }
                //else {
                //    $customer->setPasswordHash($customer->hashPassword($customer->generatePassword(8)));
                //}
            }
        }
        elseif (!empty($importData['group'])) {
            $customerGroups = $this->getCustomerGroups();
            /**
             * Check customer group
             */
            if (isset($customerGroups[$importData['group']])) {
                $customer->setGroupId($customerGroups[$importData['group']]);
            }
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_billingFields)) {
                continue;
            }
            if (in_array($field, $this->_shippingFields)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                }
                else {
                    $setValue = null;
                    foreach ($options as $item) {
                        if ($item['label'] == $value) {
                            $setValue = $item['value'];
                        }
                    }
                }
            }

            $customer->setData($field, $setValue);
        }

        if (isset($importData['is_subscribed'])) {
            $customer->setData('is_subscribed', $importData['is_subscribed']);
        }

        //$customer->setImportMode(true);
        //$customer->save();

        //return $this;

        $importBillingAddress = $importShippingAddress = true;
        $savedBillingAddress = $savedShippingAddress = false;

        /**
         * Check Billing address required fields
         */
        foreach ($this->_billingRequiredFields as $field) {
            if (empty($importData[$field])) {
                $importBillingAddress = false;
            }
        }

        /**
         * Check Sipping address required fields
         */
        foreach ($this->_shippingRequiredFields as $field) {
            if (empty($importData[$field])) {
                $importShippingAddress = false;
            }
        }

        $onlyAddress = false;

        /**
         * Check addresses
         */
        if ($importBillingAddress && $importShippingAddress) {
            $onlyAddress = true;
            foreach ($this->_addressFields as $field) {
                if (!isset($importData['billing_'.$field]) && !isset($importData['shipping_'.$field])) {
                    continue;
                }
                if (!isset($importData['billing_'.$field]) || !isset($importData['shipping_'.$field])) {
                    $onlyAddress = false;
                    break;
                }
                if ($importData['billing_'.$field] != $importData['shipping_'.$field]) {
                    $onlyAddress = false;
                    break;
                }
            }

            if ($onlyAddress) {
                $importShippingAddress = false;
            }
        }
		//var_dump($onlyAddress); die;
        //Mage::throwException($importBillingAddress);

        //var_dump($importBillingAddress); die;
        
        /**
         * Import billing address
         */
        if ($importBillingAddress) {
            $billingAddress = $this->getBillingAddressModel();
            if ($customer->getDefaultBilling()) {
                $billingAddress->load($customer->getDefaultBilling());
            }
            else {
                $billingAddress->setData(array());
            }

            foreach ($this->_billingFields as $field) {
                $cleanField = Mage::helper('core/string')->substr($field, 8);

                if (isset($importData[$field])) {
                    $billingAddress->setDataUsingMethod($cleanField, $importData[$field]);
                }
                elseif (isset($this->_billingMappedFields[$field])
                    && isset($importData[$this->_billingMappedFields[$field]])) {
                    $billingAddress->setDataUsingMethod($cleanField, $importData[$this->_billingMappedFields[$field]]);
                }
            }

            $street = array();
            foreach ($this->_billingStreetFields as $field) {
                if (!empty($importData[$field])) {
                    $street[] = $importData[$field];
                }
            }
            if ($street) {
                $billingAddress->setDataUsingMethod('street', $street);
            }

            $billingAddress->setCountryId($importData['billing_country']);
            $regionName = isset($importData['billing_region']) ? $importData['billing_region'] : '';
            if ($regionName) {
                $regionId = $this->getRegionId($importData['billing_country'], $regionName);
                $billingAddress->setRegionId($regionId);
            }


            // COMPANY
            if ($importData['billing_firstname'] || $importData['billing_lastname']) {
                $company = $importData['billing_firstname'] . ' ' . $importData['billing_lastname'];

                $billingAddress->setDataUsingMethod('company', trim($company));
            }


            if ($customer->getId()) {
                $billingAddress->setCustomerId($customer->getId());

				$billingAddress->setIsDefaultShipping(1);
                $billingAddress->save();
                $customer->setDefaultBilling($billingAddress->getId());

                if ($onlyAddress) {
                    $customer->setDefaultShipping($billingAddress->getId());
                }
                $savedBillingAddress = true;
            }
			
        }

		
		
        /**
         * Import shipping address
         */
        if ($importShippingAddress) {
            $shippingAddress = $this->getShippingAddressModel();
            if ($customer->getDefaultShipping() && $customer->getDefaultBilling() != $customer->getDefaultShipping()) {
                $shippingAddress->load($customer->getDefaultShipping());
            }
            else {
                $shippingAddress->setData(array());
            }

            foreach ($this->_shippingFields as $field) {
                $cleanField = Mage::helper('core/string')->substr($field, 9);

                if (isset($importData[$field])) {
                    $shippingAddress->setDataUsingMethod($cleanField, $importData[$field]);
                }
                elseif (isset($this->_shippingMappedFields[$field])
                    && isset($importData[$this->_shippingMappedFields[$field]])) {
                    $shippingAddress->setDataUsingMethod($cleanField, $importData[$this->_shippingMappedFields[$field]]);
                }
            }

            $street = array();
            foreach ($this->_shippingStreetFields as $field) {
                if (!empty($importData[$field])) {
                    $street[] = $importData[$field];
                }
            }
            if ($street) {
                $shippingAddress->setDataUsingMethod('street', $street);
            }

            $shippingAddress->setCountryId($importData['shipping_country']);
            $regionName = isset($importData['shipping_region']) ? $importData['shipping_region'] : '';
            if ($regionName) {
                $regionId = $this->getRegionId($importData['shipping_country'], $regionName);
                $shippingAddress->setRegionId($regionId);
            }

            if ($customer->getId()) {
                $shippingAddress->setCustomerId($customer->getId());
                $shippingAddress->save();
                $customer->setDefaultShipping($shippingAddress->getId());

                $savedShippingAddress = true;
            }
        }
		
        $customer->save();

        $customer->setConfirmation(null);
        $customer->setStatus(1);

        $customer->setImportMode(true);
        $customer->save();
        $saveCustomer = false;

        if ($importBillingAddress && !$savedBillingAddress) {
            $saveCustomer = true;
            $billingAddress->setCustomerId($customer->getId());
            $billingAddress->save();
			$customer->setDefaultShipping($billingAddress->getId());
			$customer->setDefaultBilling($billingAddress->getId());
            
           /* if ($onlyAddress) {
                $customer->setDefaultShipping($billingAddress->getId());
            } */
        }
        if ($importShippingAddress && !$savedShippingAddress) {
            $saveCustomer = true;
            $shippingAddress->setCustomerId($customer->getId());
            $shippingAddress->save();
            $customer->setDefaultShipping($shippingAddress->getId());
        }
        if ($saveCustomer) {
            $customer->save();
        }

        return $this;
    }

}
