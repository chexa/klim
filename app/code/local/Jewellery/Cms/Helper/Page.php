<?php

if (Mage::getConfig()->getNode('modules/Clever_Cms')) {
    class Jewellery_Cms_Helper_Page_Abstract extends Clever_Cms_Helper_Page {}
} else {
    class Jewellery_Cms_Helper_Page_Abstract extends Mage_Cms_Helper_Page {}
}

class Jewellery_Cms_Helper_Page extends Jewellery_Cms_Helper_Page_Abstract
{
    const XML_PATH_HOME_PAGE_AUTH            = 'web/default/cms_home_page_auth';
}
