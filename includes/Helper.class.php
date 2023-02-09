<?php
/**
 * ====================================================================================
 *                           Google Drive Proxy Player (c) CodySeller
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at codyseller.com. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from https://codyseller.com/license.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author CodySeller (http://codyseller.com)
 * @link http://codyseller.com
 * @license http://codyseller.com/license
 */

class Helper {

    public function __construct() {

    }

    /**
     * Clean data
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public static function clean($data) {
        // Fix &entity\n;
        $data = str_replace(array(
            '&amp;',
            '&lt;',
            '&gt;'
        ) , array(
            '&amp;amp;',
            '&amp;lt;',
            '&amp;gt;'
        ) , $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);
        // we are done...
        return trim($data);
    }

    /**
     * Get user agent
     * @author CodySeller <https://codyseller.com>
     * @since 1.4
     */
    public static function getUserAgent() {
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36';
        return $ua;
    }

    /**
     * URL Validate
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public static function isUrl($url) {
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url) && filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }
        return false;
    }

    /**
     * Check url status
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public static function isI($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
        // curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        return $info["http_code"];
    }
    public static function randomNumber($length) {
        $result = '';

        for ($i = 0;$i < $length;$i++) {
            $result .= mt_rand(0, 9);
        }

        return $result;
    }
    /**
     * Encrypt
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public static function e($str) {
        $enc = openssl_encrypt($str, "AES-128-ECB", _SEC_LOCK);
        return base64_encode($enc);
    }

    /**
     * Decrypt
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public static function d($str) {
        $dec = base64_decode($str);
        return openssl_decrypt($dec, "AES-128-ECB", _SEC_LOCK);
    }

    /**
     *JSON validater
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function o(){
        return self::getDomain(0);
    }

    /**
     * Get time now
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public static function tnow() {
        $dt = new DateTime("now");
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Get video infomation
     * @author CodySeller <https://codyseller.com>
     * @since 1.5
     */
    public static function getVInfo($url, $key) {
        $headers = [];

        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Pragma: no-cache';

        session_write_close();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
        curl_setopt($ch, CURLOPT_COOKIEFILE, ROOT . '/data/cookiz/gdrive~' . $key . '.txt');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_exec($ch);
        $fsize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $ftype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return ['fsize' => $fsize, 'ftype' => $ftype];

    }

    public static function a(){
        if(defined(base64_decode(self::x()))){
            return constant(base64_decode(self::x()));
        }
        return 'xxx';
    }

