<?php

class Jewellery_Sales_Model_Observer {

    /**
     * [checkQuoteItemQty description]
     * @param  Varien_Object $observer
     * @return Jewellery_Sales_Model_Observer
     *
     */
    public function checkQuoteItemQty($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();

        if (!$quoteItem->getLocalProcessed()) {
            $quoteItem->setLocalProcessed(true);
            if ($quoteItem->getHasError()) {
                $quoteItem->setData('qty', 0);

                $quoteMessages = $quoteItem->getQuote()->getMessages();
                if (isset($quoteMessages['qty'])) {
                    unset($quoteMessages['qty']);
                    $quoteItem->getQuote()->setData('messages', $quoteMessages);
                }

            }
        }

        return $this;
    }
}