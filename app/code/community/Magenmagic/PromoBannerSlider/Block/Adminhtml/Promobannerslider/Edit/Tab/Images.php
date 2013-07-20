<?php

class Magenmagic_PromoBannerSlider_Block_Adminhtml_Promobannerslider_Edit_Tab_Images extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
      {
          parent::__construct();
          $this->setTemplate('magenmagic/promobannerslider/gallery.phtml');

      }

    protected function _beforeToHtml()
      {
      }

    protected function _prepareLayout()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->getUploader()->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload'))
            ->setFileField('image')
            ->setFilters(array(
                'images' => array(
                    'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                    'files' => array('*.gif', '*.jpg','*.jpeg', '*.png')
                )
            ));

        return parent::_prepareLayout();
    }

    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }

    public function getUploader()
    {
        return $this->getChild('uploader');
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

}