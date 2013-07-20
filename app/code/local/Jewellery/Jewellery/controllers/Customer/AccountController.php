<?php

require_once 'Mage/Customer/controllers/AccountController.php';

class Jewellery_Jewellery_Customer_AccountController extends Mage_Customer_AccountController {


    public function preDispatch()
    {
        // a brute-force protection here would be nice

        Mage_Core_Controller_Front_Action::preDispatch();

        $this->setFlag('', 'no-dispatch', false);

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();

        $pattern = '/^(create|toregisterPost|login|logoutSuccess|firmLogin|forgotpassword|forgotpasswordpost|confirm|confirmation|license|overview|overviewPost|success)/i';
        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    public function successAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    public function overviewAction()
     {
	
         if ($this->_getSession()->isLoggedIn()) {
             $this->_redirect('*/*');
             return;
         }

         $jSession = Mage::getSingleton('jewellery/session');
         $registrationStep = $jSession->getRegistrationStep();

         if ($registrationStep < 3) {
             $this->_redirect('customer/account/license');
             return;
         }

         $jSession->setRegistrationStep(3);

         $this->loadLayout();
         $this->_initLayoutMessages('customer/session');
         $this->renderLayout();
     }


    public function toregisterPostAction()
    {
        $jSession = Mage::getSingleton('jewellery/session');
        $jSession->setRegistrationStep(1);

        $this->_redirectUrl(Mage::helper("customer")->getRegisterUrl());
        return;
    }

    public function createAction()
    {

        $jSession = Mage::getSingleton('jewellery/session');
        $registrationStep = $jSession->getRegistrationStep();

        if (!$registrationStep || $registrationStep < 1) {
            $this->_redirect('customer/account/login');
            return;
        }

        $jSession->setRegistrationStep(1);

        if ($jSession->getCustomerFormData()) {
            $this->_getSession()->setCustomerFormData($jSession->getCustomerFormData());
            //$jSession->setCustomerFormData(null);
        }

        return parent::createAction();
    }

	public function firmloginAction()
	{
		$session = $this->_getSession();
		if ($session->isLoggedIn()) {
			$this->_redirect('*/*/');
			return;
		}

		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
	}

	public function firmloginpostAction()
	{
		$email = $this->getRequest()->getPost('email');
		$customerNumber = $this->getRequest()->getPost('customer_number');
		$zip = \trim($this->getRequest()->getPost('zip'));
		$validateArray = array(
			$this->getRequest()->getPost('firm_name'),
			$this->getRequest()->getPost('customer_number'),
			$this->getRequest()->getPost('zip'),
			$this->getRequest()->getPost('city'),
			$this->getRequest()->getPost('email')
		);

		foreach ($validateArray as $item) {
			if (! trim($item)) {
				$this->_getSession()->addError($this->__('Bitte füllen Sie alle Felder aus.'));
				$this->getResponse()->setRedirect(Mage::getUrl('*/*/firmlogin'));
				return;
			}
		}

		$errorMessage = 'Vielen Dank für Ihre Autorisierung. Wir werden Ihre Anfrage werktags innerhalb von 24 Std. prüfen und Ihnen
		Ihre Zugangsdaten per E-Mail zukommen lassen. <br />
		Selbstverständlich stehen wir Ihnen auch persönlich gerne zur Verfügung! ';

		if ($customerNumber) {
			// if email was entered
			if ($email) {
				if (!Zend_Validate::is($email, 'EmailAddress')) {
					$this->_getSession()->setForgottenEmail($email);
					$this->_getSession()->addError($this->__('Invalid email address.'));
					$this->getResponse()->setRedirect(Mage::getUrl('*/*/firmlogin'));
					return;
				}
			} else {
				$this->_getSession()->addError($this->__('Invalid email address.'));
				$this->getResponse()->setRedirect(Mage::getUrl('*/*/firmlogin'));
				return;
			}

			$customer = Mage::getModel('customer/customer')
				->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
				->loadByCustomerNumber($customerNumber);

			if ($customer->getId()) {
				try {
					$customerAddressId = $customer->getDefaultBilling();
					$addressError = false;
					if ($customerAddressId){
						$address = Mage::getModel('customer/address')->load($customerAddressId);
						$postCode = $address->getData('postcode');
						if (empty($postCode) ||
							$address->getData('postcode') != $zip
						) {
							$addressError = true;
						}
					} else {
						$addressError = true;
					}

					if ($addressError) {                                    
						throw new \Exception($errorMessage);
					}

					$customer->setEmail($email);
					$customer->save();
					$newPassword = $customer->generatePassword();
					$customer->changePassword($newPassword, false);
					$customer->sendPasswordReminderEmail();
					$this->_sendMailFirmRequestToAdmin(array(
						'firm' => $this->getRequest()->getPost('firm_name'),
						'customer_number' => $this->getRequest()->getPost('customer_number'),
						'zip' => $this->getRequest()->getPost('zip'),
						'city' => $this->getRequest()->getPost('city'),
						'customer_email' => $this->getRequest()->getPost('email')
					));
           
					$this->_getSession()->addSuccess('Vielen Dank für Ihre Autorisierung. Ihre neuen Zugangsdaten wurden Ihnen per E-Mail zugeschickt. Bitte
				überprüfen Sie Ihr E-Mail Fach.');

					$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
					return;
				}
				catch (Exception $e){
					$this->_getSession()->addError($e->getMessage());
				}
			} else {
        $this->_sendMailFirmRequestErrorToAdmin(array(
        	'firm' => $this->getRequest()->getPost('firm_name'),
        	'customer_number' => $this->getRequest()->getPost('customer_number'),
        	'zip' => $this->getRequest()->getPost('zip'),
        	'city' => $this->getRequest()->getPost('city'),
        	'customer_email' => $this->getRequest()->getPost('email')
        ));
				$this->_getSession()->addError($this->__($errorMessage));
				$this->_getSession()->setForgottenEmail($email);
			}
		} else {
			$this->_getSession()->addError($this->__('Please enter your customer number.'));
			$this->getResponse()->setRedirect(Mage::getUrl('*/*/firmlogin'));
			return;
		}

		$this->getResponse()->setRedirect(Mage::getUrl('*/*/firmlogin'));
	}

	protected function _sendMailFirmRequestToAdmin($data)
	{
		/* Sender Name */
		$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
		/* Sender Email */
		$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');

		$mail = Mage::getModel('core/email_template');

		$collection =  Mage::getResourceSingleton('core/email_template_collection');
		$template = $collection->getItemByColumnValue('template_code', 'new_firm_password_request');

		if (! $template || ! $template->getData('template_id')) {
			return false;
		}

		$mail->setDesignConfig(array('area' => 'frontend', 'store' => Mage::app()->getStore()->getId()))
			->sendTransactional(
			$template->getData('template_id'),
			'general',
			$senderEmail,
			$senderName,
			$data
		);
	}
	protected function _sendMailFirmRequestErrorToAdmin($data)
	{
		/* Sender Name */
		$senderName = Mage::getStoreConfig('trans_email/ident_general/name');
		/* Sender Email */
		$senderEmail = Mage::getStoreConfig('trans_email/ident_general/email');

		$mail = Mage::getModel('core/email_template');

		$collection =  Mage::getResourceSingleton('core/email_template_collection');
		$template = $collection->getItemByColumnValue('template_code', 'new_firm_password_request_error');

		if (! $template || ! $template->getData('template_id')) {
			return false;
		}

		$mail->setDesignConfig(array('area' => 'frontend', 'store' => Mage::app()->getStore()->getId()))
			->sendTransactional(
			$template->getData('template_id'),
			'general',
			$senderEmail,
			$senderName,
			$data
		);
	}

    public function licensePostAction()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $jSession = Mage::getSingleton('jewellery/session');

        if ($this->getRequest()->isPost()) {
            $errors = array();

             if (!is_null ($this->getRequest()->getPost('license_submit'))) {
                // FILE UPLOAD
                if (isset($_FILES['license-file']['name']) && $_FILES['license-file']['name'] != '') {
                    try {
                        $uploader = new Varien_File_Uploader('license-file');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','pdf'));
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $path = Mage::getBaseDir('media') . DS . 'licenses' . DS;
                        $licenseName = $_FILES['license-file']['name'];
                        $uploader->save($path, $licenseName);

                        $jSession->setCustomerLicenseFilename($licenseName);

                    } catch (Exception $e) {
                        // UPLOAD UNSUCCESSFUL
                        $errors[] = $this->__($e->getMessage());
                    }
                } else {
                    $errors[] = $this->__('License not uploaded 1');
                }
            } elseif (!is_null ($this->getRequest()->getPost('submit'))) {
                if ($this->getRequest()->getPost('license_upload')) {
                    // check if file was uploaded
                    if (!$jSession->getCustomerLicenseFilename()) {
                        $errors[] = $this->__('License not uploaded 2');
                    }

                    $path = Mage::getBaseDir('media') . DS . 'licenses' . DS . $jSession->getCustomerLicenseFilename();
                    if (!file_exists($path)) {
                        $errors[] = $this->__('License not uploaded 3');
                    }

                    $jSession->setCustomerLicenseUploadType('upload');
                } else {
                    $jSession->setCustomerLicenseUploadType('send');
                }
            }

            $validationResult = count($errors) == 0;

            if (true === $validationResult) {
                try {
                     if (!is_null ($this->getRequest()->getPost('submit'))) {
                        $jSession->setRegistrationStep(3);
                        $this->_redirect('*/*/overview');
                    } else {
                        $session->addSuccess($this->__('File uploaded successfully'));
                        $this->_redirect('*/*/license');
                    }
                    return;
                } catch (Exception $e) {

                }
            } else {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if (is_array($errors)) {
                    foreach ($errors as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                } else {
                    $session->addError($this->__('Invalid license data'));
                }
            }

            $this->_redirectError(Mage::getUrl('*/*/license', array('_secure' => true)));
        }
    }

    public function overviewPostAction()
    {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
             $this->_redirect('*/*');
             return;
         }

         $jSession = Mage::getSingleton('jewellery/session');

        try {
            // check license data
            if (!$jSession->getCustomerLicenseUploadType()) {
                $jSession->setRegistrationStep(2);
                $this->_redirect(Mage::helper('jewellery')->getLicenseUrl());
                return;
            }

            $path = '';
            if ($jSession->getCustomerLicenseUploadType() == 'upload') {
                $fileName = $jSession->getCustomerLicenseFilename();
                $path = Mage::getBaseDir('media') . DS . 'licenses' . DS . $jSession->getCustomerLicenseFilename();
                if (!$fileName || !file_exists($path)) {
                    $jSession->setRegistrationStep(2);
                    $this->_redirect(Mage::helper('jewellery')->getLicenseUrl());
                    return;
                }
            }

            $customer = $jSession->getCustomer();

            // check customer
            if (!$customer) {
                $jSession->setRegistrationStep(1);
                $this->_redirect('customer/account/create');
                return;
            }

            $jSession->setRegistrationStep(3);

            $customer = $jSession->getCustomer();

            $customer->setLicensePath($path);

            $customer->save();

            /**
             * Remove license file after the customer is saved
             */
            if ($path) {
                unlink($path);
            }

            $jSession->clear();

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
            } else {
                $this->_welcomeCustomer($customer);
            }


            $session->addSuccess($this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName()));

            $this->getResponse()->setRedirect(Mage::helper('jewellery')->getSuccessUrl());

        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->getResponse()->setRedirect(Mage::helper('jewellery')->getOverviewUrl());
        }

