<?php

class Jewellery_Jewellery_Model_Rewrite_CustomerForm extends Mage_Customer_Model_Form
{
	public function validateData(array $data)
    {
        $errors = array();
        foreach ($this->getAttributes() as $attribute) {
            if ($this->_isAttributeOmitted($attribute)) {
                continue;
            }

            if ($attribute->getIsUnique()) {
                if ($attribute->getIsRequired() || $data[$attribute->getAttributeCode()]) {
                    $customerId = $this->getEntity()->getData('entity_id'); //get current customer id
                    $customerCollection = Mage::getModel('customer/customer')
                        ->getCollection()
                        ->addAttributeToFilter($attribute->getAttributeCode(), $data[$attribute->getAttributeCode()]);
                    
                    $isFound = 0;
                    foreach ($customerCollection as $customer) {
                        $dbCustomerId=$customer->getId();
                        if ($dbCustomerId != $customerId) {//if the value is from another customer_id
                            $isFound = 1;  //we found a dup value
                            break;
                        }
                    }

                    if ($isFound) {
                        $label = $attribute->getStoreLabel();
                        $errors = array_merge($errors, array(Mage::helper('customer')->__('"%s" already used!',$label)));
                    }

                }
            }

            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = null;
            }
            $result = $dataModel->validateValue($data[$attribute->getAttributeCode()]);
            if ($result !== true) {
                $errors = array_merge($errors, $result);
            }
        }

        if (count($errors) == 0) {
            return true;
        }

        return $errors;
    }
}