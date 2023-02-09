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


class GDrive{

    /**
     * Proxy IP
     * @since 1.5
     **/
    protected $proxy;

    /**
     * Drive Access token
     * @since 2.3
     **/
    protected $accessToken = '';

    /**
     * File ID
     * @since 1.3
     **/
    protected $id;

    /**
     * Source links
     * @since 1.3
     **/
    protected $source;

    /**
     * Cookies
     * @since 1.3
     **/
    protected $cookiz;


    protected $error = '';


    public function __construct($accId){
        if ($p = Proxy::getOne())
        {
            $this->proxy = $p;
        }

        $gauth = new GAuth($accId);

        if($t = $gauth->getAccessToken()){
            $this->accessToken = $t;
        }else{
            $this->addError($gauth->getError());
        }


    }

    public function get($id){
        $this->setId($id);
        return $this->getSources();
    }

    /**
     * Get video sources
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function getSources($reloads = 0){

        $url = "https://docs.google.com/get_video_info?docid=" . $this->id;

        $cookies = $sources = [];
        $title = '';

        usleep(rand(900000, 1500000));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, Helper::getUserAgent());
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_COOKIEJAR, ROOT . '/data/cookiz/gdrive~' . $this->key . '.txt');

        if($this->issetAccessToken()){
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $this->getAccessToken()]);
        }

        if ($this->issetProxy())
        {
            curl_setopt($ch, CURLOPT_PROXY, $this->getProxy());
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, FH::getConfig('proxyUser'). ':' . FH::getConfig('proxyPass'));
            if (Proxy::isSocks($this->getProxy()))
            {
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
            }
        }

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
       
        if (empty($result) || $info["http_code"] != "200")
        {
            if ($info["http_code"] == "200")
            {
                $this->addError("cURL Error (" . curl_errno($ch) . "): " . (curl_error($ch) ? : "Unknown"));
            }
            else
            {
                $this->addError("Error Occurred (" . $info["http_code"] . ")");
            }
        }
        else
        {

            $header = substr($result, 0, $info["header_size"]);
            $result = substr($result, $info["header_size"]);
            preg_match_all("/^Set-Cookie:\\s*([^=]+)=([^;]+)/mi", $header, $cookie);
            foreach ($cookie[1] as $i => $val)
            {
                $cookies[] = $val . "=" . trim($cookie[2][$i], " \n\r\t");
            }

            parse_str($result, $fileData);

            if ($fileData['status'] == 'ok')
            {
                $streams = explode(',', $fileData['fmt_stream_map']);
                foreach ($streams as $stream)
                {
                    list($quality, $link) = explode("|", $stream);
                    $fmt_list = array(
                        '37' => "1080",
                        '22' => "720",
                        '59' => "480",
                        '18' => "360",
                    );
                    if (array_key_exists($quality, $fmt_list))
                    {
                        $quality = $fmt_list[$quality];
                        $sources[$quality] = ['file' => $link, 'quality' => $quality, 'type' => 'video/mp4', 'size' => 0];
                    }

                }
                if (isset($fileData['title']))
                {
                    $title = $fileData['title'];
                }

            }
            else
            {
                if(strpos($fileData['reason'], 'playbacks has been exceeded') !== false)
                {
                    $this->addError('This Video is unavailable !');
                }
                else
                {
                    $this->addError($fileData['reason']);
                }
            }


            if (!$this->hasError())
            {
                $this->saveToCache($sources);
                return ['title' => $title, 'data' => ['sources' => $sources, 'cookies' => $cookies]];
            }
            else
            {
                if ($reloads < 2)
                {
                    $this->removeError();
                    return $this->getSources($reloads + 1);
                }
    
            }
    
            return false;

        }

    }

    protected function issetAccessToken(){
        return !empty($this->accessToken) ? true : false;
    }

    protected function getAccessToken(){
        return $this->accessToken;
    }

    protected function issetProxy(){
        return !empty($this->proxy) ? true : false;
    }

    protected function getProxy(){
        return $this->proxy;
    }

    public function setKey($key){
        $this->key = $key;
    }

    public function getKey(){
        return $this->key;
    }

    protected function addError($error){
        $this->error = $error;
    }

    public function getError(){
        return $this->error;
    }

    protected function removeError(){
        $this->error = '';
    }

    public function hasError(){
        return !empty($this->getError());
    }

    protected function setId($id){
        $this->id = $id;
    }

    protected function getid(){
        return $this->id;
    }

    protected function saveToCache($s){
        $cache = new Cache($this->key);
        $cache->save($s);
    }
    
    public function isValidAuth()
    {
        return !empty($this->accessToken) ? true : false;
    }
  





}