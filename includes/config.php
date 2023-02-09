<?php

/**
 * ======================= MOST IMPORTANT CONFIGURATION =======================
 */

/*
    Database Configuration
*/
define('DB_HOST', 'localhost'); // Your mySQL Host (usually Localhost)
define('DB_USER', 'root'); // Your mySQL Databse username
define('DB_PASS', ''); // Your mySQL Databse Password
define('DB_NAME', 'prueba'); // The database where you have dumped the included sql file


/**
 * Enable/disable firewall for protect video/stream pages
 * default : false
 * val : true/false
 */
define('FIREWALL', false);

/**
 * If you enbaled firewall, add your allowed domain list here
 * example : ['mydomain.com','movies.com']
 */
$allowed_domains = ['localhost'];



/**
 * Change HLS and main script cominication API key here, it is important
 * If you changed it here, you need change it in both server's config
 * default : 12345
 * val : any string
 */
define('HLS_API_SECRET_KEY', 'ddWA2322!35e');


/**
 * Application activation key
 * val : any string
 */
define('APP_ACTIVATION_KEY', 'RAKEY');

/**
 * If you install script on sub folder, insert that folder name here
 * default : ''
 * example : mydomain.com/gdplyr
 * define('PROOT', '/gdplyr');
 */
define('PROOT', '');

/**
 * Upload chunks to drive
 * default: 25MB
 */
define('DRIVE_UPLOAD_CHUNK', 25 * 1024 * 1024);




/**
 * ======================= COMMON CONFIGURATION =======================
 */


/**
 * Enable/disable google drive delete action
 * default : false
 * val : true/false
 */
define('MY_DRIVE_FILE_DELETE_ACTION', false);

/**
 * Disable main server from stream (after we will use only loadbalncer servers)
 * default : true
 * val : true/false
 */
define('STREAM_WITH_MAIN_SERVER', true);

/**
 * Maximum files per page in mydrive page
 * default : 1000
 * val : integer (less than 1000)
 */
define('MAX_GDRIVE_FILES_PER_PAGE', 1000);

/**
 * Application screct key
 * val : any string
 */
define('APP_SCRECT_KEY', 'RSCRECT');
// SCRECT_KEY

/**
 * Play HLS files with only plyr.io
 * default : true
 * val : true/false
 */
define('HLS_WITH_PLYR', true);


/**
 * Enable/disable direct stream
 * default : false
 * val : true/false
 */
define('DIRECT_STREAM', true);

/**
 * Application name
 * default : false
 * val : true/false
 */
define('APP_NAME', 'GDplyr');

/**
 * Application debug mode
 * default : false
 * val : true/false
 */
define('DEBUG', false);



/**
 * Application root directory
 */
define('ROOT', dirname(__FILE__, 2));

/**
 * Define Template
 */
define('TEMPLATE', ROOT . '/theme');

/**
 * Define subtitles upload directory
 * default : subtitles
 */
define('SUB_UPLOAD_DIR', 'subtitles');

/**
 * Define link's preview images upload directory
 * default : banners
 */
define('BANNER_UPLOAD_DIR', 'banners');

/**
 * Is allowed duplicate links
 * default : false
 * val : true/ false
 */
define('IS_DUPLICATE', false);

/**
 * Stream page debug
 * default : false
 * val : true/ false
 */
define('STREAM_DEBUG', false);

/**
 * Upload max file size
 * default : 5MB
 * val : bytes
 */
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);


/**
 * Changer HLS master file name (if you change it here , also need change from hls script config)
 * default : xfgdYshjhYhj=!sdsHsyG
 * val : any string
 */
define('HLS_MASTER_FILE', 'xfgdYshjhYhj=!sdsHsyG');

/*
   This details not important for you !
*/
define('GDRIVE_API', 'AIzaSyD43F1N3Wvj2vfqpgyImQgv81eQylP-bJk');
define('GDRIVE_IDENTIFY', '__001');
define('GPHOTO_IDENTIFY', '__002');
define('ONEDRIVE_IDENTIFY', '__003');
define('YANDEX_IDENTIFY', '__004');
define('DIRECT_IDENTIFY', '__005');
define('OKRU_IDENTIFY', '__006');
define('ALLOWED_DOMAINS', $allowed_domains);
define('OKRU_EXPIRED', 10 * 60);
define('YANDEX_EXPIRED', 10 * 60);
define('TMP_DIR', ROOT . '/data/tmp/');

/**
 * Change application encryption code
 * default : #$wel
 * val : any string
 */
define('_SEC_LOCK', '#$wel');

$config = [];

function dnd($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die();
}


if (strpos($_SERVER['REQUEST_URI'], '/stream/') === false) {
    include (ROOT . '/includes/core.php');
}

