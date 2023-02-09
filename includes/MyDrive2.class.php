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


class MyDrive2 {

    protected $accessToken = null;
    protected $error = '';
    protected $baseURI = 'https://www.googleapis.com/drive';
    protected $rootId;
    protected $client, $service = null;

    public function __construct($acc_id) {
        $gauth = new GAuth($acc_id);
        if ($at = $gauth->getAccessToken()) {
            $this->accessToken = $at;
            $this->rootId = $this->getRootId();
            //set client
            $this->client = new Google_Client();
            $this->client->setAccessToken($this->accessToken);
            //set client serivece
            $this->service = new Google_Service_Drive($this->client);
        } else {
            $this->error = 'GAUTH ERROR : ' . $gauth->getError();
        }
    }

    public function getAccessToken() {
        return $this->accessToken;
    }

    public function getAccountInfo() {
        return $this->getData('v2/about');
    }

    public function getRootId() {
        $results = $this->getAccountInfo();
        if (!empty($results)) {
            return $results['rootFolderId'];
        }
        return '';
    }

    public function shareMyFile($fileId, $email) {
        try {
            $data = ['type' => 'user', 'role' => 'writer', 'emailAddress' => $email];
            $userPermission = new Google_Service_Drive_Permission($data);
            $this->service->permissions->create($fileId, $userPermission, array('fields' => 'id', 'sendNotificationEmail' => false));
            return true;
        }
        catch(Google_Service_Exception $e) {
            $this->error = $e->getErrors() [0]['message'];
            return false;
        }
    }

    public function makeBackup($fileId, $backupDrives = []) {
        $errors = $sEmails = $results = [];
        if (!empty($backupDrives) && is_array($backupDrives)) {
            foreach ($backupDrives as $bk => $bkV) {
                $sEmails[] = $bkV['email'];
                //share file
                if ($this->shareMyFile($fileId, $bkV['email'])) {
                    $tmpDrive = new $this($bk);
                    if (!$tmpDrive->hasError()) {
                        if ($cId = $tmpDrive->makeCopy($fileId)) {
                            $results[] = ['accId' => $bk, 'fileId' => $cId];
                        } else {
                            $errors[] = 'Can not copy this file ! <b>' . $bkV['email'] . '</b> ' . $tmpDrive->getError();
                        }
                    } else {
                        $errors[] = 'Drive connection failed to <b>' . $bkV['email'] . '</b> ';
                    }
                } else {
                    $errors[] = 'Can not to share file to <b>' . $bkV['email'] . '</b> -> Reason : ' . $this->getError();
                    $this->cleanError();
                }
            }
            //finally rmeove shared files
            $file = $this->getFile($fileId, "permissions(emailAddress,id)");
            if (!empty($file)) {
                $permissions = $file->getPermissions();
                if (!empty($permissions) && is_array($permissions) && !empty($sEmails)) {
                    foreach ($permissions as $perm) {
                        if (in_array($perm->getEmailAddress(), $sEmails)) {
                            $this->removePermission($fileId, $perm->getId());
                        }
                    }
                }
            }
        }
        if (!empty($errors)) {
            $this->error = json_encode($errors);
        }
        return $results;
    }

    public function cleanError() {
        $this->error = '';
    }

    public function makeCopy($fileId) {
        try {
            $file = new Google_Service_Drive_DriveFile();
            $results = $this->service->files->copy($fileId, $file, ['fields' => 'id']);
            return $results->getId();
        }
        catch(Google_Service_Exception $e) {
            $this->error = 'An error occurred: ' . $e->getErrors() [0]['message'];
        }
        return false;
    }

    public function removePermission($fileId, $pId) {
        try {
            $this->service->permissions->delete($fileId, $pId);
            return true;
        }
        catch(Google_Service_Exception $e) {
            // $e = 'An error occurred: ' . $e->getErrors()[0]['message'];
            return false;
        }
    }