    /**
     * Check POST request
     * @author CodySeller <https://codyseller.com>
     * @since 2.1
     */
    public static function isPost() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Check GET request
     * @author CodySeller <https://codyseller.com>
     * @since 2.1
     */
    public static function isGet() {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    public static function x(){
        return 'QVBQX0FDVElWQVRJT05fS0VZ';
    }

    /**
     * Get Requested data
     * @author CodySeller <https://codyseller.com>
     * @since 2.2
     */
    public static function getReqData($req, $m = '') {
        $resp = '';

        if ((self::isPost() && $m != 'GET') || $m == 'POST') {
            if (isset($_POST[$req])) {
                $resp = $_POST[$req];
            }
        }
        else if (self::isGet() || $m == 'GET') {
            if (isset($_GET[$req])) {
                $resp = $_GET[$req];
            }
        }

        return !is_array($resp) ? self::clean($resp) : $resp;
    }

    public static function j(){
        return ROOT.'/includes/helpers/'.base64_encode(self::a()).'.log';
    }

    public static function isValidResp(){
        return file_exists(self::j()) ? true : false;
    }

    /**
     * Format unites
     * @since 2.1
     */
    public static function formatSizeUnits($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        }
        else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Host validater
     * @since 2.2
     */
    public static function isValidHost($host) {
        $hosts = [GDRIVE_IDENTIFY => 'gdrive', YANDEX_IDENTIFY => 'yandex', GPHOTO_IDENTIFY => 'gphoto', ONEDRIVE_IDENTIFY => 'onedrive', DIRECT_IDENTIFY => 'direct', OKRU_IDENTIFY => 'okru'];

        if (array_key_exists($host, $hosts)) {
            return true;
        }
        return false;

    }

    public static function getHostUniqId($htype) {
        $e = '';
        switch (strtolower($htype)) {
            case 'gdrive':
                $e = GDRIVE_IDENTIFY;
            break;
            case 'yandex':
                $e = YANDEX_IDENTIFY;
            break;
            case 'gphoto':
                $e = GPHOTO_IDENTIFY;
            break;
            case 'onedrive':
                $e = ONEDRIVE_IDENTIFY;
            break;
            case 'okru':
                $e = OKRU_IDENTIFY;
            break;
            case 'direct':
                $e = DIRECT_IDENTIFY;
            break;

        }
        return $e;
    }

    public static function getUptoboxURI($id = '') {
        return 'https://uptobox.com/' . $id;
    }
    public static function getFembedURI($id = '') {
        return 'https://femax20.com/f/' . $id;
    }
    public static function getDropapkURI($id = '') {
        return 'https://dropapk.to/' . $id;
    }

    /**
     * Validate gdrive url
     * @since 1.3
     */
    public static function isDrive($url) {
        if (strpos($url, 'drive.google.com/file/d/') !== false) {
            $gId = self::getDriveId($url);
        }
        return (!empty($gId)) ? true : false;
    }

    /**
     * Get gdrive id
     * @since 1.3
     */
    public static function getDriveId($url) {
        $path = explode('/', parse_url($url) ['path']);
        return (isset($path[3]) && !empty($path[3])) ? $path[3] : '';
    }

    /**
     * Validate GPhoto
     * @since 1.3
     */
    public static function isPhoto($url) {
        if (strpos($url, 'photos.app.goo.gl') !== false) {
            return true;
        }
        return false;
    }

    /**
     * Validate OneDrive
     * @since 1.5
     */
    public static function isOneDrive($url) {
        if (strpos($url, '1drv.ms') !== false || strpos($url, 'my.sharepoint.com') !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Validate Yandex
     * @since 2.2
     */
    public static function isYandex($url) {
        if (strpos($url, 'https://yadi.sk/i/') !== false || strpos($url, 'https://disk.yandex.ru/i/') !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function isOkRu($url) {
        if (strpos($url, 'https://ok.ru/') !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Get random string
     * @since 1.5
     */
    public static function random($length = 15, $f=0) {
        if($f == 0){
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0;$i < $length;$i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1) ];
            }
            if($f == 0) return $randomString;
        }

        
        return 'i';
        
    }

    /**
     * Get link type
     * @since 2.0
     */
    public static function getLinkType($url) {
        if (self::isDrive($url)) {
            return 'GDrive';
        }
        elseif (self::isPhoto($url)) {
            return 'GPhoto';
        }
        elseif (self::isOneDrive($url)) {
            return 'OneDrive';
        }
        elseif (self::isYandex($url)) {
            return 'Yandex';
        }
        elseif (self::isOkRu($url)) {
            return 'OkRu';
        }
        else {
            return 'Direct';
        }
    }

    public static function fixSlug(){
        @file_put_contents(self::m(), 's');
    }

    /**
     * Get icon url
     * @since 2.2
     */
    public static function getIcon($n, $e = 'png') {
        return getThemeURI() . '/static/icons/' . $n . '.' . $e;
    }

    public static function cs(){
        return file_exists(self::m()) ? true : false;
    } 

    /**
     * Get banner url
     * @since 2.2
     */
    public static function getBanner($n) {
        return PROOT . '/uploads/' . BANNER_UPLOAD_DIR . '/' . $n;
    }

    /**
     * Get sub file
     * @since 2.2
     */
    public static function getSubD($a) {
        return PROOT . '/uploads/' . SUB_UPLOAD_DIR . '/' . $a;
    }

    /**
     * Format date time
     * @since 2.2
     */
    public static function formatDT($dt, $r = true) {
        if ($r) {
            return date("F jS, Y - h:i A", strtotime($dt));
        }
        else {
            return date("F jS, Y", strtotime($dt));
        }

    }

    public static function m(){
        return ROOT.'/theme/assets/libs/jqvmap/dist/map.txt';
    }

    /**
     * Redirect
     * @since 1.3
     */
    public static function redirect($url = '', $fullurl = false, $message = array() , $header = "") {

        if ($url == 'self') {
            $url = $_SERVER['REQUEST_URI'];
            $fullurl = true;
        }

        if (!empty($message)) {
            $_SESSION["msg"] = self::clean("{$message[0]}::{$message[1]}", 2);
        }
        switch ($header) {
            case '301':
                header('HTTP/1.1 301 Moved Permanently');
            break;
            case '404':
                header('HTTP/1.1 404 Not Found');
            break;
            case '503':
                header('HTTP/1.1 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 60');
            break;
        }
        if ($fullurl) {
            header("Location: $url");
            exit;
        }
        header("Location: " . PROOT . "/$url");
        exit;
    }

    /**
     * Get gdrive qulities
     * @since 2.0
     */
    public static function getQulities($data) {
        $q = [];
        if (!empty($data) && self::isJson($data)) {
            $data = json_decode($data, true);
            $q = array_keys($data['sources']);
        }

        return $q;
    }

    public static function getDisabledQualities() {
        $disabledQualities = FH::getConfig('disabledQualities');
        if (!empty($disabledQualities) && self::isJson($disabledQualities)) {

            return json_decode($disabledQualities, true);
        }
        else {
            return [];
        }
    }

    public static function getCusteomSlugs(){
        return 'i';
    }

    /**
     * Get gdrive data
     * @since 2.0
     */
    public static function getDriveData($file) {
        $qulities = self::getQulities($file['data']);
        $disabledQualities = self::getDisabledQualities();

        $slug = $file['slug'];
        $links = [];
        foreach ($qulities as $q) {
            if (!in_array($q, $disabledQualities)) {
                $f = PROOT . "/stream/{$q}/{$slug}/" . GDRIVE_IDENTIFY;
                $links[] = ['file' => $f, 'q' => $q];
            }

        }
        return $links;
    }

    public static function getOkRuData($data) {
        $links = [];
        if (is_array($data)) {
            foreach ($data as $dk => $dv) {
                $f = PROOT . '/stream/' . $dk . '/' . base64_encode(Helper::e(urlencode($dv['file']))) . '/' . OKRU_IDENTIFY . '?t=' . time();
                $links[] = ['file' => $f, 'q' => $dk];
            }
        }
        return $links;
    }

    /**
     * Clean subtitles data
     * @since 2.0
     */
    public static function cleanSub($subs = '') {

        if (!empty($subs)) {
            $subs = json_decode('[' . $subs . ']', true);

            if ($subs !== null) {
                return $subs;
            }
        }
        return '';
    }

    /**
     * Format player data
     * @since 2.0
     */
    public static function formatPlayerData($data, $isY = false) {
        $players = ['jw', 'plyr', 'videojs'];
        $sources = $subs = [];
        if (!in_array($data['player'], $players)) $data['player'] = 'jw';

        switch ($data['player']) {
            case 'jw':

                //sources
                foreach ($data['sources'] as $s) {

                    $sources[] = "{'label':'" . $s['q'] . "p','type':'video\/mp4','file':'" . $s['file'] . "'}";
                }

                // subtitles
                if (!empty($data['subs'])) {
                    foreach ($data['subs'] as $sub) {
                        $subs[] = '{"kind": "captions","file": "' . self::getSubD($sub['file']) . '",  "label": "' . $sub['label'] . '"  }';
                    }
                    $data['subs'] = '[' . implode(',', $subs) . ']';
                }
                else {
                    $data['subs'] = '[]';
                }

            break;

            case 'plyr':

                //sources
                if (!$isY) {
                    foreach ($data['sources'] as $s) {
                        $sources[] = "{ src: '" . $s['file'] . "',type: 'video/mp4', size: " . $s['q'] . " }";
                    }
                }
                else {
                    $data['sources'] = $data['sources']['file'];
                }

                // subtitles
                if (!empty($data['subs'])) {
                    if (!$isY) {
                        foreach ($data['subs'] as $k => $sub) {
                            $d = $k == 0 ? true : false;
                            $subs[] = "{'kind' : 'captions','label' : '" . $sub['label'] . "', 'src' : '" . self::getSubD($sub['file']) . "','default' : '" . $d . "'}";

                        }
                        $data['subs'] = '[' . implode(',', $subs) . ']';
                    }
                    else {
                        foreach ($data['subs'] as $k => $sub) {
                            $d = $k == 0 ? 'default' : '';

                            $subs[] = ' <track
                                            kind="captions"
                                            label="' . $sub['label'] . '"
                                            srclang="en"
                                            src="' . self::getSubD($sub['file']) . '"
                                            ' . $d . '
                                        />';

                        }
                        $data['subs'] = implode(' ', $subs);
                    }

                }
                else {
                    $data['subs'] = '[]';
                }

            break;

        }

        if (!$isY) $data['sources'] = '[' . implode(',', $sources) . ']';

        return $data;

    }

    /**
     * Check gdrive source
     * @since 2.0
     */
    public static function checkD($url, $id) {
        usleep(rand(900000, 1500000));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, ROOT . '/data/cookiz/gdrive~' . $id . '.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $statusCode != 403 ? true : false;

    }

    /**
     * Check gdrive status
     * @since 2.0
     */
    public static function isHit($data, $id) {
        if (self::isJson($data)) {
            $data = json_decode($data, true);

            $source = reset($data['sources']);
            $url = $source['file'];

            return self::checkD($url, $id);

        }
        return false;
    }

    /**
     * curl
     * @since 1.5
     */
    public static function curl($url, $headers = [], $type = 'get', $data = [], $proxy = false) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, self::getUserAgent());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($type));
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($proxy) {
            $p = Proxy::getOne();
            if (!empty($p)) {
                curl_setopt($ch, CURLOPT_PROXY, $p);
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, FH::getConfig('proxyUser') . ':' . FH::getConfig('proxyPass'));
                if (Proxy::isSocks($p)) {
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                }
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_CAINFO, NULL);
        curl_setopt($ch, CURLOPT_CAPATH, NULL);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        return $result;
    }

    public static function getX(){
        $t = !empty(PROOT) ? PROOT : 1;
        $data = [
            'domain' => self::getHost(),
            'acKey' => APP_ACTIVATION_KEY,
            't' => $t
        ];
        
       return 'http://workplace.codyseller.com/verify_key?'. http_build_query($data);
    }

    /**
     * Get sub files
     * @since 2.0
     */
    public static function addSD($s, $a) {
        foreach ($s as $k => $v) {
            $s[$k]['file'] = $a . str_replace(PROOT, '', $v['file']);
        }
        return $s;
    }

    /**
     * Get servers list
     * @since 2.2
     */
    public static function getServerList($servers = []) {
        $html = $ac = '';
        $sId = self::getReqData('sid');
        $path = self::getPath();

        if (empty($sId)) $ac = 'active';

        // $html .= '<li> <a href="' . $path . '" class="' . $ac . '">Main Server</a> </li>';
        if (!empty($servers)) {
            foreach ($servers as $k => $v) {
                $ac = '';
                if (!empty($sId) && $sId == $v['id']) {
                    $ac = 'active';
                }
                else {
                    if (empty($sId) && $k == 0) {
                        $ac = 'active';
                    }
                }
                $html .= '<li> <a href="' . $path . '?sid=' . $v['id'] . '&t=' . $v['type'] . '" class="' . $ac . '">' . $v['name'] . '</a> </li>';
            }
        }
        return $html;
    }

    /**
     * Get host
     * @since 2.0
     */
    public static function getHost() {
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        }
        else {
            $host = $_SERVER['SERVER_NAME'];
        }
        return trim($host);
    }

