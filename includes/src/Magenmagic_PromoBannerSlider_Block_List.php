<?php
class Magenmagic_PromoBannerSlider_Block_List  extends  Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{

    protected function _construct()
    {
        parent::_construct();
    }

   public function _toHtml()
   {
       //data
       $category  = (int) $this->getData("category");
       $speed     = (int) $this->getData("speed");
     //  var_dump($speed);
       $direction = (int) $this->getData("direction");
       $auto      = (int) $this->getData("autoscroll");
       $rand      = (int) $this->getData("random");

       if ( $category == 0 ) return "";

       $imageList = Mage::getModel('promobannerslider/bannersimages')->getCurrentCollection( $category, $rand );

       $this->imageList = $imageList;

       if ( ! count($imageList) ) return "";

       $uniqID = rand(10, 1000);
       $this->uniqID     = $uniqID;
       $this->speed      = $speed <= 0 ? 1 : $speed;
       $this->direction  = $direction == 1 ? "endlessloopright" : "endlessloopleft";
       $this->auto       = $auto      == 1 ? "always"           : "";

       return parent::_toHtml();
   }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

}