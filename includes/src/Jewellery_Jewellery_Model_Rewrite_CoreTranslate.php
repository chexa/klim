<?php

class Jewellery_Jewellery_Model_Rewrite_CoreTranslate extends Mage_Core_Model_Translate
{

    protected function _getTranslatedString($text, $code)
    {
        $translated = '';
//        if (array_key_exists($code, $this->getData())) {
//            $translated = $this->_data[$code];
//        }
//        elseif (array_key_exists($text, $this->getData())) {
//            $translated = $this->_data[$text];
//        }
        if (array_key_exists($text, $this->getData())) {
            $translated = $this->_data[$text];
        }
        elseif (array_key_exists($code, $this->getData())) {
            $translated = $this->_data[$code];
        }
        else {
            $translated = $text;
        }
        return $translated;
    }
}