    /**
     * Get embed code
     * @since 2.0
     */
    public static function getEmbedCode($url) {
        return '<iframe src="' . $url . '" frameborder="0" allowFullScreen="true" width="640" height="320"></iframe>';
    }

    /**
     * Get domain
     * @since 2.0
     */
    public static function getDomain($i = 1) {
        if($i != 0){
            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            return $protocol . self::getHost();
        }
        return self::h();

    }

    /**
     * Get path
     * @since 2.2
     */
    public static function getPath() {
        $a = parse_url($_SERVER['REQUEST_URI']);
        return $a['path'];
    }

    /**
     * Get path
     * @since 2.2
     */
    public static function getFullUrl($reset) {
        $a = self::getDomain() . PROOT . '/' . $reset;
        return $a;
    }

    /**
     * Get player link
     * @since 2.0
     */
    public static function getPlyrLink($id = '') {
        return self::getDomain() . PROOT . '/' . FH::getConfig('playerSlug') . '/' . $id;
    }

    public static function getDownloadLink($id = '') {
        return self::getDomain() . PROOT . '/' . FH::getConfig('downloadSlug') . '/' . $id;
    }
    /**
     * Get stream link
     * @since 2.0
     */
    public static function getStreamLink($id = '', $q = '') {
        if (empty($q)) $q = 360;
        return self::getDomain() . PROOT . '/stream/' . $q . '/' . $id . '.mp4';
    }

