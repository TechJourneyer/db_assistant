<?php

define("DB_HOST","");
define("DB_USER","");
define("DB_PASS","");
define("DB_NAME","");

define("CHATGPT_KEY","");

define("MEDIA_DB_HOST","");
define("MEDIA_DB_USER","");
define("MEDIA_DB_PASS","");
define("MEDIA_DB_NAME","");
define("SITE_URL","http://localhost/dba/");

if (!defined("ROOTDIR")) {
    define("ROOTDIR", $_SERVER["DOCUMENT_ROOT"] . "/dba/");
}

require_once ROOTDIR . 'functions.php';
