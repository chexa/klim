<?php
/**
 * Product:     Advanced Permissions
 * Package:     Aitoc_Aitpermissions_2.2.4_2.0.3_90233
 * Purchase ID: xFF945pgbyDre0fKeBvM8FAv6R2a0C65GbaW0cwEpD
 * Generated:   2011-07-05 15:42:56
 * File path:   app/code/local/Aitoc/Aitpermissions/Block/Rewrite/AdminPollEdit.data.php
 * Copyright:   (c) 2011 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'Aitoc_Aitpermissions')){ ghaZqIwDpTUDTsZB('1e00fc3848b7abf0bbc391ab66b49076'); ?><?php
class Aitoc_Aitpermissions_Block_Rewrite_AdminPollEdit extends Mage_Adminhtml_Block_Poll_Edit
{
    protected function _preparelayout()
    {
        parent::_prepareLayout();
        if (Mage::helper('aitpermissions')->isPermissionsEnabled())
        {
            $allowDelete = true;
            
            $poll = Mage::registry('poll_data');

            // checking if we have permissions to edit this poll
            $AllowedStoreviews = Mage::helper('aitpermissions')->getAllowedStoreviews();
            if ($AllowedStoreviews && !array_intersect($poll->getStoreIds(), $AllowedStoreviews) && $poll->getId())
            {
                Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/*'));
            }

            if ($poll->getStoreIds() && is_array($poll->getStoreIds()))
            {
                foreach ($poll->getStoreIds() as $pollStoreId)
                {
                    if (!in_array($pollStoreId, $AllowedStoreviews))
                    {
                        $allowDelete = false;
                        break 1;
                    }
                }
            }

            if (!$allowDelete)
            {
                $this->_removeButton('delete');
            }
        }
        return $this;
    }
} } 