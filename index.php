<?php

//phpinfo();die;
use Front\FrontController;

/**Mazeo autoloader**/
require_once('app/autoload.php');

/**Composer autoloader**/
require_once('vendor/autoload.php');

/***Launching mazeo autoloader**/
new Autoloader();

/**
 * The two line belows it's just for the current app but can used globaly
 * header avoid ERR_CACHE_MISS and come bakc to form charged with data
 * so the browser will not ask we to confirm form
 */
// header('Cache-Control: no cache');
// session_cache_limiter('private_no_expire'); // works

/**Do not touch this session_start position*/
session_start();

/**Running the current app**/
FrontController::run();
