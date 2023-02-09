<?php



class FH{

    protected static $config;

    public static function setConfig($c){
        self::$config = $c;
    }

    public static function getConfig($var){
        if(!empty(self::$config)){
            if(array_key_exists($var, self::$config)){
                return self::$config[$var];
            }
        }
        die('Required application configuration are does not exist !');
    }

    public static function getDriveAccounts($active = false, $isBackup = false, $isPasued = false){
        $ac = self::getConfig('driveAccounts');
        if(!empty($ac) && Helper::isJson($ac)){
            $data =  json_decode($ac, true);
           
            if($active || $isBackup){
                foreach($data as $ak => $av){
                    if($active){
                        if($av['status'] == 0)
                        unset($data[$ak]);
                    }
                    if($isBackup){
                        if($av['is_backup'] == 0)
                        unset($data[$ak]);
                    }
                    if($isPasued){
                        if($av['is_paused'] == 0)
                        unset($data[$ak]);
                    }
                    
                }
            }
            return $data;
        }
        return [];
    }

    public static function errorLog($e){
        error_log(print_r($e, true). "\n", 3, TMP_DIR.'/errors.log');

    }

    public static function flushN($e){
         echo $e . '<br>' ;
        ob_flush();
        flush();
    }



}