<?php

// A fix for a lot of unfortunate PHP4 code. Gets auto-prepended in php.ini
// https://stackoverflow.com/questions/12978293/reintroduce-http-post-vars-in-php-5-3
$HTTP_POST_VARS     = &$_POST;
$HTTP_GET_VARS      = &$_GET;
$HTTP_COOKIE_VARS   = &$_COOKIE;
$HTTP_SERVER_VARS   = &$_SERVER;
$HTTP_POST_FILES    = &$_FILES;
$HTTP_SESSION_VARS  = &$_SESSION;
$HTTP_ENV_VARS      = &$_ENV;
