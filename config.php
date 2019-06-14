<?php
/*
 * Configuration Settings
 */
define("DELIMITER", "/~/");
date_default_timezone_set('Europe/Athens');

class Config {
    
    // Builder URL
    const BUILDER_URL                   = "http://builder.testware.eu";
//    const BUILDER_URL                   = "http://builder.wgi.certapis.com";
    // Builder Service WSDL URI
    const BUILDER_SERVICE_WSDL          = "/BuilderService.svc?wsdl";
    // Builder Service Location
    const BUILDER_LOCATION              = "/BuilderService.svc";
    
    const BUILDER_GROUPID               = 1;
    
    static $BUILDER_CLIENT_OPTIONS        = array(
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_DISK
    );
    
    const MANAGER_URL                   = "http://localhost/testware/index.php/manager/";
//    const MANAGER_URL                   = "http://manager.wgi.certapis.com";
//      const MANAGER_URL                   = 'http://localhost/bebras/index.php/manager/';
//    const MANAGER_SERVICE_WSDL          = "/Clients.asmx?WSDL";
    const MANAGER_SERVICE_WSDL          = "wsdl";

//    const MANAGER_LOCATION              = "/Clients.asmx";
    const MANAGER_LOCATION              = "wsdl";
    
    static $MANAGER_CLIENT_OPTIONS        = array(
        'trace' => true,
        'exceptions' => true,
        'cache_wsdl' => WSDL_CACHE_DISK
    );
    
    const MANAGER_USERNAME              = "system";
    const MANAGER_PASSWORD              = "123456";
    
    // Application Default Language
    const DEFAULT_LANG                  = 'en';
    
    // Logo Settings
    const CUSTOM_LOGO                   = 'logo_1.jpg';
//    const CUSTOM_LOGO_TWO               = 'logo_2.jpg';
    const CUSTOM_LOGO_TWO               = '';
    
    // Application Resources
    const COPYRIGHTS                    = 'Copyrights (C) CERTAPIS Limited 2013. All rights reserved.';
    const VERSION                       = '1.03';
    
    // Application Languages
    static $LANGS                       = array(
        // English
        array('Filename'=>'en','Name'=>'English'),
        // Greek
        array('Filename'=>'el','Name'=>'Ελληνικά')
    );
    
    // Preview Settings
    const PREVIEW_PASSWORD              = '123456';
    
    // Testware Mode
    const TestwareMode                  = TRUE;
    
    // TESTWARE YII APP INDEX
    const TESTWARE_RETURN_PATH          = "/bebras/index.php/site/complete";
    
    // Crypt Key
    const CRYPT_KEY                     = 'asdbnw2erfsn$@#FSD$@#123';
    const CRYPT_IV                      = 'ASDH2-#D';
}

?>
