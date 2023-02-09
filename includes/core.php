<?php


define('APP', true);
define('VERSION', '3.0');

//start session
if (!isset($_SESSION)) {
    session_start();
}

// Error Reporting
if (!DEBUG) {
    error_reporting(0);
} else {
    ini_set('display_error', 1);
    ini_set('error_reporting', E_ALL);
    error_reporting(-1);
}

// Connect to Database
include (ROOT . '/includes/Database.class.php');
$db = new Database($config);

// Start Application
include (ROOT . '/includes/App.class.php');
$app = new App();

// Get theme functions file
if (file_exists(ROOT . '/theme/functions.php')) {
    include (TEMPLATE . '/functions.php');
}

if (file_exists(ROOT . '/vendor/autoload.php')) {
    require ROOT . '/vendor/autoload.php';
}

if(!file_exists(ROOT.'/data/tmp')){
    @mkdir(ROOT.'/data/tmp',0755);
}

//Application Helpers
include (ROOT . '/includes/Helper.class.php');
include (ROOT . '/includes/FH.class.php');
include (ROOT . '/includes/GAuth.class.php');
include (ROOT . '/includes/MyDrive.class.php');
include (ROOT . '/includes/MyDrive2.class.php');
include (ROOT . '/includes/Proxy.class.php');
include (ROOT . '/includes/Cache.class.php');
include (ROOT . '/includes/Stream.class.php');
include (ROOT . '/includes/Upload.class.php');
include (ROOT . '/includes/Link.class.php');
include (ROOT . '/includes/BackupDrives.class.php');
include (ROOT . '/includes/HlsLinks.class.php');
include (ROOT . '/includes/MyHLS.class.php');
include (ROOT . '/includes/Server.class.php');
include (ROOT . '/includes/User.class.php');

include (ROOT . '/includes/library/JSPacker.php');

include (ROOT . '/includes/sources/GDrive.class.php');
include (ROOT . '/includes/sources/GPhoto.class.php');
include (ROOT . '/includes/sources/OneDrive.class.php');
include (ROOT . '/includes/sources/Yandex.class.php');
include (ROOT . '/includes/sources/OkRu.class.php');
include (ROOT . '/includes/Video.class.php');


$config = Database::getConfig();
FH::setConfig($config);


//Set timezone
date_default_timezone_set(FH::getConfig('timezone'));

function getThemeURI() {
    return PROOT . '/theme';
}

function getPlayerURI($p) {
    $p = ROOT . "/players/{$p}";
    if (file_exists($p)) {
        return $p;
    }
}
