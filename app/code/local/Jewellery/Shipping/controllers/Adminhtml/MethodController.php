<?php
class Jewellery_Shipping_Adminhtml_MethodController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction(){
        $this->loadLayout(); 
        $this->_setActiveMenu('sales/jeshipping');
        $this->_addBreadcrumb($this->__('Deutsche Post Shipping Methods'), $this->__('Deutsche Post Shipping Methods')); 
        $this->_addContent($this->getLayout()->createBlock('jeshipping/adminhtml_method'));         
         $this->renderLayout();
    }
    
    public function newAction() {
        $this->editAction();
    }
    
    public function editAction() {
        $id     = (int) $this->getRequest()->getParam('id');
        
        $model  = Mage::getModel('jeshipping/method')->load($id);

        if ($id && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('jeshipping')->__('Shipping method does not exist'));
            $this->_redirect('*/*/');
        }
        
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('jeshipping_method', $model);

        $this->loadLayout();
        $this->_setActiveMenu('sales/jeshipping');
        $this->_addContent($this->getLayout()->createBlock('jeshipping/adminhtml_method_edit'));
        $this->renderLayout();
    }

    public function saveAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('jeshipping/method');
        $data = $this->getRequest()->getPost();
        if ($data) {
            $model->setData($data)->setId($id);
            try {
                $model->save();
                    
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('jeshipping')->__('Shipping method has been successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                $this->_redirect('*/*/');
                return;
                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('jeshipping')->__('Unable to find Shipping method to save'));
        $this->_redirect('*/*/');
    } 
    
        
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        if (!$id) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('jeshipping')->__('Unable to find a Shipping method to delete'));
            $this->_redirect('*/*/');
            return;
        }
        
        try {
            $method = Mage::getModel('jeshipping/method')->load($id);
            if (!$method->getId()) {
                throw new Exception(Mage::helper('jeshipping')->__('Unable to delete Shipping method'));
            }

            $method->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('jeshipping')->__('Event Type has been successfully deleted'));
            $this->_redirect('*/*/');
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }
        
    }
}
