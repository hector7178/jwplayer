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



class OneDrive 
{

    protected $url;
    protected $source = '';
    protected $error;

    public function __construct()
    {
        
    }

    public function get($url)
    {
        $this->url = $url;

        if($this->loadSourceLink()->isOk()){
            
            $link =  PROOT . '/stream/360/' . base64_encode(urlencode($this->getSource())) . '/__003?vh='.time();
            $resp = [
                [
                    'file' => $link,
                    'q' => 360
                ]
            ];
         
            return $resp;

        }

        return false;

    }


    protected function loadSourceLink(){

        if (strpos($this->url, 'my.sharepoint.com') !== false) {
            $url = $this->url . '&download=1';
            $ls = Helper::isI($url);
          
            if($ls != 404)
            { 
                $this->source = $url;
            }
           
        } else {
            
            if( strpos($this->url, "1drv.ms") !== false){

                $rUrl = Helper::getRemoteFileData($this->url);

                if(!empty($rUrl) && isset($rUrl['_url']) && strpos($rUrl['_url'], 'redir?') !== false){

                    $burl = str_replace('redir?','embed?',$rUrl['_url']);

                    $results = Helper::curl($burl);

                    if(!empty($results)){
                        $results = Helper::getStringBetween($results, 'window.itemData =', ';');
                    
                        if(!empty($results) && Helper::isJson($results)){
                            
                            $results = json_decode(trim($results), true);
    
                            if(isset($results['items'][0]['urls']['download'])){
                                $this->source = $results['items'][0]['urls']['download'];
                            }
    
                        }
                    }
    
                }

            }
          
        }
        return $this;

    }

    protected function isOk(){
        return !empty($this->getSource()) ? true : false;
    }

    protected function getSource(){
        return $this->source;
    }


}