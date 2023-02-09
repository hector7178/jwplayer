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

class OkRu{

    protected $url = '';
    protected $id = '';
    protected $baseURI = 'https://ok.ru/videoembed/';
    protected $error = '';

    public function __construct(){

    }

    public function set($url){
        $this->url = $url;
        $this->id = $this->getId();
        return $this;
    }

    public function get(){
        return $this->getSources();
    }

    protected function getSources(){
        $headers = [
            'host: ok.ru',
            'origin: https://ok.ru'
        ];
        $sources = [];
        $quFormatsO = ['mobile', 'lowest', 'low', 'sd', 'hd', 'full'];
        $quFormatsN = ['144', '240', '360', '480', '720', '1080'];

        $results = Helper::curl($this->getReqURI(), $headers,'get',[],true);
        
        if(!empty($results)){
            $doc = new DOMDocument();
            @$doc->loadHTML($results);
            $xpath = new DOMXpath($doc);
            $data = $xpath->query('//div[@data-module="OKVideo"]');

            if(!empty($data) && is_object($data)){
                if($data->length == 1){
                    $playerData = json_decode(html_entity_decode($data[0]->getAttribute('data-options')), true);
                    if(isset($playerData['flashvars']['metadata'])){
                        $playerData = json_decode($playerData['flashvars']['metadata'], true);
                        if(!empty($playerData['videos']) && is_array($playerData['videos'])){
                            foreach($playerData['videos'] as $video){
                                $q = str_replace($quFormatsO, $quFormatsN, $video['name']);
                                $sources[$q] = ['file' => $video['url'], 'quality' => $q, 'type' => 'video/mp4', 'size' => 0];
                            }
                        }
                    }
                }
            }else{
                //contenct getting failed
            }

        }

        return $sources;
        
    }

    protected function getReqURI(){
        return $this->baseURI . $this->getId();
    }

    public function getId($url = ''){
        if(!empty($url)) $this->url = $url;
        if((empty($this->id) && !empty($this->url)) || !empty($url)){
            $u = parse_url($this->url, PHP_URL_PATH);
            if(!empty($u)){
                $u = rtrim(ltrim($u, '/'), '/');
                $u = explode('/', $u);
                $this->id = end($u);
            }
        }
        return $this->id;   
    }

    public function isExpired($lastUpdated){
        $timeFirst = strtotime($lastUpdated);
        $timeSecond = strtotime(Helper::tnow());
        $differenceInSeconds = $timeSecond - $timeFirst;
        return $differenceInSeconds < OKRU_EXPIRED && $differenceInSeconds > 1 ?  false : true;
    }







}