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


class BackupDrives {


    protected $db;
    protected $config;
    protected $obj;
    protected $tbl = 'backup_drives';
    protected $error;
    protected $blackListed = ['id'];


    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = Database::getConfig();
        $this->initProperties();
    }


    public function assign($data = []) {
        foreach ($data as $k => $v) {
            if (array_key_exists($k, $this->obj)) {
                $this->obj[$k] = $v;
            }
        }
        return $this;
    }


    public function isEdit() {
        if (!empty($this->obj['id'])) {
            return true;
        }
        return false;
    }


    protected function getData() {
        $data = $this->obj;
        foreach ($this->blackListed as $bl) {
            if (array_key_exists($bl, $data)) {
                unset($data[$bl]);
            }
        }
        return $data;
    }


    public function hasError() {
        if (!empty($this->error)) {
            return true;
        }
        return false;
    }


    public function getError() {
        return $this->error;
    }


    public function getObj() {
        return $this->obj;
    }


    public function save() {
        if (!$this->hasError()) {
            $this->beforeSave();
            if (!$this->isEdit()) {
                $id = $this->db->insert($this->tbl, $this->getData());
                if ($id) {
                    $this->obj['id'] = $id;
                } else {
                    $this->error = $this->db->getLastError();
                }
            } else {
                $this->db->where('id', $this->getID());
                if (!$this->db->update($this->tbl, $this->getData(), '1')) {
                    $this->error = 'Update Filed ! -> ' . $this->db->getLastError();
                }
            }
        }
        return !$this->hasError() ? true : false;
    }


    protected function beforeSave() {
    }


    public function findById($id) {
        $this->db->where('id', $id);
        $link = $this->db->getOne($this->tbl);
        if ($this->db->count > 0) {
            return $link;
        }
        return false;
    }


    public function get($link_id = '', $acc_id = '') {
        if (!empty($link_id)) $this->db->where('link_id', $link_id);
        if (!empty($type)) $this->db->where('acc_id', $acc_id);
        $results = $this->db->get($this->tbl);
        if ($this->db->count > 0) {
            return $results;
        }
        return [];
    }


    public function getOne($link_id = '') {
    }


    public function broken($id, $un = true) {
        $this->db->where('id', $id);
        $status = $un ? 1 : 0;
        $this->obj['status'] = $status;
        $this->db->update($this->tbl, ['status' => $status], '1');
    }


    public function load($id) {
        if (!is_array($id)) {
            $server = $this->findById($id);
            if ($server) {
                foreach ($server as $k => $v) {
                    if (array_key_exists($k, $this->obj)) {
                        $this->obj[$k] = $v;
                    }
                }
                return true;
            }
            return false;
        } else {
            $this->obj = $id;
        }
    }


    public function getID() {
        if ($this->isEdit()) {
            return $this->obj['id'];
        }
        return false;
    }


    public function getBackupIds() {
        $resp = $this->db->rawQuery('SELECT DISTINCT acc_id as id FROM ' . $this->tbl);
        return !empty($resp) ? $resp : [];
    }


    public function del() {
        if ($this->isEdit()) {
            $this->db->where('id', $this->getID());
            if ($this->db->delete($this->tbl)) {
                return true;
            }
        }
        return false;
    }


    public function delDriveAll($accId = '', $linkId = '') {
        if (!empty($linkId)) $this->db->where('link_id', $linkId);
        if (!empty($accId)) $this->db->where('acc_id', $accId);
        if (!empty($linkId) || !empty($accId)) {
            if ($this->db->delete($this->tbl)) {
                return true;
            }
        }
        return false;
    }


    protected function initProperties() {
        $dbColumns = $this->db->rawQuery("DESCRIBE " . $this->tbl);
        if (!empty($dbColumns)) {
            foreach ($dbColumns as $col) {
                $this->obj[$col['Field']] = NULL;
            }
        }
    }


}
