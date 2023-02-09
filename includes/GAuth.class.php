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


class GAuth {


    private $user;
    private $accessToken;
    protected $createdAt;
    protected $error = '';
    protected $tbl = 'drive_auth';
    protected $db = null;
    protected $blackListed = ['id', 'status'];



    public function __construct($id = '') {
        $this->db = Database::getInstance();
        $this->initUser();
        if ($this->loadUser($id)) {
            $this->setAccessToken();
        }
    }
    
    public function getAccessToken() {
        if (!$this->hasError()) {
            if (!empty($this->accessToken)) {
                return $this->accessToken;
            } else {
                $this->error = 'Unknown Error : Access token varification failed ! ';
            }
        }
        return false;
    }

    public function getUser() {
        if (!$this->hasError()) {
            if (!empty($this->user)) {
                return $this->user;
            } else {
                $this->error = 'unknow Error : Auth User load failed !';
            }
        }
        return false;
    }

    public function loadUser($id = '') {
        $skip = false;

        if (empty($id)) {
            $this->db->where('status', 0);
            $users = $this->db->get($this->tbl);
            if ($this->db->count > 0) {
                $this->user = $users[array_rand($users) ];
            }
        } else {
            if ($e = $this->isDriveExist($id)) {
                $this->db->where('email', $e);
                $this->db->where('status', 0);
                $skip = true;
            } else {
                $this->db->where('id', $id);
            }
            $users = $this->db->get($this->tbl);
            if ($this->db->count > 0) {
                $this->user = $users[array_rand($users) ];
            } else {
                if ($skip) $this->loadUser();
            }
        }
     
        if (empty($this->getId())) $this->error = 'GAuth user not found !';
        return !$this->hasError();
    }

    protected function isDriveExist($id) {
        $driveAccounts = $this->getDriveAccounts();
        if (array_key_exists($id, $driveAccounts)) {
            return $driveAccounts[$id]['email'];
        }
        return false;
    }

    protected function getUserByEmail($id) {
    }

    protected function getDriveAccounts() {
        if (!empty(FH::getConfig('driveAccounts'))) {
            $ac = FH::getConfig('driveAccounts');
            if (!empty($ac) && Helper::isJson($ac)) {
                return json_decode($ac, true);
            }
        }
        return [];
    }

    public function hasError() {
        return !empty($this->error) ? true : false;
    }

    public function getError() {
        return $this->error;
    }

    protected function setAccessToken() {
        if (!empty($this->user['access_token'])) {
            $this->createdAt = $this->user['updated_at'];
            $this->accessToken = json_decode($this->user['access_token'], true) ['access_token'];
        }
        if (!$this->isValidToken()) $this->reloadToken();
    }

    protected function isValidToken() {
        if (!empty($this->accessToken)) {
            $lastUpdated = $this->createdAt;
            $timeFirst = strtotime($lastUpdated);
            $timeSecond = strtotime(Helper::tnow());
            $differenceInSeconds = $timeSecond - $timeFirst;
            if ($differenceInSeconds < 3500 && $differenceInSeconds > 1) {
                return true;
            }
        }
        return false;
    }

