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


class Server {


    protected $db;
    protected $config;
    protected $obj;
    protected $tbl = 'servers';
    protected $servers;
    protected $error;


    /**
     * Blacklisted columns
     * @since 1.3
     *
     */
    protected $blackListed = ['id'];


    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = Database::getConfig();
        $this->initProperties();
        $this->init();
    }

    public function getDeletedServers($t = 'hls') {
        $this->db->where('is_deleted', 1);
        $this->db->where('type', 'hls');
        $results = $this->db->get($this->tbl);
        if ($this->db->count > 0) {
            return $results;
        }
        return [];
    }

    /**
     * Assign data
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function assign($data = []) {
        foreach ($data as $k => $v) {
            if (array_key_exists($k, $this->obj)) {
                $this->obj[$k] = $v;
            }
        }
        return $this;
    }

    /**
     * Initialize properties
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function initProperties() {
        $dbColumns = $this->db->rawQuery("DESCRIBE " . $this->tbl);
        if (!empty($dbColumns)) {
            foreach ($dbColumns as $col) {
                $this->obj[$col['Field']] = NULL;
            }
        }
    }

    /**
     * Check is edit or not
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function isEdit() {
        if (!empty($this->obj['id'])) {
            return true;
        }
        return false;
    }

    /**
     * Get data for save
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function getData() {
        $data = $this->obj;
        foreach ($this->blackListed as $bl) {
            if (array_key_exists($bl, $data)) {
                unset($data[$bl]);
            }
        }
        return $data;
    }

    /**
     * Check error
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function hasError() {
        if (!empty($this->error)) {
            return true;
        }
        return false;
    }

    /**
     * Get error
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Find by ID
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function findById($id) {
        $this->db->where('id', $id);
        $link = $this->db->getOne($this->tbl);
        if ($this->db->count > 0) {
            return $link;
        }
        return false;
    }

    /**
     * Save data
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
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
        if (empty($this->obj['playbacks'])) {
            $this->obj['playbacks'] = 0;
        }
        if (!$this->isEdit()) {
            $this->obj['status'] = 0;
        }
    }

    protected function init() {
        $this->db->where("is_broken", 0);
        $this->db->where("status", 0);
        $servers = $this->db->get($this->tbl);
        if ($this->db->count > 0) {
            foreach ($servers as $k => $v) {
                if ($v['type'] == 'stream') {
                    $this->servers[$v['id']] = $v;
                }
            }
        } else {
            $this->servers = [];
        }
    }

    public function getAll($a = false, $c = false) {
        if (!$a) {
            $this->db->orderBy("id", "Desc");
            $servers = $this->db->get($this->tbl);
            if (!$c) {
                return $this->db->count > 0 ? $servers : [];
            } else {
                return $this->db->count;
            }
        } else {
            return $this->servers;
        }
    }

    public function get($type = '', $st = '', $is_broken = '') {
        if (!empty($type)) $this->db->where('type', $type);
        if (!empty($st)) {
            if ($st == 'active') {
                $this->db->where('status', 0);
            } else {
                $this->db->where('status', 1);
            }
        }
        if ($is_broken != '') $this->db->where('is_broken', $is_broken);
        $results = $this->db->get($this->tbl);
        if ($this->db->count > 0) {
            return $results;
        }
        return [];
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

    /**
     * Get currect server id
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function getID() {
        if ($this->isEdit()) {
            return $this->obj['id'];
        }
        return false;
    }

    public function getOne($id = '') {
        $s = false;
        if (!empty($this->servers)) {
            if (!empty($id)) {
                if (array_key_exists($id, $this->servers)) {
                    $s = $this->servers[$id];
                }
            } else {
                $s = $this->servers[array_rand($this->servers) ];
            }
        }
        return $s;
    }

    public function del($id, $soft = false) {
        $this->db->where('id', $id);
        if (!$soft) {
            if ($this->db->delete($this->tbl, 1)) {
                return true;
            }
        } else {
            if ($this->db->update($this->tbl, ['is_deleted' => 1], 1)) {
                return true;
            }
        }
        return false;
    }

    public function isHit() {
        $isHit = false;
        if ($this->isEdit()) {
            if ($this->obj['type'] == 'hls') {
                $surl = $this->obj['domain'] . '/check';
            } else {
                $surl = $this->obj['domain'] . '/stream/check';
            }
            $resp = Helper::curl($surl);
            if (!empty($resp)) {
                if (strpos($resp, 'Looks Good') !== false) {
                    $isHit = true;
                }
            }
        }
        if ($isHit) {
            if ($this->obj['is_broken'] == 1) {
                $isBroken = 0;
            }
        } else {
            if ($this->obj['is_broken'] == 0) {
                $isBroken = 1;
            }
        }
        if (isset($isBroken)) {
            $this->obj['is_broken'] = $isBroken;
            $this->save();
        }
        return $isHit;
    }

    public function changeStatus() {
        if ($this->isEdit()) {
            if ($this->obj['status'] == 1) {
                $this->obj['status'] = 0;
            } else {
                $this->obj['status'] = 1;
            }
            $this->save();
        }
    }
    
    public function addPlayback() {
        if ($this->isEdit()) {
            $this->db->where('id', $this->getID());
            $this->db->update($this->tbl, ['playbacks' => $this->obj['playbacks'] + 1], 1);
        }
    }

}
