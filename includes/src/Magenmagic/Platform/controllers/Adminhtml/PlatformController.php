<?php

class Magenmagic_Platform_Adminhtml_PlatformController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('platform/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   

    public function sendEmailAction ()
    {
        $request   = $this->getRequest();
        $name      = $request->getParam("name");
        $email     = $request->getParam("email");
        $telephone = $request->getParam("telephone");
        $comment   = $request->getParam("comment");

        try
        {
            if ( ! $this->_validateEmailData() )
            {
                throw new Exception("Invalid Input Data");
            }

            $subject   = "Request From Client Site";
            $bodyHtml  = "Host: ". $_SERVER['HTTP_HOST'];
            $bodyHtml .= "Name: ".$name."<br />";
            $bodyHtml .= "Email: ".$email."<br />";
            $bodyHtml .= "Phone Number: ".$telephone."<br />";
            $bodyHtml .= "<br />Comment: ".$comment."<br />";

            $mail = new Zend_Mail();
            $mail->setSubject($subject);
            $mail->setFrom($email, $name);
            $mail->addTo("support@magenmagic.com", "Magen Magic Support");
            $mail->setBodyHtml($bodyHtml);
            $mail->send();

            echo "Thank you for feedback! Your message was sucessfully sent. We will provide answer soon.";
            die;

        } catch (Exception $e)
        {
            echo $e->getMessage();
        }

    }

    protected function _validateEmailData()
    {
        $request   = $this->getRequest();
        $name      = $request->getParam("name");
        $email     = $request->getParam("email");
        $comment   = $request->getParam("comment");

        if ( !$name || strlen(trim($name)) == 0 ) return false;
        if ( !$email || (int) preg_match("/^(.)+@(.)+\.(\D)+$/i", trim($email)) == 0 ) return false;
        if ( !$comment || strlen(trim($comment)) == 0 ) return false;
        return true;
    }

}