    protected function reloadToken() {
        $userData = ['client_id' => $this->user['client_id'], 'client_secret' => $this->user['client_secret'], 'refresh_token' => $this->user['refresh_token'], 'grant_type' => 'refresh_token'];
        $isOK = false;
        session_write_close();
        usleep(rand(1000000, 1500000));
        $curl = curl_init();
        curl_setopt_array($curl, array(CURLOPT_URL => 'https://www.googleapis.com/oauth2/v4/token', CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_MAXREDIRS => 2, CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($userData), CURLOPT_USERAGENT => Helper::getUserAgent(),));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!$err) {
            $tokenInfo = json_decode($response, true);
            if (!isset($tokenInfo['error'])) {
                if (isset($tokenInfo['expires_in'])) unset($tokenInfo['expires_in']);
                if (isset($tokenInfo['scope'])) unset($tokenInfo['scope']);
                $this->accessToken = $tokenInfo['access_token'];
                $this->createdAt = Helper::tnow();
                if (!$this->assign(['access_token' => json_encode($tokenInfo) ])->save()) {
                    $this->error = 'Access token saved failed !';
                } else {
                    $isOK = true;
                }
            } else {
                $this->error = 'gdrive_access_token ' . $tokenInfo['error'] . ' => ' . $tokenInfo['error_description'];
            }
        } else {
            $this->error = 'gdrive_access_token ' . $status . ' => ' . $err;
        }
        if (!empty($this->getId())) {
            if ($isOK) {
                if ($this->isBroken()) {
                    $this->broken(false);
                }
            } else {
                if (!$this->isBroken()) {
                    $this->broken();
                }
            }
        }
    }

    protected function isBroken() {
        return isset($this->user['status']) && $this->user['status'] == 1;
    }

    protected function broken($a = true) {
        if ($a) {
            $this->user['access_token'] = '';
            $st = 1;
        } else {
            $st = 0;
        }
        $this->user['status'] = $st;
        $this->removeBlackListed(['status']);
        $this->save();
    }

    public function assign($data) {
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                if (array_key_exists($k, $this->user)) {
                    $this->user[$k] = $v;
                }
            }
        }
        return $this;
    }

    public function save($tmp = false) {
        $this->beforeSave();
        $data = $this->getData();
        if (!empty($data)) {
            if (!$this->isNew()) {
                $this->db->where('id', $this->getId());
                $id = $this->db->update($this->tbl, $data, 1);
                if (!$id) {
                    $this->error = 'GAuth DB Update Failed !';
                }
            } else {
                $id = $this->db->insert($this->tbl, $data);
                if (!$id) {
                    $this->error = 'GAuth DB Insert Failed !';
                } else {
                    $this->user['id'] = $id;
                }
            }
        }
        return isset($id) ? true : false;
    }

    public function removeBlackListed($keywords = []) {
        if (is_array($keywords)) {
            foreach ($keywords as $k => $keyword) {
                if (in_array($keyword, $this->blackListed)) {
                    unset($this->blackListed[$k]);
                }
            }
        }
    }

    protected function getData() {
        $data = $this->user;
        if (!empty($this->blackListed) && is_array($this->blackListed)) {
            foreach ($this->blackListed as $v) {
                if (array_key_exists($v, $data)) {
                    unset($data[$v]);
                }
            }
        }
        return is_array($data) ? $data : [];
    }

    protected function beforeSave() {
        $this->user['updated_at'] = Helper::tnow();
        if ($this->isNew()) {
            $this->user['created_at'] = Helper::tnow();
        }
    }

    public function getId() {
        return isset($this->user['id']) && !empty($this->user['id']) ? $this->user['id'] : 0;
    }

    protected function isNew() {
        return empty($this->getId()) ? true : false;
    }

    protected function initUser() {
        $dbColumns = $this->db->rawQuery("DESCRIBE " . $this->tbl);
        if (!empty($dbColumns)) {
            foreach ($dbColumns as $col) {
                $this->user[$col['Field']] = NULL;
            }
        }
    }
    
    public function getAccount($id) {
        $this->db->where('id', $id);
        return $this->db->getOne($this->tbl);
    }

    public function getAccounts($id = '', $email = '') {
        if (!empty($id)) $this->db->where('id', $id);
        if (!empty($email)) $this->db->where('email', $email);
        $accounts = $this->db->get($this->tbl);
        return !empty($accounts) ? $accounts : [];
    }

    public function delByAccEmail($email) {
        $this->db->where('email', $email);
        if ($this->db->delete($this->tbl)) {
            return true;
        }
        return false;
    }

    public function clean() {
        $this->initUser();
        $this->accessToken = '';
    }

    public function delete($id = '') {
        if (empty($id) && isset($this->user['id'])) $id = $this->user['id'];
        if (!empty($id)) {
            $this->db->where('id', $id);
            if ($this->db->delete($this->tbl)) return true;
        }
        return false;
    }

    public function updateId($id) {
        if (!empty($id) && !$this->isNew()) {
            $this->db->where('id', $this->getId());
            if ($this->db->update($this->tbl, ['id' => $id])) return true;
        }
        return false;
    }

    public function __destruct() {
        $this->db = null;
        $this->user = [];
        $this->accessToken = '';
    }

}