    public function getFile($fileId, $fields = "*") {
        try {
            $optParams = array('fields' => $fields,);
            return $this->service->files->get($fileId, $optParams);
        }
        catch(Google_Service_Exception $e) {
            $this->error = 'An error occurred: ' . $e->getErrors() [0]['message'];
        }
        return false;
    }

 


    public function delete($fileId) {
        try {
            $this->service->files->delete($fileId);
            return true;
        }
        catch(Google_Service_Exception $e) {
            $this->error = 'An error occurred: ' . $e->getErrors() [0]['message'];
        }
        return false;
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

    public function uploadFile($url, $parentId = '', $filename = '') {
        session_write_close();
        // ignore_user_abort(true);
        set_time_limit(0);
        $uploadedFileId = 0;
        $chunkSizeBytes = DRIVE_UPLOAD_CHUNK;
        $status = false;
        $sizeUploaded = 0;
        $metaData = Helper::getRemoteFileData($url);
        if (empty($filename)) $filename = 'My File';
        if (isset($metaData['size']) && isset($metaData['mime'])) {
            $mimeType = $metaData['mime'];
            $size = $metaData['size'];
            if (isset($metaData['_url'])) $url = $metaData['_url'];
            $extension = Helper::mime2ext($mimeType);
            if (!empty($extension)) {
                $opt = !empty($parentId) ? ['parents' => [$parentId]] : '';
                $file = new Google_Service_Drive_DriveFile($opt);
                //set meta data
                $file->name = $filename . '.' . $extension;
                $this->client->setDefer(true);
                $request = $this->service->files->create($file);
                $media = new Google_Http_MediaFileUpload($this->client, $request, $mimeType, null, true, $chunkSizeBytes);
                $media->setFileSize($size);
                try {
                    $handle = @fopen($url, 'rb');
                    if (!empty($handle)) {
                        $isOk = true;
                        $this->uploaded = 0;
                        while (!$status && !feof($handle)) {
                            # Read until you get $chunkSizeBytes from the file
                            $chunk = Helper::readFileChunk($handle, $chunkSizeBytes);
                            $chunkSizee = strlen($chunk);
                            $sizeUploaded+= $chunkSizee;
                            $sizeMissing = $size - $sizeUploaded;
                            $status = $media->nextChunk($chunk);
                            $uploaded = $media->getProgress();
                            $p = ($uploaded / $size) * 100;
                            $d = ['status' => 'processing', 'progress' => round($p) ];
                            if ($this->uploaded < $uploaded) {
                                @file_put_contents($this::uniqFile(), json_encode($d));
                                $this->uploaded = $uploaded;
                            }
                            if (!file_exists($this::uniqFile())) {
                                $this->error = 'Process status saving faield !';
                                break;
                            }
                        }
                        fclose($handle);
                        if ($status !== false) {
                            $uploadedFileId = $status['id'];
                            $d = ['status' => 'success', 'fileId' => $uploadedFileId];
                            @file_put_contents($this::uniqFile(), json_encode($d));
                        } else {
                            $this->error = 'Unable to upload this file !';
                        }
                    } else {
                        $this->error = 'Can not access this file !';
                    }
                }
                catch(Google_Service_Exception $e) {
                    $this->error = 'An error occurred: ' . $e->getErrors() [0]['message'];
                }
            } else {
                $this->error = 'Invalid file extension !';
            }
        } else {
            $this->error = 'Can not access this file !';
        }
        if ($this->hasError() && empty($uploadedFileId)) {
            $d = ['status' => 'failed', 'error' => $this->error];
            @file_put_contents($this::uniqFile(), json_encode($d));
        }
        return $uploadedFileId;
    }

    public static function uniqFile() {
        return ROOT . '/data/tmp/drive-upload-processing.txt';
    }

    public function download($_key = '', $saveProgress = false) {
        
    }
    
    public function hasError() {
        return !empty($this->getError()) ? true : false;
    }

    public function getError() {
        return $this->error;
    }

}
