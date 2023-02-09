<?php

class HlsLink {

    protected $db;
    protected $config;
    protected $obj;
    protected $tbl = 'hls_links';
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
                $id = $this
                    ->db
                    ->insert($this->tbl, $this->getData());
                if ($id) {
                    $this->obj['id'] = $id;
                }
                else {
                    $this->error = $this
                        ->db
                        ->getLastError();
                }
            }
            else {
                $this
                    ->db
                    ->where('id', $this->getID());
                if (!$this
                    ->db
                    ->update($this->tbl, $this->getData() , '1')) {
                    $this->error = 'Update Filed ! -> ' . $this
                        ->db
                        ->getLastError();
                }
            }
        }
        return !$this->hasError() ? true : false;
    }

    /**
     * Get currect link id
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function getID() {
        if ($this->isEdit()) {
            return $this->obj['id'];
        }
        return false;
    }

    public function findById($id) {
        $this
            ->db
            ->where('id', $id);
        $link = $this
            ->db
            ->getOne($this->tbl);
        if ($this
            ->db->count > 0) {
            return $link;
        }
        return false;
    }

    protected function beforeSave() {

    }

    public function delHlsAll($linkId = '', $serId = '') {
        if (!empty($linkId)) $this
            ->db
            ->where('link_id', $linkId);
        if (!empty($serId)) $this
            ->db
            ->where('server_id', $serId);
        if (!empty($linkId) || !empty($serId)) {
            if ($this
                ->db
                ->delete($this->tbl)) {
                return true;
            }
        }
        return false;
    }

    public function del($id) {
        $this
            ->db
            ->where('id', $id);
        if ($this
            ->db
            ->delete($this->tbl, 1)) {
            return true;
        }
        return false;
    }
    public function broken($id, $st = true) {
        $st = $st ? 1 : 0;
        $this
            ->db
            ->where('id', $id);
        $this
            ->db
            ->update($this->tbl, ['status' => $st], 1);
    }

    public function getHlsIds() {
        $resp = $this
            ->db
            ->rawQuery('SELECT DISTINCT server_id as id FROM ' . $this->tbl);
        return !empty($resp) ? $resp : [];
    }
    public function delServerAll($serverId) {
        $this
            ->db
            ->where('server_id', $serverId);
        if ($this
            ->db
            ->delete($this->tbl)) {
            return true;
        }
        return false;
    }

    public function get($link_id = '', $server_id = '') {

        if (!empty($link_id)) $this
            ->db
            ->where('link_id', $link_id);
        if (!empty($server_id)) $this
            ->db
            ->where('server_id', $server_id);
        $results = $this
            ->db
            ->get($this->tbl);
        if ($this
            ->db->count > 0) {
            return $results;
        }

        return [];
    }

    protected function initProperties() {
        $dbColumns = $this
            ->db
            ->rawQuery("DESCRIBE " . $this->tbl);
        if (!empty($dbColumns)) {
            foreach ($dbColumns as $col) {
                $this->obj[$col['Field']] = NULL;
            }
        }
    }

}