        return $this;
    }


    public function createPostAction()
    {
        $session = $this->_getSession();

        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $session->setEscapeMessages(true); // prevent XSS injection in user input

        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    // customize
                    $customer->setAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }

            try {
                $customerErrors = $customerForm->validateData($customerData);

                // additional validation: validate if entered email is correct
                $confirmEmail = trim($this->getRequest()->getParam('confirm_email'));
                $email = trim($this->getRequest()->getParam('email'));

                // check if no customer with such email has been registered before
                $validateEmailCustomer = Mage::getModel('customer/customer')->loadByEmail($email);
                if ($validateEmailCustomer->getId()) {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This customer email already exists.'),
                        Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS
                    );
                }

                // validate email fields
                if ($email != $confirmEmail) {
                    $emailErrors = array($this->__('Entered emails are not the same'));
                    $errors = array_merge($emailErrors, $errors);
                }

                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    //$customer->setPassword($this->getRequest()->getPost('password'));
                    $tempPassword = $customer->generatePassword(6);
                    $customer->setPassword($tempPassword);
                    $customer->setConfirmation($tempPassword);
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    Mage::getSingleton('jewellery/session')
                        ->setCustomerFormData($this->getRequest()->getPost())
                        ->setCustomer($customer)
                        ->setRegistrationStep(2); // account step completed
                    ;

                    $this->_redirect('*/*/license');
                    return;


                    //$customer->save();

                    //if ($customer->isConfirmationRequired()) {
                    //    $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                    //    $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                    //    $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                    //    return;
                    //} else {
                    //    $session->setCustomerAsLoggedIn($customer);
                    //    $url = $this->_welcomeCustomer($customer);
                    //    $this->_redirectSuccess($url);
                    //    return;
                    //}

                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $session->addError($errorMessage);
                        }
                    } else {
                        $session->addError($this->__('Invalid customer data'));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
    }


    public function licenseAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }

        $jSession = Mage::getSingleton('jewellery/session');
        $registrationStep = $jSession->getRegistrationStep();

        if ($registrationStep < 2) {
            $this->_redirect('customer/account/create');
            return;
        }

        $jSession->setRegistrationStep(2);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
     }




    /**
     * Login post action
     */
    public function loginPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Customer number and password are required.'));
            }
        }

        $jSession = Mage::getSingleton('jewellery/session');

        if (!$jSession->getLoginRefererUrl()) {
            $jSession->setLoginRefererUrl($this->_getRefererUrl());
        }

        $session->setAfterAuthUrl($jSession->getLoginRefererUrl());

        $this->_loginPostRedirect();



        //$this->_redirectReferer(Mage::helper('customer')->getDashboardUrl());
    }

    protected function _loginPostRedirect()
    {
        parent::_loginPostRedirect();

        $session = $this->_getSession();
		
        if ($session->isLoggedIn()) {
            Mage::getSingleton('jewellery/session')->setLoginRefererUrl(false);
        }
    }


    /**
     * Forgot customer password action
     */
    public function forgotPasswordPostAction()
    {
        $email = $this->getRequest()->getPost('email');
        $customerNumber = $this->getRequest()->getPost('customer_number');
        if ($email || $customerNumber) {
            // if email was entered
            if ($email) {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $this->_getSession()->setForgottenEmail($email);
                    $this->_getSession()->addError($this->__('Invalid email address.'));
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
                    return;
                }
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email);
            } else if ($customerNumber) {
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByCustomerNumber($customerNumber);
            }

            if ($customer->getId()) {
                try {
					$email = trim($customer->getEmail());
					if (preg_match('/^\d+@standard-schmuck.de$/', $email)) {
						$this->_getSession()->addError('Ihre Kundennummer ist im Shop noch nicht autorisiert. Bitte füllen Sie das folgende Formular aus um sich zu
autorisieren. <br />
Selbstverständlich stehen wir Ihnen auch persönlich gerne zur Verfügung! ');
						$this->getResponse()->setRedirect(Mage::getUrl('*/*/firmlogin'));
						return;
					}

                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();

                    $this->_getSession()->addSuccess($this->__('A new password has been sent.'));

                    $this->getResponse()->setRedirect(Mage::getUrl('*/*'));
                    return;
                }
                catch (Exception $e){
                    $this->_getSession()->addError($e->getMessage());
                }
            } else {
                $this->_getSession()->addError($this->__('This email address or customer number was not found in our records.'));
                $this->_getSession()->setForgottenEmail($email);
            }
        } else {
            $this->_getSession()->addError($this->__('Please enter your email or customer number.'));
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
            return;
        }

        $this->getResponse()->setRedirect(Mage::getUrl('*/*/forgotpassword'));
    }

}