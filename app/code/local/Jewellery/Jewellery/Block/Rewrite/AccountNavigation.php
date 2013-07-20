<?php

class Jewellery_Jewellery_Block_Rewrite_AccountNavigation extends Mage_Customer_Block_Account_Navigation
{
    public function removeLink($name)
    {
        unset ($this->_links[$name]);
        return $this;
    }
}
