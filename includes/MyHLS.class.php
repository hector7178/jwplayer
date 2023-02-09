<?php



class MyHLS{

    protected $serverURI;
    protected $reqData = [];


    public function __construct($server){
        $this->serverURI = $server;
    }

    public function convert(){
        set_time_limit(0);
        session_write_close();
        $url = $this->serverURI . '/convert?' . http_build_query($this->reqData);
        Helper::curl($url);
        return true;
    }

    public function reqURI(){

    }

    public function delete($id){
        $url = $this->serverURI . '/delete/'.$id.'?secret_key='.HLS_API_SECRET_KEY;
        $results = Helper::curl($url);
        if(!empty($results) && Helper::isJson($results)){
            $resp = json_decode($results, true);
            if($resp['status'] == 'success'){
                return true;
            }
        }
        return false;
    }

    public function getStorageData(){
        $url = $this->serverURI . '/check/space/?secret_key='.HLS_API_SECRET_KEY;
        $results = Helper::curl($url);
        if(!empty($results) && Helper::isJson($results)){
            $resp = json_decode($results, true);
            return $resp;
        }
        return '';
    }

    public function set($d){
        if(is_array($d)){
            $d['secret_key'] = HLS_API_SECRET_KEY;
            $this->reqData = $d;
        }
        return $this;
    }

    public function getStatus($id){
        $url = $this->serverURI . '/status/'.$id;
        $results = Helper::curl($url);
        if(!empty($results) && Helper::isJson($results)){
            return json_decode($results, true);
        }
        return false;
    }

    public static function getHlsLink($server, $id){
        return $server . '/hls/' . $id . '/' . HLS_MASTER_FILE . '';
    }






}