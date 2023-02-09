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


class Link {

    /**
     * Object data
     * @since 1.3
     *
     */
    public $obj = [];
    /**
     * Links table
     * @since 1.3
     *
     */
    protected $tbl = 'links';
    protected $altTbl = 'alt_links';
    /**
     * Blacklisted columns
     * @since 1.3
     *
     */
    protected $blackListed = ['id', 'deleted', 'views', 'altLinks', 'downloads'];
    /**
     * Database
     * @since 1.3
     *
     */
    protected $db;
    /**
     * Configuration
     * @since 1.3
     *
     */
    protected $config;
    /**
     * Link error
     * @since 1.3
     *
     */
    protected $error = '';
    protected $t = false;
    protected $altLinks = [];


    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = Database::getConfig();
        $this->initProperties();
    }

    /**
     * Assign data
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function assign($data = []) {
        if (isset($data['main_link'])) {
            $this->s($data['main_link']);
        }
        foreach ($data as $k => $v) {
            if (array_key_exists($k, $this->obj)) {
                $this->obj[$k] = $v;
            }
        }
        if (!$this->isEdit() && !$this->isAlt() && empty($this->obj['slug'])) {
            $slug = Helper::random();
            if ($this->isExit($slug)) {
                $slug = Helper::random();
            }
            $this->obj['slug'] = $slug;
        }
        if ($data['type'] == 'GDrive' && $this->t) {
            $this->set();
        }
        if ($data['type'] == 'Yandex' && $this->t) {
            $this->setY();
        }
        if ($data['type'] == 'OkRu' && $this->t) {
            $this->setO();
        }
        return $this;
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
                    $this->setupAltLinks();
                } else {
                    $this->error = $this->db->getLastError();
                }
            } else {
                $this->db->where('id', $this->getID());
                if (!$this->db->update($this->tbl, $this->getData(), '1')) {
                    $this->error = 'Update Filed ! -> ' . $this->db->getLastError();
                } else {
                    $this->setupAltLinks();
                }
            }
        } else {
            FH::errorLog('Save error !');
        }
        return !$this->hasError() ? true : false;
    }

    protected function setupAltLinks() {
        if (!empty($this->altLinks)) {
            $i = 0;
            foreach ($this->altLinks as $ak => $alt) {
                $tmpObj = new $this;
                $tmpObj->switch('alt');
                if (isset($alt['is_remove']) && $alt['is_remove'] == 1) {
                    if (isset($alt['id'])) {
                        $tmpObj->delete($alt['id']);
                    }
                } else {
                    $data = ['parent_id' => $this->getID(), 'link' => $alt['link'], 'type' => $alt['type'], 'status' => 0, '_order' => $i];
                    if (isset($alt['id'])) {
                        $data['id'] = $alt['id'];
                    }
                    $tmpObj->assign($data)->save();
                }
                $i++;
            }
        }
    }

    public function switch ($t = '') {
            if ($t == 'alt') {
                $this->tbl = $this->altTbl;
            }
            if ($t == 'main') {
                $this->tbl = 'links';
            }
            $this->initProperties();
            return $this;
    }

    /**
     * Do something, before save data
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function beforeSave() {
        $this->obj['updated_at'] = Helper::tnow();
        if (!$this->isEdit()) {
            if (!$this->isAlt() && empty($this->obj['title'])) {
                $this->obj['title'] = 'Unknown';
            }
            $this->obj['created_at'] = Helper::tnow();
        }
    }

    protected function isAlt() {
        return $this->tbl == $this->altTbl ? true : false;
    }

    protected function setY($alt = false) {
        $yandex = new Yandex($this->config);
        if (!$alt) {
            $url = $this->obj['main_link'];
        } else {
            $url = $this->obj['alt_link'];
        }
        $source = $yandex->get($url);
        if (!empty($source)) {
            $this->obj['data'] = $source;
        } else {
            $this->error = 'Video not found !';
        }
    }

    public function setO() {
        $ml = $this->obj['main_link'];
        $okru = new OkRu;
        $l = $okru->set($ml)->get();
        if (!empty($l)) {
            $cache = new Cache($okru->getId(), 'okru');
            $cache->save($l);
        }
    }

    public function setAltLinks($links) {
        if (!empty($links) && is_array($links)) {
            foreach ($links as $k => $link) {
                if (isset($link['link']) && Helper::isUrl($link['link'])) {
                    $altType = Helper::getLinkType($link['link']);
                    $links[$k]['type'] = $altType;
                } else {
                    unset($links[$k]);
                }
            }
        }
        $this->altLinks = $links;
    }

    /**
     * Set main source links
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function set($alt = false, $rld = 0, $skipIds = []) {
        // echo '............................looped '.$rld.'.......................' ;
        // ob_flush();
        // flush();
        // FH::flushN('............................looped '.$rld.'.......................');
        $accId = $this->obj['acc_id'];
        if (!$alt) {
            if ($this->isEdit()) {
                $driveAccounts = FH::getDriveAccounts(false, false, true);
                $isPaused = false;
                if ($rld == 0 && !in_array($this->getID(), $skipIds)) {
                    if (array_key_exists($accId, $driveAccounts)) {
                        if ($driveAccounts[$accId]['status'] == 1) {
                            if ($driveAccounts[$accId]['is_paused'] == 1) {
                                $isPaused = true;
                            }
                        } else {
                            //current gauth not created yet
                            // FH::flushN('Current gauth not created with -> ' . $driveAccounts[$accId]['email']);
                            
                        }
                    } else {
                        //current drive acc does not exist
                        // FH::flushN('Current drive acc does not exist -> Link Id : ' . $this->getID());
                        
                    }
                } else {
                    $isPaused = true;
                }
                if ($isPaused) {
                    $backupDrive = new BackupDrives;
                    $results = $backupDrive->get($this->getID(), $accId);
                    if (!empty($results)) {
                        foreach ($results as $bk => $bv) {
                            if (array_key_exists($bv['acc_id'], $driveAccounts)) {
                                if ($driveAccounts[$bv['acc_id']]['status'] == 1) {
                                    if ($driveAccounts[$bv['acc_id']]['is_paused'] == 0) {
                                        if (!in_array($bv['id'], $skipIds)) {
                                            $bID = $bv['id'];
                                            $gid = $bv['file_id'];
                                            $accId = $bv['acc_id'];
                                            break;
                                        } else {
                                            // FH::flushN('Skipped : ' . $bv['id']);
                                            
                                        }
                                    }
                                } else {
                                    // FH::flushN('Current gauth not created with -> ' . $driveAccounts[$bv['acc_id']]['email']);
                                    
                                }
                            }
                        }
                    } else {
                        // FH::flushN('Backup not found.... : ');
                        
                    }
                }
                if (!isset($gid) && !in_array($this->getID(), $skipIds) && $rld == 0) {
                    $gid = Helper::getDriveId($this->obj['main_link']);
                }
            } else {
                $gid = Helper::getDriveId($this->obj['main_link']);
            }
        } else {
            $gid = Helper::getDriveId($this->obj['alt_link']);
        }

        if (!empty($gid)) {
            $gdrive = new GDrive($accId);
            $gdrive->setKey($this->obj['slug']);
            $result = $gdrive->get($gid);
            if ($result !== false) {
                if (is_array($result)) {
                    if (empty($this->obj['title'])) {
                        $this->obj['title'] = $result['title'];
                    }
                    $this->obj['data'] = json_encode($result['data']);
                } else {
                    $this->obj['data'] = '';
                }
                if (!isset($backupDrive)) {
                    if ($this->isEdit() && $this->obj['status'] == 2 && !$alt) {
                        $this->broken(false);
                        // FH::flushN('Main link status updated -> success !');
                        
                    }
                } else {
                    if (isset($bID)) {
                        $backupDrive->broken($bID, false);
                        // FH::flushN('Backup link status updated -> success ! ' . $bID);
                        
                    }
                }
                // FH::flushN('success !');
                // $this->obj['is_alt'] = $alt ? 1 : 0;
                
            } else {
                if ($gdrive->hasError()) {
                    // FH::flushN($gdrive->getError());
                    if ($this->isEdit()) {
                        if (!isset($backupDrive)) {
                            if (!$this->isBroken()) {
                                $this->broken();
                            }
                            $skipIds[] = $this->getID();
                            // FH::flushN('Main link status updated -> broken !');
                            
                        } else {
                            if (isset($bID)) {
                                $skipIds[] = $bID;
                                $backupDrive->broken($bID);
                                // FH::flushN('Backup link status updated -> broken ! ' . $bID);
                                
                            }
                        }
                        if ($rld < 3) {
                            $rld+= 1;
                            // FH::flushN('Repeated...' );
                            return $this->set(false, $rld, $skipIds);
                        }
                    } else {
                        $this->error = $gdrive->getError();
                    }
                }
            }
        } else {
            // FH::flushN('Loop stopped !');
            
        }
    }

    public function broken($un = true) {
        if ($this->isEdit()) {
            $this->db->where('id', $this->getID());
            $status = $un ? 2 : 0;
            $this->obj['status'] = $status;
            $this->db->update($this->tbl, ['status' => $status], '1');
        }
    }

    public function isBroken() {
        if ($this->isEdit()) {
            if ($this->obj['status'] == 2) {
                return true;
            }
        }
        return false;
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
     * Initialize properties
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function initProperties() {
        $dbColumns = $this->db->rawQuery("DESCRIBE " . $this->tbl);
        $this->obj = [];
        if (!empty($dbColumns)) {
            foreach ($dbColumns as $col) {
                $this->obj[$col['Field']] = NULL;
            }
        }
    }
    
    public function isExit($s, $ty = 'slug') {
        if ($ty == 'slug') {
            if ($link = $this->findBySlug($s)) {
                if ($link['slug'] != $this->obj['slug']) {
                    return true;
                }
            }
        }
        if ($ty == 'id') {
            if ($this->findById($s)) {
                return true;
            }
        }
        return false;
    }

    public function getNextLink() {
        if ($this->isEdit()) {
            $sql = "select * from " . $this->tbl . " where id = ";
            $sql.= "(select min(id) from  " . $this->tbl . " where id > " . $this->getID() . ")";
            $results = $this->db->rawQuery($sql);
            if ($this->db->count > 0) {
                return $results[0];
            }
        }
        return '';
    }

    public function getAll($s = '') {
        $st = ['all' => '', 'active' => 0, 'paused' => 1, 'broken' => 2];
        if (!empty($s)) {
            $this->db->where("status", $st[$s]);
        }
        $this->db->orderBy("id", "Desc");
        $links = $this->db->get($this->tbl);
        return $this->db->count > 0 ? $links : [];
    }

    /**
     * Find by slug
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function findBySlug($s) {
        $this->db->where('slug', $s);
        $link = $this->db->getOne($this->tbl);
        if ($this->db->count > 0) {
            return $link;
        }
        return false;
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

    public function search($k) {
        if (Helper::isDrive($k)) {
            $k = Helper::getDriveId($k);
        }
        if (strpos($this->obj['main_link'], $k) === false) {
            $this->db->where("main_link", "%$k%", 'like');
            $results = $this->db->getOne($this->tbl);
            if ($this->db->count > 0) {
                return $results;
            }
        }
        return false;
    }

    public function getByAccId($acc_id) {
        $this->db->where("acc_id", $acc_id);
        $results = $this->db->get($this->tbl);
        if ($this->db->count > 0) {
            return $results;
        }
        return [];
    }

    public function load($id, $t = 'id') {
        if ($t == 'id') {
            $link = $this->findById($id);
        } else {
            $link = $this->findBySlug($id);
        }
        if ($link) {
            foreach ($link as $k => $v) {
                if (array_key_exists($k, $this->obj)) {
                    $this->obj[$k] = $v;
                }
            }
            $this->obj['altLinks'] = $this->getAltLinks($this->getID());
            return true;
        }
        return false;
    }

    public function getAltLinks($t = '') {
        if (!empty($t)) {
            $this->db->where('parent_id', $t);
        }
        $this->db->orderBy('_order', 'asc');
        $results = $this->db->get($this->altTbl);
        if ($this->db->count > 0) {
            return $results;
        }
        return [];
    }

    public function getObj() {
        return $this->obj;
    }

    public function refresh($alt = false, $s = '__001') {
        if ($this->isEdit()) {
            $this->error = '';
            if ($s == '__001') {
                $this->set($alt);
            } else {
                $this->setY($alt);
            }
            $this->save();
        }
    }

    public function delete($id) {
        if ($this->beforeDel($id)) {
            $this->db->where('id', $id);
            if ($this->db->delete($this->tbl)) {
                return true;
            }
        }
        return false;
    }

    public function beforeDel($id) {
        if (!$this->isAlt()) {
            $this->delAllAlts($id);
        }
        //del backup links
        $backupDrive = new BackupDrives;
        $myHLS = new HlsLink;
        if ($backupDrive->delDriveAll('', $id)) {
            //del hls
            if ($myHLS->delHlsAll($id)) {
                return true;
            }
        }
        return false;
    }

    protected function delAllAlts($pid) {
        $this->db->where('parent_id', $pid);
        if ($this->db->delete($this->altTbl)) {
            return true;
        }
        return false;
    }

    public function multiDelete($ids) {
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->delete($id);
            }
            return true;
        }
        return false;
    }

    public function viewed() {
        if ($this->isEdit()) {
            
            if($this->isAlt()){
               
                $pId = $this->obj['parent_id'];
                $this->switch('main');
                $this->load($pId);
            }
            
            
                             $this->db->where('id', $this->getID());
            $this->db->update($this->tbl, ['views' => $this->obj['views'] + 1], 1);
           
        }
    }

    public function getDTY() {
        $links = $this->db->rawQuery('SELECT type, count(1) as c From links Group by type');
        $resp = ['GDrive' => 0, 'GPhoto' => 0, 'OneDrive' => 0, 'Yandex' => 0, 'Direct' => 0, 'OkRu' => 0];
        if (!empty($links) && is_array($links)) {
            foreach ($links as $l) {
                if (array_key_exists($l['type'], $resp)) {
                    $resp[$l['type']] = number_format($l['c']);
                }
            }
        }
        return $resp;
    }

    public function getRDT() {
        $links = $this->db->rawQuery('SELECT status, count(1) as c From links Group by status');
        $resp = ['active' => 0, 'inactive' => 0, 'broken' => 0];
        if (!empty($links) && is_array($links)) {
            foreach ($links as $l) {
                switch ($l['status']) {
                    case '0':
                        $resp['active'] = number_format($l['c']);
                    break;
                    case '1':
                        $resp['inactive'] = number_format($l['c']);
                    break;
                    case '2':
                        $resp['broken'] = number_format($l['c']);
                    break;
                }
            }
        }
        return $resp;
    }

    protected function s($u) {
        if ($this->isEdit() && Helper::isDrive($u)) {
            if ($this->obj['type'] == 'GDrive') {
                $o_gid = Helper::getDriveId($this->obj['main_link']);
                $n_gid = Helper::getDriveId($u);
                if ($o_gid != $n_gid) {
                    $this->t = true;
                }
            }
        } else {
            if (!$this->isEdit()) {
                $this->t = true;
            }
        }
    }
    
    public function getTotalLinks() {
        $this->db->get($this->tbl);
        return $this->db->count;
    }

    public function getTotalViews() {
        $stats = $this->db->getOne($this->tbl, "sum(views)");
        return (isset($stats['sum(views)'])) ? $stats['sum(views)'] : 0;
    }

    public function getMostViewed() {
        $this->db->where("status", 2, "!=");
        $this->db->orderBy("views", "desc");
        $results = $this->db->get($this->tbl, 10);
        if ($this->db->count > 0) {
            return $results;
        } else {
            return [];
        }
    }

    public function getRecentlyAdded() {
        $this->db->where("status", 2, "!=");
        $this->db->orderBy("created_at", "desc");
        $results = $this->db->get($this->tbl, 10);
        if ($this->db->count > 0) {
            return $results;
        } else {
            return [];
        }
    }
    
}