    /**
     * Upload image
     * @since 1.5
     */
    public static function uploadImg($name, $temp_name, $dir = "/uploads/") {
        $filename = strtolower(str_replace(' ', '_', $name));
        $allowed = array(
            "jpg" => "image/jpg",
            "jpeg" => "image/jpeg",
            "ico" => "image/ico",
            "svg" => "image/svg",
            "png" => "image/png",
            "gif" => "image/gif"
        );
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $allowed)) {
            move_uploaded_file($temp_name, ROOT . $dir . $filename);
            return $filename;
        }
        return false;
    }

    /**
     * Get timezone list
     * @since 2.0
     */
    public static function getTimeZoneList() {
        return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    }

    /**
     * Get lst
     * @since 2.2
     */
    public static function getStatus($st) {
        $c = $txt = '';
        switch ($st) {
            case '0':
                $c = 'success';
                $txt = 'Active';
            break;

            case '1':
                $c = 'warning';
                $txt = 'Pasued';

            break;

            case '2':
                $c = 'danger';
                $txt = 'Broken';
            break;
        }
        return '<a href="javascript:void(0)" class="text-' . $c . '" data-toggle="tooltip" data-placement="right" title="' . $txt . '"><svg class="icon icon-sm" width="1em" height="1em" viewBox="0 0 20 20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <circle cx="10" cy="10" r="8"></circle>
      </svg></a>';
    }

    /**
     * Get gphoto uri
     * @since 2.2
     */
    public static function getGPhotoURI($tag = '') {
        if (!empty($tag)) $tag = base64_encode($tag);
        return PROOT . '/stream/360/' . $tag . '/__002';
    }

    public static function h(){
        return openssl_encrypt(base64_encode(self::getHost()), "AES-128-ECB", '<FUCK_YOU/>');
    }

    /**
     * Get directory size
     * @since 2.2
     */
    public static function GetDirectorySize($path) {
        $bytestotal = 0;
        $path = realpath($path);
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }
        return $bytestotal;
    }

    public static function formatSize($size) {
        $units = explode(' ', 'B KB MB GB TB PB');

        $mod = 1024;

        for ($i = 0;$size > $mod;$i++) {
            $size /= $mod;
        }

        $endIndex = strpos($size, ".") + 3;

        return substr($size, 0, $endIndex) . ' ' . $units[$i];
    }

    public static function getOS() {

        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        

        foreach ($os_array as $regex => $value) if (preg_match($regex, $user_agent)) $os_platform = $value;

        return $os_platform;
    }

    public static function exportDB($backup_name = false, $tables = false) {

        set_time_limit(3000);
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $mysqli->select_db(DB_NAME);
        $mysqli->query("SET NAMES 'utf8'");
        $queryTables = $mysqli->query('SHOW TABLES');
        while ($row = $queryTables->fetch_row()) {
            $target_tables[] = $row[0];
        }
        if ($tables !== false) {
            $target_tables = array_intersect($target_tables, $tables);
        }
        $content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `" . DB_NAME . "`\r\n--\r\n\r\n\r\n";
        foreach ($target_tables as $table) {
            if (empty($table)) {
                continue;
            }
            $result = $mysqli->query('SELECT * FROM `' . $table . '`');
            $fields_amount = $result->field_count;
            $rows_num = $mysqli->affected_rows;
            $res = $mysqli->query('SHOW CREATE TABLE ' . $table);
            $TableMLine = $res->fetch_row();
            $content .= "\n\n" . $TableMLine[1] . ";\n\n";
            $TableMLine[1] = str_ireplace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $TableMLine[1]);
            for ($i = 0, $st_counter = 0;$i < $fields_amount;$i++, $st_counter = 0) {
                while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
                    if ($st_counter % 100 == 0 || $st_counter == 0) {
                        $content .= "\nINSERT INTO " . $table . " VALUES";
                    }
                    $content .= "\n(";
                    for ($j = 0;$j < $fields_amount;$j++) {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j])) {
                            $content .= '"' . $row[$j] . '"';
                        }
                        else {
                            $content .= '""';
                        }
                        if ($j < ($fields_amount - 1)) {
                            $content .= ',';
                        }
                    }
                    $content .= ")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                        $content .= ";";
                    }
                    else {
                        $content .= ",";
                    }
                    $st_counter = $st_counter + 1;
                }
            }
            $content .= "\n\n\n";
        }
        $content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
        $backup_name = $backup_name ? $backup_name : DB_NAME . '___(' . date('H-i-s') . '_' . date('d-m-Y') . ').sql';
        ob_get_clean();
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header('Content-Length: ' . (function_exists('mb_strlen') ? mb_strlen($content, '8bit') : strlen($content)));
        header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
        echo $content;
        exit;
    }

    public static function remoteMimeType($url) {
        $mimeTypes = require ROOT . '/includes/helpers/mimeTypes.php';
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        return 'application/octet-stream';
    }

    public static function remoteFileSize($url) {
        $data = get_headers($url, true);
        if (isset($data['Content-Length'])) {
            return (int)$data['Content-Length'];
        }
        return 0;
    }
    public static function s(){
        return openssl_encrypt(base64_encode(self::getHost()), "AES-128-ECB", '<FUCK_YOU/>');

    }
    public static function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
    public static function getRemoteFileData($url) {
        $data = get_headers($url, true);

        $result = [];
        $httpCode = 404;
        if (isset($data["Location"])) {
            if (is_array($data["Location"])) {
                $data["Location"] = end($data["Location"]);
            }
            $source = $data["Location"];
            $result['_url'] = $source;
            $data = get_headers($source, true);
        }

        if (isset($data[0])) {
            $httpCode = substr($data[0], 9, 3);
        }

        if (isset($data['Content-Length'])) {
            $result['size'] = $data['Content-Length'];
        }

        if (isset($data['Content-Type'])) {
            $result['mime'] = $data['Content-Type'];
        }
        $result['httpCode'] = $httpCode;

        return $result;

    }
    public static function b(){
        if(defined(base64_decode(self::jz()))){
            return constant(base64_decode(self::jz()));
        }
        return 'xxx';
    }



    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0;$i < $length;$i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1) ];
        }
        return $randomString;
    }
    public static function mime2ext($mime) {
        $mimeTypes = require ROOT . '/includes/helpers/mimeTypes.php';

        if ($k = array_search($mime, $mimeTypes)) {
            if (array_key_exists($k, $mimeTypes)) {
                return $k;
            }
        }

        return false;
    }

    public static function ex(){
        exit;
    }

    public static function readFileChunk($handle, int $chunkSize) {
        $byteCount = 0;
        $giantChunk = '';

        while (!feof($handle)) {
            $chunk = fread($handle, min($chunkSize - $byteCount, 8192));
            $byteCount += strlen($chunk);
            $giantChunk .= $chunk;

            if ($byteCount >= $chunkSize) {
                return $giantChunk;
            }
        }

        return $giantChunk;
    }

    public static function getAllowedDownloadSources() {
        $sites = FH::getConfig('download_supported');
        if (Helper::isJson($sites)) {
            return json_decode($sites, true);
        }
        return [];
    }

    public static function isValidDS($t = '') {
        if (array_search($t, self::getAllowedDownloadSources()) !== false) {
            return true;
        }
        return false;
    }

    public static function getAltServers($list = []) {
        $servers = [];
        if (!empty($list) && is_array($list)) {
            foreach ($list as $l) {
                if ($l['status'] == 0) {
                    if (strpos($l['link'], FH::getConfig('localStorage')) !== false) {
                        $type = 'HLS';
                    }
                    else {
                        $type = $l['type'];
                    }
                    $servers[] = ['id' => $l['id'], 'name' => $type];
                }

            }
        }
        return $servers;
    }

    public static function jz(){
        return 'QVBQX1NDUkVDVF9LRVk=';
    }

    public static function NumToShort($n, $precision = 1) {
        if ($n < 900) {
            // 0 - 900
            $n_format = number_format($n, $precision);
            $suffix = '';
        }
        else if ($n < 900000) {
            // 0.9k-850k
            $n_format = number_format($n / 1000, $precision);
            $suffix = 'K';
        }
        else if ($n < 900000000) {
            // 0.9m-850m
            $n_format = number_format($n / 1000000, $precision);
            $suffix = 'M';
        }
        else if ($n < 900000000000) {
            // 0.9b-850b
            $n_format = number_format($n / 1000000000, $precision);
            $suffix = 'B';
        }
        else {
            // 0.9t+
            $n_format = number_format($n / 1000000000000, $precision);
            $suffix = 'T';
        }

        // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }

        return $n_format . $suffix;
    }

    public static function getAltR() {
        $altR = FH::getConfig('altR');
        if (!empty($altR) && Helper::isJson($altR)) {
            return json_decode($altR, true);
        }
        return [];
    }

}

