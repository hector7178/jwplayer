<?php 

/**
 * ====================================================================================
 *                           Google Drive Proxy Player (c) CodySeller
 * ----------------------------------------------------------------------------------
 * @copyright This software is exclusively sold at codester.com. If you have downloaded this
 *  from another site or received it from someone else than me, then you are engaged
 *  in an illegal activity. You must delete this software immediately or buy a proper
 *  license from https://www.codester.com/codyseller?ref=codyseller.
 *
 *  Thank you for your cooperation and don't hesitate to contact me if anything :)
 * ====================================================================================
 *
 * @author CodySeller (http://codyseller.com)
 * @link http://codyseller.com
 * @license http://codyseller.com/license
 */


class Yandex 
{


    protected $url;
    protected $sources = '';
    protected $error;
    protected $baseURI = 'https://yadi.sk/i/';
    protected $proxy = '';

    public function __construct()
    {
        

    }

    public function get($url)
    {
        $this->url = $url;
        $this->fURI();
        return $this->loadSources();
    }

    protected function loadSources()
    {

        $u = 'http://workplace.codyseller.com/yandex/?url=' . $this->url;
        $results = Helper::curl($u);

        if(!empty($results) && Helper::isJson($results)){
            $results = json_decode($results, true);
            if($results['status'] == 'success'){
                return $results['link'];
            }
        }
        return '';

    }

    protected function fURI(){
        $u = parse_url($this->url, PHP_URL_PATH);
        if(!empty($u)){
            $u = explode('/', rtrim(ltrim($u, '/'), '/'));
            $this->url = $this->baseURI . end($u);
        }
    }

    public static function getFID($u){
        $u = parse_url($u, PHP_URL_PATH);
        if(!empty($u)){
            $u = explode('/', rtrim(ltrim($u, '/'), '/'));
            return end($u);
        }
        return '';
    }

    public static function getStreamURI($ml){
        $id = self::getFID($ml);
        return Helper::getDomain() . PROOT . '/stream/hls/' . base64_encode(Helper::e($id)) .  '/' . YANDEX_IDENTIFY . '/master.m3u8';
    }

    

   

}