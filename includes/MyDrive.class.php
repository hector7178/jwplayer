<?php
class MyDrive {

    
    protected $accessToken = null;
    protected $error = '';
    protected $baseURI = 'https://www.googleapis.com/drive';
    protected $rootId;


    public function __construct($acc_id = '') {
        $gauth = new GAuth($acc_id);
        if ($at = $gauth->getAccessToken()) {
            $this->accessToken = $at;
            $this->rootId = $this->getRootId();
        } else {
            $this->error = 'GAUTH ERROR : ' . $gauth->getError();
        }
    }

    public function getAccountInfo() {
        return $this->getData('v2/about');
    }

    public function getAllFiles($folderId = '', $nextPageToken = '', $pageSize = MAX_GDRIVE_FILES_PER_PAGE) {
        if (empty($folderId)) $folderId = $this->getRootId();
        $inFolder = !empty($folderId) ? "and '" . $folderId . "' in parents" : '';
        $orderBy = 'folder,name';
        $fields = "nextPageToken, files(id,name,mimeType,createdTime,modifiedByMeTime,shared)";
        $q = ['q' => "(mimeType = 'application/vnd.google-apps.folder' or mimeType contains 'video/') and
             trashed = false   " . $inFolder, 'pageSize' => $pageSize, 'spaces' => 'drive', 'fields' => $fields, 'orderBy' => $orderBy, 'pageToken' => ''];
        if (!empty($nextPageToken)) $q['pageToken'] = $nextPageToken;
        return $this->getData('v3/files', $q);
    }

    public function getFile($fileId, $fields = ['*']) {
        $q = ['fields' => implode(',', $fields) ];
        return $this->getData('v3/files/' . $fileId, $q);
    }

    public function getError() {
        return $this->error;
    }

    protected function getData($rurl, $q = [], $headers = [], $type = 'get', $data = []) {
        $q = !empty($q) && is_array($q) ? '?' . http_build_query($q) : '';
        $headers[] = 'Content-Type: application/json';
        $headers[] = "Authorization: Bearer " . $this->accessToken;
        $results = Helper::curl($this->baseURI . '/' . $rurl . $q, $headers, $type, $data);
        if (Helper::isJson($results)) {
            $results = json_decode($results, true);
            if (!isset($results['error'])) {
                return $results;
            } else {
                if (!empty($results['error']['message'])) {
                    $this->error = 'GDrive API Error : ' . $results['error']['message'];
                } else {
                    $this->error = 'GDrive API Error : Unknown error !';
                }
            }
        }
        return [];
    }

    public function getRootId() {
        $results = $this->getAccountInfo();
        if (!empty($results)) {
            return $results['rootFolderId'];
        }
        return '';
    }

    public static function isFolder($mime) {
        if (strpos($mime, 'application/vnd.google-apps.folder') !== false) {
            return true;
        }
        return false;
    }

    public function getPermissionList($fileId, $fields = ['*']) {
        $q = ['fields' => implode(',', $fields) ];
        return $this->getData('v3/files/' . $fileId . '/permissions', $q);
    }

    public function getPermission($fileId, $permissionId, $fields = ['*']) {
        $q = ['fields' => implode(',', $fields) ];
        return $this->getData('v3/files/' . $fileId . '/permissions/' . $permissionId, $q);
    }

    public function deletePermission($fileId, $permissionId) {
        $url = 'v3/files/' . $fileId . '/permissions/' . $permissionId;
        return empty($this->getData($url, [], [], 'delete')) ? true : false;
    }

    public function deleteSharedPermission($fileId) {
        $permissions = $this->getPermissionList($fileId, ['permissions']);
        if (!empty($permissions) && isset($permissions['permissions'])) {
            $permissions = $permissions['permissions'];
            foreach ($permissions as $permission) {
                if ($permission['role'] != 'owner') {
                    $this->deletePermission($fileId, $permission['id']);
                }
            }
        }
        return true;
    }

    public function insertPermisson($fileId) {
        $data = ['role' => 'reader', 'type' => 'anyone'];
        $results = $this->getData('v3/files/' . $fileId . '/permissions', [], [], 'post', $data);
        return !empty($results) ? true : false;
    }

    public function changeSharedPermission($fileId, $m) {
        $result = false;
        if ($m == 'shared') {
            $result = $this->deleteSharedPermission($fileId);
        }
        if ($m == 'not-shared') {
            $result = $this->insertPermisson($fileId);
        }
        return $result;
    }

    public function getParentFolderList($fileId = '') {
        $folders = [];
        $isOk = true;
        if (!empty($fileId)) {
            while ($isOk) {
                $folder = $this->getFile($fileId, ['id', 'name', 'parents']);
                if (!empty($folder) && isset($folder['parents'][0])) {
                    $fileId = $folder['parents'][0];
                    if ($fileId == $this->rootId) $isOk = false;
                    $folders[] = ['id' => $folder['id'], 'name' => $folder['name']];
                } else {
                    $isOk = false;
                }
            }
        }
        $folders[] = ['id' => $this->rootId, 'name' => 'My Drive'];
        krsort($folders);
        return $folders;
    }

    public function createFolder($folderName, $parentId = '') {
        $data = ["name" => $folderName, "mimeType" => 'application/vnd.google-apps.folder'];
        if (!empty($parentId)) $data['parents'] = [$parentId];
        $results = $this->getData('v3/files', ['fields' => 'id'], [], 'post', $data);
        return !empty($results) && isset($results['id']) ? $results['id'] : false;
    }

    public function deleteFile($fileId) {
        return $this->getData('v3/files/' . $fileId, [], [], 'delete');
    }

    public function uploadFile($url) {
    }

    public function hasError() {
        return !empty($this->getError()) ? true : false;
    }

}
