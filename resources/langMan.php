<?php

require_once __DIR__.'/../config.php';

if(isset($_GET['Lang'])) 
{
    setcookie('lang', $_GET['Lang'], 0);
    include_once __DIR__.'/languages/'.$_GET['Lang'].'.php';
} elseif(isset($_COOKIE['lang'])) {
    include_once __DIR__.'/languages/'.$_COOKIE['lang'].'.php';
} else {
    include_once __DIR__.'/languages/'.Config::DEFAULT_LANG.'.php';
}
?>
