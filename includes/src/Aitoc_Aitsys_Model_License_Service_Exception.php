<?php

class Aitoc_Aitsys_Model_License_Service_Exception extends Exception
{
    
    const SHOULD_TEST_MODE = 0x1;
    
    const SHOULD_LIVE_MODE = 0x2;
    
    const SHOULD_TEST_MODE_MSG = "Module should be installed in test mode";
    
    const SHOULD_LIVE_MODE_MSG = "Module should be installed in live mode"; 
    
}