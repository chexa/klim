<?php
class Jewellery_Jewellery_Model_Rewrite_Customer extends Mage_Customer_Model_Customer
{
    public function authenticate($login, $password)
    {
        $this->loadByCustomerNumber($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$this->validatePassword($password)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid customer number or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }
        Mage::dispatchEvent('customer_customer_authenticated', array(
           'model'    => $this,
           'password' => $password,
        ));
        return true;
    }

    public function loadByCustomerNumber($customerNumber)
    {
        $this->_getResource()->loadByCustomerNumber($this, $customerNumber);
        return $this;
    }

	/**
	 * Validate customer attribute values.
	 * For existing customer password + confirmation will be validated only when password is set (i.e. its change is requested)
	 *
	 * @return bool
	 */
	public function validate()
	{
		$errors = array();
		$customerHelper = Mage::helper('customer');
	/*	if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
			$errors[] = $customerHelper->__('The first name cannot be empty.');
		}

		if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
			$errors[] = $customerHelper->__('The last name cannot be empty.');
		}*/

		if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
			$errors[] = $customerHelper->__('Invalid email address "%s".', $this->getEmail());
		}

		$password = $this->getPassword();
		if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
			$errors[] = $customerHelper->__('The password cannot be empty.');
		}
		if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
			$errors[] = $customerHelper->__('The minimum password length is %s', 6);
		}
		$confirmation = $this->getConfirmation();
		if ($password != $confirmation) {
			$errors[] = $customerHelper->__('Please make sure your passwords match.');
		}

		$entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
		$attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
		if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
			$errors[] = $customerHelper->__('The Date of Birth is required.');
		}
		$attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
		if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
			$errors[] = $customerHelper->__('The TAX/VAT number is required.');
		}
		$attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
		if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
			$errors[] = $customerHelper->__('Gender is required.');
		}

		if (empty($errors)) {
			return true;
		}
		return $errors;
	}

}
