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
}
