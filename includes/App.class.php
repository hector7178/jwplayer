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


class App {

    /**
     * Application Variables
     * @since 1.3
     *
     */
    protected $db;
    protected $config;
    protected $actions = ['dashboard', 'links', 'video', 'api', 'servers', 'ajax','driveFix', 'bulk', 'mydrive', 'ads', 'settings', 'profile','login', 'logout'];
    protected $action;
    protected $alerts = [];
    protected $data = [];
    protected $videoType;

    /**
     * User Variables
     * @since 1.3
     *
     */
    protected $logged = false;
    protected $hasAccess = false;
    protected $isAdmin = false;
    protected $userId = NULL;

    /**
     * Constructor: Checks logged user status
     * @since 1.3
     *
     */


    public function __construct() {
        $this->db = Database::getInstance();
        $this->config = Database::getConfig();
    }

    /**
     * Run Applicatioin
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function run() {
        if (isset($_GET['a']) && !empty($_GET['a'])) {
            $this->setup();
            $var = explode('/', $_GET['a']);
            $var[0] = str_replace('.', '', $var[0]);
            $this->action = Helper::clean($var[0]);
            unset($var[0]);
            $this->resolveCustomSlugs();
            if (in_array($this->action, $this->actions)) {
                $this->check();
                if (method_exists($this, $this->action)) {
                    return call_user_func_array([$this, $this->action], $var);
                } else {
                    //method does not exist
                    die('This method is does not exists in app !');
                }
            } else {
                //page not found
                $this->_404();
            }
        }
        return $this->home();
    }

    /**
     * Check user permission
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function check() {
        $public = ['login', 'video', 'api', 'i'];
        if (!$this->logged) {
            if (in_array($this->action, $public)) {
                $this->hasAccess = true;
            } else {
                $this->_400();
            }
        } else {
            $this->hasAccess = true;
        }
       
    }

    /**
     * Setup application data
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function setup() {
        if (isset($_SESSION['alerts'])) {
            $this->alerts = $_SESSION['alerts'];
            unset($_SESSION['alerts']);
        }
        if (isset($_SESSION['logged']) && $_SESSION['logged'] == 1) {
            $user = new User();
            $user = $user->findByUsername($_SESSION['user']);
            if (!empty($user)) {
                //we have only admin user
                $this->logged = true;
                $this->userImg = $user['img'];
            }
        }
        $this->actions[] = Helper::getCusteomSlugs();

    }

    /**
     * Resolve custom slugs issues
     * @author CodySeller <https://codyseller.com>
     * @since 1.4
     */
    protected function resolveCustomSlugs() {
        $videoSlug = $this->getSlug('playerSlug');
        $cslugs = [$videoSlug => 'video'];
        if (array_key_exists($this->action, $cslugs)) {
            $this->action = $cslugs[$this->action];
        }
    }

    /**
     * Get custom slug
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function getSlug($slug) {
        $default = ['videoSlug' => 'video'];
      
        return !empty($this->config[$slug]) ? $this->config[$slug] : $default[$slug];
    }

    /**
     * Home page
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function home() {
        $this->display('home', true);
    }

    /**
     * Drive explorer page
     * @author CodySeller <https://codyseller.com>
     * @since 2.3
     */
    protected function mydrive($id = '') {
        $driveId = Helper::getReqData('driveId', 'GET');
        $files = [];
        $parentFolders = [['id' => 0, 'name' => 'My Drive']];
        $driveConnection = $isRoot = false;
        $filter = Helper::getReqData('filter', 'GET');
        $preBackUri = '';
        $filter = !empty($filter) ? 1 : 0;
        $nextPageToken = Helper::getReqData('nextPageToken', 'GET');
        if (empty($driveId)) {
            $driveId = $this->getFirstDriveAccountId();
            if(!empty($driveId)) Helper::redirect('mydrive?driveId=' . $driveId);
            
        }
        if ($this->isDriveExist($driveId) !== false) {
            $mydrive = new MyDrive($driveId);
            if (!$mydrive->hasError()) $driveConnection = true;
            if (Helper::isPost()) {
                $type = Helper::getReqData('type');
                switch ($type) {
                    case 'new-folder':
                        $folderName = Helper::getReqData('folder');
                        if (!empty($mydrive->createFolder($folderName, $id))) {
                            $this->addAlert("<b>$folderName<b> folder created successfully !", 'success');
                        } else {
                            $this->addAlert("Can't create <b>$folderName<b> folder ! ", 'danger');
                            $this->addAlert($mydrive->getError(), 'danger');
                        }
                    break;
                }
                $this->saveAlerts();
                Helper::redirect('self');
            }
            $parentFolders = $mydrive->getParentFolderList($id);
            $isRoot = empty($id) ? true : false;
            if (!$isRoot && is_array($parentFolders) && count($parentFolders) > 1) {
                $preFolderId = $parentFolders[array_key_last($parentFolders) + 1]['id'];
            }
            $respFiles = $mydrive->getAllFiles($id, $nextPageToken);
            $files = isset($respFiles['files']) ? $respFiles['files'] : [];
            if (isset($respFiles['nextPageToken']) && !empty($respFiles['nextPageToken'])) {
                $nextPageToken = $respFiles['nextPageToken'];
            }
            if ($mydrive->hasError()) {
                $this->addAlert($mydrive->getError(), 'danger');
            }
        } else {
            if (empty($driveId)) {
                $this->addAlert('Google drive accounts does not exist !', 'danger');
            } else {
                $this->_404();
            }
        }
        if (!empty($preFolderId)) {
            if ($preFolderId == $mydrive->getRootId()) $preFolderId = '';
            $preBackUri = PROOT . '/mydrive/' . $preFolderId . '?driveId=' . $driveId;
        }
        $driveAccounts = $this->getDriveAccounts(true);
        if (file_exists(MyDrive2::uniqFile())) {
            unlink(MyDrive2::uniqFile());
        }
        $this->addData($driveId, 'activeDriveId');
        $this->addData($files, 'files');
        $this->addData($driveConnection, 'driveConnection');
        $this->addData($parentFolders, 'parentFolders');
        $this->addData($driveAccounts, 'driveAccounts');
        $this->addData($nextPageToken, 'nextPageToken');
        $this->addData($isRoot, 'isRoot');
        $this->addData($filter, 'filter');
        $this->addData($id, 'folderId');
        $this->addData($preBackUri, 'preBackUri');
        $this->display('mydrive');
    }

    /**
     * Dashboard page
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function dashboard() {
        $this->setTitle('Dashboard | GDplyr Application');
        $this->analyze();

        $this->display('dashboard');
    }

    /**
     * Profile page
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function profile() {
        $this->setTitle('Admin profile | ' . APP_NAME . ' Application');
        $user = new User();
        $user->load($this->config['adminId']);
        if (Helper::isPost()) {
            $username = Helper::getReqData('username');
            $password = Helper::getReqData('password');
            $confirmPassword = Helper::getReqData('confirm_passsword');
            $img = Helper::getReqData('image');
            if (empty($username)) {
                $this->addAlert('Username is required !', 'danger');
            } else {
                if ($username != $user->obj['username']) {
                    $isNewU = true;
                }
            }
            if (!empty($password)) {
                if (empty($confirmPassword)) {
                    $this->addAlert('Confirm password is required !', 'danger');
                } else {
                    if ($password != $confirmPassword) {
                        $this->addAlert('Password does not matched !', 'danger');
                    }
                }
            }
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $piname = $_FILES['image']['name'];
                $pitmp = $_FILES['image']['tmp_name'];
                $imgDir = "/uploads/";
                if (!file_exists(ROOT . $imgDir)) {
                    $this->addAlert("Profile image upload failed ! -> <b>{$imgDir}</b> folder does not exist . ", 'warning');
                } else {
                    if (!is_writable(ROOT . $imgDir)) {
                        $this->addAlert("Profile image upload failed ! -> <b>{$imgDir}</b> folder is not writable . ", 'warning');
                    } else {
                        $upname = Helper::uploadImg($piname, $pitmp, $imgDir);
                        if (!$upname) {
                            $this->addAlert("Profile image upload failed. -> Invalid file format !", 'warning');
                        } else {
                            $img = $upname;
                        }
                    }
                }
            }
            if (!$this->hasAlerts()) {
                $data = ['username' => $username, 'img' => $img];
                if (!empty($password)) {
                    $hasedPass = password_hash($password, PASSWORD_DEFAULT);
                    $data['password'] = $hasedPass;
                }
                if ($user->assign($data)->save()) {
                    if ($isNewU) {
                        $_SESSION['user'] = $username;
                    }
                    $this->addAlert('Saved changes successfully !', 'success');
                    $this->saveAlerts();
                    Helper::redirect('profile');
                }
            }
        }
        $this->addData($user->obj, 'user');
        $this->display('profile');
    }
    
    /**
     * Login page
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function login() {
        echo password_hash("1234", PASSWORD_DEFAULT);
        if ($this->logged) {
            Helper::redirect('dashboard');
        }
        if (Helper::isPost()) {
            $username = Helper::getReqData('username');
            $password = Helper::getReqData('password');
            $user = new User();
            $user = $user->findByUsername($username);
            if (!empty($user)) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['logged'] = 1;
                    Helper::redirect('dashboard');
                } else {
                    $this->addAlert('Invalid Password !', 'danger');
                }
            } else {
                $this->addAlert('Invalid Username !', 'danger');
            }
        }
        $this->display('login', true);
    }

    /**
     * User logout
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    public function logout() {
        if (isset($_SESSION["logged"])) unset($_SESSION["logged"]);
        if (isset($_SESSION["user"])) unset($_SESSION["user"]);
        Helper::redirect('login');
    }
    
    /**
     * Analyze data
     * @author CodySeller <https://codyseller.com>
     * @since 1.4
     */
    protected function analyze() {
        $link = new Link();
        $server = new Server();
        $proxy = new Proxy();
        $tl = $link->getTotalLinks();
        $tv = $link->getTotalViews();
        $bl = number_format(count($link->getAll('broken')));
        $serL = $server->getAll();
        $serl = count($serL);
        $diffT = $link->getDTY();
        $reffT = $link->getRDT();
        $sdPN = [];
        $sdPV = [];
        $t = $tv;
        $drSize = Helper::GetDirectorySize(ROOT . '/data/cache/');
        $sdPN[] = 'Main server';
        $sdPV[] = 0;
        foreach ($serL as $s) {
            $sdPN[] = $s['name'];
            $pb = $s['playbacks'];
            if ($pb != 0 && $tv != 0) {
                $pb = round(($pb / $tv) * 100);
            }
            $sdPV[] = $pb;
            $t-= $s['playbacks'];
        }
        if ($t != 0 && $tv != 0) {
            $t = round(($t / $tv) * 100);
        }
        $sdPV[0] = $t;
        $activeProxy = $proxy->getProxyList();
        $brokenProxy = $proxy->getProxyList('broken');
        $nap = !empty($activeProxy) && is_array($activeProxy) ? count($activeProxy) : 0;
        $nbp = !empty($brokenProxy) && is_array($brokenProxy) ? count($brokenProxy) : 0;
        $mal = $link->getMostViewed();
        $ral = $link->getRecentlyAdded();
        $driveAccounts = $this->getDriveAccounts();
        $data = ['totalLinks' => $tl, 'driveAccounts' => count($driveAccounts), 'totalViews' => $tv, 'brokenLinks' => $bl, 'maLinks' => $mal, 'raLinks' => $ral, 'totalServers' => $serl, 'dft' => $diffT, 'rft' => $reffT, 'serL' => [$sdPV, $sdPN], 'drSize' => $drSize, 'proxy' => [$nap, $nbp], 'gauths' => $this->getGDA() ];
        $this->addData($data, 'data');
    }

    /**
     * Get gdrive counter
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function getGDA() {
        $auths = $this->db->rawQuery('SELECT status, count(1) as c From drive_auth Group by status');
        $resp = ['active' => 0, 'broken' => 0];
        if (!empty($auths) && is_array($auths)) {
            foreach ($auths as $a) {
                switch ($a['status']) {
                    case '0':
                        $resp['active'] = $a['c'];
                    break;
                    case '1':
                        $resp['broken'] = $a['c'];
                    break;
                }
            }
        }
        return $resp;
    }

    protected function driveFix($email =''){

        $e = '';

        if(!empty($email)){

            $driveAccounts = $this->getDriveAccounts();

            if(!empty($driveAccounts)){

                foreach($driveAccounts as $d => $v){
                    if($email == $v['email']){
                        $accId = $d;
                    }
                }

                if(isset($accId)){

                    if($this->db->update('links',['acc_id'=>$accId])){
                        die('Drive accounts setuped successfully !');
                    }
        
                }else{
                    $e = 'Drive account not found !';
                }

            }else{
                $e = 'Active drive accounts not found !';
            }

        }else{
            $e = 'set email address !';
        }
dnd($e);


    }

    /**
     * Settings page
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function settings($action = '', $sAction = '', $id = '') {
        if (!empty($action)) {
            switch ($action) {
                case 'gdrive-accounts':
                    $driveAccounts = $this->getDriveAccounts(true);
                    if ($sAction == 'del') {
                        $links = new Link;
                        $results = $links->getByAccId($id);
                        if (empty($results)) {
                            $email = $driveAccounts[$id]['email'];
                            $gauth = new GAuth;
                            if ($gauth->delByAccEmail($email)) {
                                if ($this->deleteDriveAccount($id)) {
                                    $this->addAlert('Account deleted successfully!', 'success');
                                } else {
                                    $this->addAlert('unable to delete this account !', 'danger');
                                }
                            } else {
                                $this->addAlert('unable to delete this gauth related with this account !', 'danger');
                            }
                        } else {
                            $this->addAlert('Firstly delete all links related with this account !', 'danger');
                        }
                        $this->saveAlerts();
                        Helper::redirect('settings/gdrive-accounts');
                    }
                    if ($sAction == 'status') {
                        $id = Helper::clean($id);
                        if (!empty($id)) {
                            $driveAccounts = $this->getDriveAccounts();
                            if (array_key_exists($id, $driveAccounts)) {
                                if (isset($driveAccounts[$id]['is_paused']) && $driveAccounts[$id]['is_paused'] == 1) {
                                    $driveAccounts[$id]['is_paused'] = 0;
                                } else {
                                    $driveAccounts[$id]['is_paused'] = 1;
                                }
                            }
                            $this->updateSettings(['driveAccounts' => json_encode($driveAccounts) ]);
                        }
                        Helper::redirect('settings/gdrive-accounts');
                    }
                    $this->addData($driveAccounts, 'driveAccounts');
                    $this->display('gdrive-accounts');
                break;
                case 'download':
                    $this->display('download-settings');
                break;
                case 'video':
                    if (Helper::isPost()) {
                        $player = Helper::getReqData('player');
                        $playerSlug = str_replace(' ', '', Helper::getReqData('playerSlug'));
                        $isAdblocker = Helper::getReqData('isAdblocker') == 'on' ? 1 : 0;
                        $vPreloader = Helper::getReqData('v_preloader') == 'on' ? 1 : 0;
                        $streamRand = Helper::getReqData('streamRand') == 'on' ? 1 : 0;
                        $autoPlay = Helper::getReqData('autoPlay') == 'on' ? 1 : 0;
                        $jsLicense = Helper::getReqData('jw_license');
                        $defaultVideo = Helper::getReqData('default_video');
                        $defaultBanner = Helper::getReqData('default_banner');
                        $disabledQ = Helper::getReqData('dq');
                        if (in_array($playerSlug, $this->actions) && $playerSlug != 'video') {
                            $playerSlug = 'video';
                            $this->addAlert("You can not use this slug. choose another one !", 'warning');
                        }
                        if (!in_array($player, ['jw', 'plyr'])) $player = 'jw';
                        if (empty($playerSlug)) $playerSlug = 'video';
                        $allowedQ = [360, 480, 720, 1080];
                        if (!empty($disabledQ) && is_array($disabledQ)) {
                            foreach ($disabledQ as $qK => $q) {
                                if (!in_array($q, $allowedQ)) {
                                    unset($disabledQ[$qK]);
                                }
                            }
                        }
                        $disabledQ = !empty($disabledQ) ? json_encode($disabledQ) : '';
                        $data = ['disabledQualities' => $disabledQ, 'streamRand' => $streamRand, 'autoPlay' => $autoPlay, 'default_banner' => $defaultBanner, 'player' => $player, 'playerSlug' => $playerSlug, 'default_video' => $defaultVideo, 'isAdblocker' => $isAdblocker, 'v_preloader' => $vPreloader, 'jw_license' => $jsLicense];
                        $this->updateSettings($data);
                        $this->addAlert('Settings saved successfully !', 'success');
                        $this->saveAlerts();
                        Helper::redirect('settings/video');
                    }
                    $disabledQulities = Helper::getDisabledQualities();
                    $this->addData($disabledQulities, 'disabledQulities');
                    $this->display('player-settings');
                    break;
                case 'general':
                    $this->setTitle('General Settings | ' . APP_NAME . ' Application');
                    if (helper::isPost()) {
                        $logo = Helper::getReqData('logo');
                        $favicon = Helper::getReqData('favicon');
                        $darkTheme = Helper::getReqData('dark_theme') == 'on' ? 1 : 0;
                        $timezone = Helper::getReqData('timezone');
                        $sublist = Helper::getReqData('sublist');
                        $altR = Helper::getReqData('altR');
                        if (is_array($altR)) {
                            $altR = json_encode($altR);
                        } else {
                            $altR = '';
                        }
                        if (!empty($sublist)) {
                            $sublist = str_replace(' ', '', strtolower($sublist));
                            $sublist = explode(',', $sublist);
                        } else {
                            $sublist = [];
                        }
                        $sublist = json_encode($sublist);
                        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                            $piname = $_FILES['logo']['name'];
                            $pitmp = $_FILES['logo']['tmp_name'];
                            $upname = Helper::uploadImg($piname, $pitmp);
                            if (!$upname) {
                                $this->addAlert("Logo image upload failed. -> Invalid file format !", 'warning');
                            } else {
                                $logo = $upname;
                            }
                        } else {
                            if (!empty($this->config['logo']) && empty($logo)) {
                                if (file_exists(ROOT . '/uploads/' . $this->config['logo'])) {
                                    unlink(ROOT . '/uploads/' . $this->config['logo']);
                                }
                                $logo = '';
                            }
                        }
                        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
                            $piname = $_FILES['favicon']['name'];
                            $pitmp = $_FILES['favicon']['tmp_name'];
                            $upname = Helper::uploadImg($piname, $pitmp);
                            if (!$upname) {
                                $this->addAlert("Favicon upload failed. -> Invalid file format !", 'warning');
                            } else {
                                $favicon = $upname;
                            }
                        } else {
                            if (!empty($this->config['favicon']) && empty($logo)) {
                                if (file_exists(ROOT . '/uploads/' . $this->config['favicon'])) {
                                    unlink(ROOT . '/uploads/' . $this->config['favicon']);
                                }
                                $favicon = '';
                            }
                        }
                        $data = ['altR' => $altR, 'sublist' => $sublist, 'timezone' => $timezone, 'dark_theme' => $darkTheme, 'logo' => $logo, 'favicon' => $favicon];
                        $this->updateSettings($data);
                        $this->addAlert('Settings saved successfully !', 'success');
                        $this->saveAlerts();
                        Helper::redirect('settings/general');
                    }
                    $altR = Helper::getAltR();
                    $this->addData($altR, 'altR');
                    $this->display('general-settings');
                    break;
                case 'backup':
                    $this->setTitle('Backup | ' . APP_NAME . ' Application');
                    if (isset($_GET['i'])) {
                        $this->updateSettings(['last_backup' => Helper::tnow() ]);
                        Helper::exportDB();
                        Helper::redirect('settings/backup');
                    }
                    $driveAccounts = $this->getDriveAccounts(true);
                    $backupDrive = new BackupDrives;
                    if ($sAction == 'del-backup-links') {
                        if (!empty($id)) {
                            $backupDrives = $this->getDriveAccounts(false, true);
                            if (!array_key_exists($id, $backupDrives)) {
                                $backupDrive->delDriveAll($id);
                            }
                        }
                        Helper::redirect('settings/backup');
                    }
                    if (Helper::isPost()) {
                        $backupDriveIds = Helper::getReqData('backup_drives');
                        $isAutoBackup = Helper::getReqData('auto_drive_backup');
                        if (empty($backupDriveIds) || !is_array($backupDriveIds)) $backupDriveIds = [];
                        if (!empty($driveAccounts)) {
                            foreach ($driveAccounts as $dak => $dav) {
                                if (in_array($dak, $backupDriveIds)) {
                                    $isBackUp = 1;
                                } else {
                                    $isBackUp = 0;
                                }
                                $driveAccounts[$dak]['is_backup'] = $isBackUp;
                            }
                        }
                        $isAutoBackup = $isAutoBackup == 'on' ? 1 : 0;
                        $this->updateSettings(['driveAccounts' => json_encode($driveAccounts), 'isAutoBackup' => $isAutoBackup]);
                        Helper::redirect('settings/backup');
                    }
                    $allDriveAccounts = $this->getDriveAccounts();
                    $activeBackupIds = $backupDrive->getBackupIds();
                    $removedBackupDrives = [];
                    if (!empty($activeBackupIds) && !empty($allDriveAccounts)) {
                        foreach ($activeBackupIds as $ac) {
                            if (array_key_exists($ac['id'], $allDriveAccounts)) {
                                if ($allDriveAccounts[$ac['id']]['is_backup'] == 0) {
                                    $removedBackupDrives[$ac['id']] = $allDriveAccounts[$ac['id']]['email'];
                                }
                            }
                        }
                    }
                    $this->addData($removedBackupDrives, 'removedBackupDrives');
                    $this->addData($driveAccounts, 'driveAccounts');
                    $this->addData(FH::getConfig('isAutoBackup'), 'isAutoBackup');
                    $this->display('backup');
                    break;
                case 'proxy':
                    $this->setTitle('Proxy settings | ' . APP_NAME . ' Application');
                    $proxy = new Proxy();
                    if (Helper::isPost()) {
                        $acpList = helper::getReqData('activeProxy');
                        $bcpList = helper::getReqData('brokenProxy');
                        $proxyUser = helper::getReqData('proxyUser');
                        $proxyPass = helper::getReqData('proxyPass');
                        if (!empty($acpList)) {
                            $acpList = explode(',', str_replace(' ', '', $acpList));
                            if (!$proxy->saveProxy($acpList)) {
                                if ($proxy->hasError()) {
                                    $this->addAlert($proxy->getError(), 'danger');
                                }
                            }
                        } else {
                            $proxy->clear();
                        }
                        if (!empty($bcpList)) {
                            $bcpList = explode(',', str_replace(' ', '', $bcpList));
                            if (!$proxy->saveBrokenProxy($bcpList, 'new')) {
                                if ($proxy->hasError()) {
                                    $this->addAlert($proxy->getError(), 'danger');
                                }
                            }
                        } else {
                            $proxy->clear('broken');
                        }
                        $this->updateSettings(['proxyUser' => $proxyUser, 'proxyPass' => $proxyPass]);
                        helper::redirect('settings/proxy');
                    }
                    $activeProxy = $proxy->getProxyList();
                    $brokenProxy = $proxy->getProxyList('broken');
                    $nap = !empty($activeProxy) && is_array($activeProxy) ? count($activeProxy) : 0;
                    $nbp = !empty($brokenProxy) && is_array($brokenProxy) ? count($brokenProxy) : 0;
                    $activeProxy = !empty($activeProxy) && is_array($activeProxy) ? implode(',' . PHP_EOL, $activeProxy) : '';
                    $brokenProxy = !empty($brokenProxy) && is_array($brokenProxy) ? implode(',' . PHP_EOL, $brokenProxy) : '';
                    $this->addData($activeProxy, 'activeProxy');
                    $this->addData($brokenProxy, 'brokenProxy');
                    $this->addData($nap, 'nap');
                    $this->addData($nbp, 'nbp');
                    $this->display('proxy');
                    break;
                case 'gauth':
                    $this->setTitle('GAuths settings | ' . APP_NAME . ' Application');
                    $gauth = new GAuth();
                    if (!empty($sAction)) {
                        switch ($sAction) {
                            case 'new':
                            case 'edit':
                                $isEdit = false;
                                $auth = ['email' => '', 'client_id' => '', 'client_secret' => '', 'refresh_token' => ''];
                                if ($sAction == 'edit') {
                                    if (!empty($id)) {
                                        $this->db->where('id', $id);
                                        $auth = $this->db->getOne('drive_auth');
                                        $isEdit = true;
                                        if ($this->db->count == 0) {
                                            $this->_404();
                                        } else {
                                            $gauth->clean();
                                            $gauth->loadUser($id);
                                        }
                                    } else {
                                        $this->_404();
                                    }
                                }
                                if (Helper::isPost()) {
                                    $email = Helper::getReqData('email');
                                    $clientId = Helper::getReqData('client_id');
                                    $clientSecret = Helper::getReqData('client_secret');
                                    $refreshToken = Helper::getReqData('refresh_token');
                                    if (empty($clientId)) {
                                        $this->addAlert('Client ID is required !', 'danger');
                                    }
                                    if (empty($clientSecret)) {
                                        $this->addAlert('Client Secret is required !', 'danger');
                                    }
                                    if (empty($refreshToken)) {
                                        $this->addAlert('Refresh Token is required !', 'danger');
                                    }
                                    if (!$this->hasAlerts()) {
                                        $data = ['email' => $email, 'client_id' => $clientId, 'client_secret' => $clientSecret, 'refresh_token' => $refreshToken, 'access_token' => ''];
                                        if (!$isEdit) $gauth->clean();
                                        if ($gauth->assign($data)->save()) {
                                            $id = $gauth->getId();
                                            if (!empty($id)) {
                                                $driveAccounts = $this->getDriveAccounts();
                                                // dnd($driveAccounts);
                                                if (!array_key_exists($id, $driveAccounts)) {
                                                    $a = 0;
                                                    foreach ($driveAccounts as $k => $v) {
                                                        if ($email == $v['email']) {
                                                            if ($v['status'] == 0) {
                                                                // dnd($gauth);
                                                                if ($gauth->updateId($k)) {
                                                                    $id = $k;
                                                                    $this->updateDriveAccount($id, ['status' => 1]);
                                                                } else {
                                                                    $this->addAlert('GAuth update failed with new ID !', 'warning');
                                                                }
                                                            }
                                                            $a = 1;
                                                            break;
                                                        }
                                                    }
                                                    if (!$a && !$this->isDriveExist($id)) $this->setDriveAccount($id, $email);
                                                } else {
                                                    if (!empty($auth['email']) && $auth['email'] != $email) {
                                                        $this->updateDriveAccount($id, ['email' => $email]);
                                                    }
                                                }
                                            }
                                        } else {
                                            $this->addAlert($gauth->getError(), 'danger');
                                        }
                                        Helper::redirect('settings/gauth');
                                    }
                                }
                                $this->addData($auth, 'auth');
                                $this->display('__new-gdrive-auth');
                                break;
                            case 'del':
                                if (!empty($id) && is_numeric($id)) {
                                    $updated = false;
                                    $st = 1;
                                    $gauth->clean();
                                    if ($gauth->loadUser($id)) {
                                        $ac = $gauth->getUser($id);
                                        $accounts = $this->getDriveAccounts();
                                        if ($gauth->delete()) {
                                            if (array_key_exists($id, $accounts)) {
                                                if (!empty($ac) && isset($ac['email'])) {
                                                    $accounts = $gauth->getAccounts('', $ac['email']);
                                                    if (!empty($accounts)) {
                                                        foreach ($accounts as $account) {
                                                            if ($id != $account['id'] && $gauth->loadUser($account['id'])) {
                                                                if ($gauth->updateId($id)) {
                                                                    if ($ac['status'] == 0) $st = 0;
                                                                    $updated = true;
                                                                }
                                                                break;
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                $st = 0;
                                            }
                                        }
                                    }
                                    if ($updated) {
                                        if (!$st) $this->updateDriveAccount($id, ['status' => 1]);
                                    } else {
                                        if ($st) $this->updateDriveAccount($id, ['status' => 0]);
                                    }
                                }
                                Helper::redirect('settings/gauth');
                                break;
                            default:
                                $this->_404();
                                break;
                            }
                        } else {
                            // dnd($this->getDriveAccounts());
                            $gdriveAuths = $this->db->get('drive_auth');
                            // dnd($gauth->loadUser('11'));
                            $this->addData($gdriveAuths, 'auths');
                            $this->display('gdrive-auth');
                        }
                        break;
                    }
                } else {
                    $this->_404();
                }
            }
        protected function getDriveAccounts($active = false, $isBackup = false) {
            $ac = $this->getConfig('driveAccounts');
            if (!empty($ac) && Helper::isJson($ac)) {
                $data = json_decode($ac, true);
                if ($active || $isBackup) {
                    foreach ($data as $ak => $av) {
                        if ($active) {
                            if ($av['status'] == 0) unset($data[$ak]);
                        }
                        if ($isBackup) {
                            if ($av['is_backup'] == 0) unset($data[$ak]);
                        }
                    }
                }
                return $data;
            }
            return [];
        }

        protected function setDriveAccount($id, $email) {
            $driveAccounts = $this->getDriveAccounts();
            if (!array_key_exists($id, $driveAccounts)) {
                $ac = ['email' => $email, 'status' => 1, 'is_backup' => 0, 'is_paused' => 0];
                $driveAccounts[$id] = $ac;
                $this->updateSettings(['driveAccounts' => json_encode($driveAccounts) ]);
            }
            return true;
        }

        protected function updateDriveAccount($id, $data) {
            $driveAccounts = $this->getDriveAccounts();
            if (array_key_exists($id, $driveAccounts)) {
                if (isset($data['email'])) {
                    $driveAccounts[$id]['email'] = $data['email'];
                }
                if (isset($data['status']) && in_array($data['status'], [0, 1])) {
                    $driveAccounts[$id]['status'] = $data['status'];
                }
                $this->updateSettings(['driveAccounts' => json_encode($driveAccounts) ]);
                return true;
            }
            return false;
        }

        public function deleteDriveAccount($id) {
            $driveAccounts = $this->getDriveAccounts();
            if (array_key_exists($id, $driveAccounts)) {
                unset($driveAccounts[$id]);
                $this->updateSettings(['driveAccounts' => json_encode($driveAccounts) ]);
                return true;
            }
            return false;
        }

        protected function isDriveExist($id) {
            $driveAccounts = $this->getDriveAccounts(true);
            if (!empty($driveAccounts) && is_array($driveAccounts)) {
                if (array_key_exists($id, $driveAccounts)) {
                    return $driveAccounts[$id]['email'];
                }
            }
            return false;
        }

        protected function getFirstDriveAccountId() {
            $driveAccounts = $this->getDriveAccounts(true);
            if (!empty($driveAccounts) && is_array($driveAccounts)) {
                return array_key_first($driveAccounts);
            }
            return '';
        }

        protected function getConfig($var) {
            if (array_key_exists($var, $this->config)) {
                return $this->config[$var];
            } else {
                die('Required application configurations are does not exist !');
            }
        }

        /**
         * Ads page
         * @author CodySeller <https://codyseller.com>
         * @since 1.5
         */
        protected function ads($action = '', $id = '') {
            $this->setTitle('Advertisement | ' . APP_NAME . ' Application');
            if (Helper::isPost()) {
                switch ($action) {
                    case 'save-vast':
                        $isEdit = false;
                        $id = Helper::getReqData('id');
                        $title = Helper::getReqData('title');
                        $xml = Helper::getReqData('xml');
                        $type = Helper::getReqData('type');
                        $offset = Helper::getReqData('offset');
                        $skipOffset = Helper::getReqData('skip-offset');
                        if (!empty($id)) {
                            $isEdit = true;
                        }
                        if (empty($xml)) {
                            $this->addAlert('XML file is required !', 'danger');
                        }
                        if (empty($offset)) {
                            $this->addAlert('Offset is required !', 'danger');
                        }
                        if (empty($type)) {
                            $this->addAlert('Ad type is required !', 'danger');
                        }
                        if (!$this->hasAlerts()) {
                            $adcode = ['tag' => $xml, 'offset' => $offset];
                            //nonlinear
                            if ($type != 'nonlinear') {
                                if (empty($skipOffset) || !is_numeric($skipOffset)) {
                                    $skipOffset = 5;
                                }
                                $adcode['skipoffset'] = $skipOffset;
                            } else {
                                $adcode['type'] = 'nonlinear';
                            }
                            $adcode = json_encode($adcode);
                            $data = ['title' => $title, 'type' => 'vast', 'code' => $adcode];
                            if (!$isEdit) {
                                $id = $this->db->insert('ads', $data);
                                if ($id) {
                                    $this->addAlert('VAST Ad Saved Successfully !', 'success');
                                } else {
                                    $this->addAlert('Something went wrong!', 'danger');
                                }
                            } else {
                                $this->db->where('id', $id);
                                if (!$this->db->update('ads', $data)) {
                                    $this->addAlert('Something went wrong!', 'danger');
                                } else {
                                    $this->addAlert('VAST Ad Saved Successfully !', 'success');
                                }
                            }
                            $this->saveAlerts();
                        }
                    break;
                    case 'save-popad':
                        $adcode = $_POST['popads'];
                        if (!empty($adcode)) {
                            $adcode = base64_encode($adcode);
                        }
                        $this->db->where('type', 'popad');
                        $this->db->update('ads', ['code' => $adcode]);
                    break;
                }
                Helper::redirect('ads');
            }

            if ($action == 'del') {
                if (!empty($id) && is_numeric($id)) {
                    $this->db->where('id', $id);
                    $this->db->delete('ads');
                    $this->addAlert('Vast Ad item deleted successfully !', 'success');
                    $this->saveAlerts();
                    Helper::redirect('ads');
                } else {
                    $this->_404();
                }
            }
            $ads = [];
            $this->db->where('type', 'vast');
            $ads['vast'] = $this->db->get('ads');
            $this->db->where('type', 'popad');
            $ads['popad'] = $this->db->get('ads');
            $ads['popad'] = $ads['popad'][0]['code'];
            $this->addData($ads, 'ads');
            $this->display('ads');
        }

        /**
         * Ajax request
         * @author CodySeller <https://codyseller.com>
         * @since 1.3
         */
        protected function ajax() {
            // usleep(rand(500000, 1000000));
            $resp = ['success' => false];
            $err = '';
            if (isset($_GET['type'])) {
                switch ($_GET['type']) {
                    case 'convert-to-hls':
                        $linkId = Helper::getReqData('linkId');
                        $serverId = Helper::getReqData('sId');
                        $link = new Link;
                        $servers = new Server;
                        // if(isset($_SESSION['hls_active_domain'])) exit;
                        if (!empty($linkId) && !empty($serverId)) {
                            if ($link->isExit($linkId, 'id')) {
                                //link data
                                $link->load($linkId);
                                $file = $link->getObj();
                                if ($file['type'] == 'GDrive') {
                                    //check server exist or not
                                    $hlsServer = $servers->get('hls', 'active', 0);
                                   
                                    if (!empty($hlsServer)) {
                                        
                                        foreach($hlsServer as $hlsV){
                                            
                                            if($serverId == $hlsV['id']){
                                                $hlsServer = $hlsV;
                                                break;
                                            }
                                        }
                                        $hlsLink = new HlsLink;
                                        $existHlsLinks = $hlsLink->get($linkId, $serverId);
                                        if (empty($existHlsLinks)) {
                                            //attempt to get gauth token
                                            $mydrive = new MyDrive2($file['acc_id']);
                                            if ($at = $mydrive->getAccessToken()) {
                                                $fileId = Helper::random(15);
                                                $driveId = Helper::getDriveId($file['main_link']);
                                                $data = ['id' => $fileId, 'file' => $driveId, 'token' => base64_encode(Helper::e($at)) ];
                                                $results = $mydrive->getFile($driveId, "size");
                                                if (!empty($results)) {
                                                    $fileSize = $results->getSize();
                                                    if (!empty($fileSize)) {
                                                        $hlsLinksData = ['link_id' => $linkId, 'server_id' => $serverId, 'file_id' => Helper::e($fileId), 'file_size' => $fileSize, 'status' => 1];
                                                        if ($hlsLink->assign($hlsLinksData)->save()) {
                                                            $myHLS = new MyHLS($hlsServer['domain']);
                                                            $myHLS->set($data)->convert();
                                                        } else {
                                                            $e = $hlsLink->getError();
                                                        }
                                                    } else {
                                                        $e = 'Something went wrong !';
                                                    }
                                                } else {
                                                    $e = $mydrive->getError();
                                                }
                                            } else {
                                                $e = 'GAuth acces token failed.';
                                            }
                                        } else {
                                            $e = 'HLS link is already exist !';
                                        }


                                    } else {
                                        $e = 'HLS server does not exist !';
                                    }
                                } else {
                                    $e = 'It is not gdrive file.';
                                }
                            } else {
                                $e = 'Main link does not exist.';
                            }
                        } else {
                            $e = 'Invalid request !';
                        }
                        if (isset($e)) {
                            $resp = ['success' => false, 'error' => $e];
                        } else {
                            $resp = ['success' => true];
                        }
                    break;
                    case 'get-hls-converter-status':
                        $sid = Helper::getReqData('sId');
                        $linkId = Helper::getReqData('linkId');
                        if (!empty($sid) && !empty($linkId)) {
                            $servers = new Server;
                            $hlsServer = $servers->findById($sid);
                            if (!empty($hlsServer)) {
                                $hlsLink = new HlsLink;
                                $slink = $hlsLink->get($linkId, $sid);
                                if (!empty($slink)) {
                                    $slink = $slink[0];
                                    $myHLS = new MyHLS($hlsServer['domain']);
                                    $status = $myHLS->getStatus($slink['file_id']);
                                    if ($status !== false) {
                                        if ($status['status'] == 'exist') {
                                            //attempt to save file
                                            if ($slink['status'] == 1) {
                                                $hlsLink->broken($slink['id'], false);
                                            }
                                        }
                                        if ($status['status'] == 'processing' && isset($status['data'])) {
                                            if (isset($status['data']['source']) && $status['data']['progress']) {
                                                if ($status['data']['source'] == 'ffmpeg') {
                                                    $completed = round(($status['data']['progress'] / $slink['file_size']) * 100);
                                                    if ($completed > 100) $completed = 100;
                                                    $status['data']['progress'] = $completed;
                                                }
                                            }
                                        }
                                        if ($status['status'] == 'not exist') {
                                            if ($slink['status'] == 0) {
                                                $hlsLink->broken($slink['id']);
                                            }
                                        }
                                        $resp = ['success' => true, 'data' => $status];
                                    }
                                }
                            }
                        }
                        break;
                    case 'make-drive-copy':
                        $fileId = Helper::getReqData('fileId');
                        $activeAcc = Helper::getReqData('activeDriveId');
                        $copyTo = Helper::getReqData('selectedDriveId');
                        if (!empty($fileId) && !empty($activeAcc) && !empty($copyTo)) {
                            $mydrive = new MyDrive2($activeAcc);
                            $driveAccounts = $this->getDriveAccounts(true);
                            if (array_key_exists($copyTo, $driveAccounts)) {
                                $tmpD = [$copyTo => $driveAccounts[$copyTo]];
                                $results = $mydrive->makeBackup($fileId, $tmpD);
                                if (!empty($results)) {
                                    foreach ($results as $result) {
                                        $newFileId = $result['fileId'];
                                    }
                                } else {
                                    $e = $mydrive->getError();
                                }
                            } else {
                                $e = 'Current drive account does not exist !';
                            }
                        } else {
                            $e = 'Invalid request !';
                        }
                        if (isset($newFileId)) {
                            $resp = ['success' => true, 'fileId' => $newFileId];
                        } else {
                            $resp = ['success' => true, 'error' => $e];
                        }
                        break;
                    case 'backup-drive-file':
                        $id = Helper::getReqData('id');
                        $selecetdDrives = Helper::getReqData('selected-drives');
                        if (!empty($id) && is_numeric($id) && !empty($selecetdDrives)) {
                            $selecetdDrives = explode(',', rtrim($selecetdDrives, ','));
                            $link = new Link();
                            if ($link->isExit($id, 'id')) {
                                $file = $link->findById($id);
                                if ($file['type'] == 'GDrive') {
                                    $fId = Helper::getDriveId($file['main_link']);
                                    $response = $this->setupBackupDrives($fId, $id, $file['acc_id'], $selecetdDrives);
                                }
                            } else {
                                $err = 'Main file does not exist !';
                            }
                        } else {
                            $err = 'Invalid request !';
                        }
                        if (!empty($response)) {
                            $resp = ['success' => true, 'data' => $response];
                        } else {
                            $resp = ['success' => false, 'error' => $err];
                        }
                        break;
                    case 'upload-to-drive':
                        $driveId = Helper::getReqData('driveId');
                        $url = Helper::getReqData('url');
                        $folderId = Helper::getReqData('folderId');
                        $filename = Helper::getReqData('filename');
                        $isOk = false;
                        $uploadedFileId = 0;
                        if (!empty($url)) {
                            if (Helper::isUrl($url)) {
                                if ($this->isDriveExist($driveId)) {
                                    $mydrive = new MyDrive2($driveId);
                                    if (!$mydrive->hasError()) {
                                        @file_put_contents(MyDrive2::uniqFile(), '');
                                        if ($up = $mydrive->uploadFile($url, $folderId, $filename)) {
                                            $isOk = true;
                                            $uploadedFileId = $up;
                                        } else {
                                            if ($mydrive->hasError()) {
                                                $error = $mydrive->getError();
                                            } else {
                                                $error = 'Upload Failed. Unknown error !';
                                            }
                                        }
                                    } else {
                                        $error = $mydrive->getError();
                                    }
                                } else {
                                    $error = 'Current drive does not exist !';
                                }
                            } else {
                                $error = 'Invalid URL !';
                            }
                        } else {
                            $error = 'Upload URL is required !';
                        }
                        break;
                    case 'get-drive-upload-process':
                        if (file_exists(MyDrive2::uniqFile())) {
                            $data = @file_get_contents(MyDrive2::uniqFile());
                            if (!empty($data) && Helper::isJson($data)) {
                                $data = json_decode($data, true);
                            }
                            $resp = ['success' => true, 'data' => $data];
                        }
                        break;
                    case 'change-drive-permission':
                        $driveId = Helper::getReqData('driveId');
                        $status = Helper::getReqData('status');
                        $fileId = Helper::getReqData('fileId');
                        $isOk = false;
                        if ($this->isDriveExist($driveId)) {
                            $mydrive = new MyDrive($driveId);
                            if (!$mydrive->hasError() && $mydrive->changeSharedPermission($fileId, $status)) {
                                $isOk = true;
                            }
                        }
                        $resp = ['success' => $isOk, 'error' => $mydrive->getError() ];
                        break;
                    case 'del-drive-file':
                        $driveId = Helper::getReqData('driveId');
                        $fileId = Helper::getReqData('fileId');
                        $isOk = false;
                        $error = '';
                        if (MY_DRIVE_FILE_DELETE_ACTION) {
                            if ($this->isDriveExist($driveId)) {
                                $mydrive = new MyDrive($driveId);
                                if (!$mydrive->hasError() && (empty($mydrive->deleteFile($fileId)) && !$mydrive->hasError())) {
                                    $isOk = true;
                                } else {
                                    $error = $mydrive->getError();
                                }
                            } else {
                                $error = 'Current drive does not exist !';
                            }
                        }
                        $resp = ['success' => $isOk, 'error' => $error];
                        break;
                    case 'clear-cache':
                        $files = glob(ROOT . '/data/cache/*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                            }
                        }
                        $resp = ['success' => true];
                        break;
                    case 'check-proxy':
                        $ip = Helper::getReqData('ip');
                        if (!empty($ip)) {
                            $proxy = new Proxy($this->config['proxyUser'], $this->config['proxyPass']);
                            if ($proxy->check($ip)) {
                                $resp = ['success' => true];
                            }
                        }
                        break;
                    case 'refresh-gauth':
                        $id = Helper::getReqData('id');
                        if (!empty($id) && is_numeric($id)) {
                            $gauth = new GAuth;
                            $rep = $gauth->getAccount($id);
                            $isOk = false;
                            if (!empty($rep)) {
                                $email = $rep['email'];
                                $driveAccounts = $this->getDriveAccounts();
                                if (!empty($driveAccounts)) {
                                    foreach ($driveAccounts as $daK => $daV) {
                                        if ($daV['email'] == $email) {
                                            $accId = $daK;
                                            $isOk = true;
                                            break;
                                        }
                                    }
                                    if ($isOk) {
                                        $drive = new GDrive($accId);
                                        if ($drive->isValidAuth()) {
                                            $resp = ['success' => true];
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'refresh-server':
                        $id = Helper::getReqData('id');
                        if (!empty($id)) {
                            $server = new Server($this->db, $this->config);
                            if ($server->load($id) && $server->isHit()) {
                                $resp = ['success' => true];
                            }
                        }
                        break;
                    case 'delete-link':
                        $id = Helper::getReqData('id');
                        if (!empty($id) && is_numeric($id)) {
                            $link = new Link();
                            if ($link->delete($id)) {
                                $resp = ['success' => true];
                            }
                        }
                        break;
                    case 'delete-link-list':
                        $ids = Helper::getReqData('ids');
                        if (!empty($ids)) {
                            $link = new Link($this->config);
                            $ids = explode(',', str_replace(' ', '', $ids));
                            if ($link->multiDelete($ids)) {
                                $resp = ['success' => true];
                            }
                        }
                        break;
                    case 'import-link':
                        $url = Helper::getReqData('url');
                        $driveId = Helper::getReqData('driveId');
                        if (!empty($url) && Helper::isUrl($url)) {
                            sleep(1);
                            $type = Helper::getLinkType($url);
                            if ($type != 'Direct') {
                                $link = new Link($this->config);
                                if (!IS_DUPLICATE) {
                                    $rep = $link->search($url);
                                    if ($rep !== false) {
                                        $l = PROOT . '/links/edit/' . $rep['id'];
                                        $err = 'This link is already exist !&nbsp; <a href="' . $l . '" target="_blank class="alert-link">view exist link</a>';
                                    }
                                }
                                if ($type == 'GDrive' && !$this->isDriveExist($driveId)) {
                                    $err = 'Current drive account does not exist !';
                                }
                                if (empty($err)) {
                                    $data = ['acc_id' => $driveId, 'main_link' => $url, 'type' => $type, 'status' => 0];
                                    $link->assign($data)->save();
                                    if (!$link->hasError()) {
                                        $title = $link->obj['title'];
                                        $slug = $link->obj['slug'];
                                        $plyr = Helper::getPlyrLink($slug);
                                        if (empty($title)) $title = $link->obj['main_link'];
                                    } else {
                                        $err = $link->getError();
                                    }
                                }
                            } else {
                                $err = 'Link Format Not Supported !';
                            }
                        } else {
                            $err = 'Invalid URL !';
                        }
                        if (empty($err)) {
                            $resp = ['success' => true, 'title' => $title, 'plyr' => $plyr];
                        } else {
                            $resp = ['success' => false, 'error' => $err];
                        }
                        break;
                    }
                }
                $this->jsonResponse($resp);
        }
        /**
         * Servers page
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        protected function servers($action = '', $id = '') {
            // dnd($this->getDriveAccounts());
            $this->setTitle('Servers | ' . APP_NAME . ' Application');
            $server = new Server($this->db, $this->config);
            switch ($action) {
                case 'del-server-links':
                    if (!empty($id)) {
                        $delServer = $server->findById($id);
                        $hlsLinks = new HlsLink;
                        if (!empty($delServer)) {
                            if ($delServer['type'] == 'hls' && $delServer['is_deleted'] == 1) {
                                if ($hlsLinks->delServerAll($delServer['id'])) {
                                    $server->del($delServer['id']);
                                }
                            }
                        }
                    }
                    Helper::redirect('servers');
                break;
                case 'del':
                    $id = Helper::clean($id);
                    if (!empty($id)) {
                        $hlsLinks = new HlsLink;
                        $sofDel = false;
                        $delServer = $server->findById($id);
                        if (!empty($delServer)) {
                            if ($delServer['type'] == 'hls') {
                                $activeHlsLinks = $hlsLinks->getHlsIds();
                                foreach ($activeHlsLinks as $ac) {
                                    if ($ac['id'] == $id) {
                                        $sofDel = true;
                                        break;
                                    }
                                }
                            }
                        }
                        $server->del($id, $sofDel);
                    }
                    Helper::redirect('servers');
                break;
                case 'status':
                    $id = Helper::clean($id);
                    if (!empty($id) && $server->load($id)) {
                        $server->changeStatus();
                    }
                    Helper::redirect('servers');
                break;
                default:
                    if (Helper::isPost()) {
                        $name = Helper::getReqData('name');
                        $domain = Helper::getReqData('domain');
                        $type = Helper::getReqData('type');
                        $id = Helper::getReqData('id');
                        $allowedTypes = ['stream', 'hls'];
                        $isBroken = 1;
                        if (!empty($id)) {
                            if (!$server->load($id)) {
                                $this->error = 'Something went wrong !';
                            }
                        }
                        if (empty($type) || !in_array($type, $allowedTypes)) {
                            $this->addAlert('Invalid server type !', 'danger');
                        }
                        if (!empty($domain)) {
                            $domain = rtrim($domain, '/');
                            if (Helper::isUrl($domain)) {
                                if ($type == 'hls') {
                                    $surl = $domain . '/check';
                                } else {
                                    $surl = $domain . '/stream/check';
                                }
                                $resp = Helper::curl($surl);
                                if (!empty($resp)) {
                                    if (strpos($resp, 'Looks Good') !== false) {
                                        $isBroken = 0;
                                    } elseif (Helper::isJson($resp)) {
                                        $resp = json_decode($resp, true);
                                        $ald = "<ul>";
                                        foreach ($resp as $e) {
                                            $ald.= "<li>{$e}</li>";
                                        }
                                        $ald.= "</ul>";
                                        $this->addAlert($ald, 'warning');
                                    } else {
                                        if (!empty($resp)) {
                                            $this->addAlert('' . $resp, 'danger');
                                        } else {
                                            $this->addAlert('Server connection failed ! Unknown error.', 'warning');
                                        }
                                    }
                                } else {
                                    $this->addAlert('Server connection failed !', 'warning');
                                }
                                // $resp = Helper::isI($domain . '/stream/test');
                                // if ($resp == 200)
                                // {
                                //     $isBroken = 0;
                                // }
                                
                            } else {
                                $this->addAlert('Invalid domain URL !', 'danger');
                            }
                        } else {
                            $this->addAlert('Domain is required !', 'danger');
                        }
                        if (empty($name)) $name = 'My server';
                        if (!$this->hasAlerts()) {
                            $data = ['name' => $name, 'domain' => $domain, 'type' => $type, 'is_broken' => $isBroken, 'is_deleted' => 0];
                            $server->assign($data)->save();
                            if (!$server->hasError()) {
                                $this->addAlert('Saved changes successfully !', 'success');
                            } else {
                                $this->addAlert($server->getError(), 'danger');
                            }
                            $this->saveAlerts();
                            Helper::redirect('servers');
                        }
                    }
                    $deletedServers = $server->getDeletedServers();
                    $hlsLinks = new HlsLink;
                    $activeHlsLinks = $hlsLinks->getHlsIds();
                    $serverIds = [];
                    $removedServerIds = [];
                    if (!empty($deletedServers)) {
                        foreach ($deletedServers as $s) {
                            $serverIds[$s['id']] = $s;
                        }
                    }
                    if (!empty($activeHlsLinks)) {
                        foreach ($activeHlsLinks as $ac) {
                            if (array_key_exists($ac['id'], $serverIds)) {
                                $removedServerIds[$ac['id']] = $serverIds[$ac['id']]['domain'];
                            }
                        }
                    }
                    $servers = $server->getAll();
                    $hlsStData = [];
                    if (!empty($servers)) {
                        foreach ($servers as $sK => $sV) {
                            if ($sV['type'] == 'hls') {
                                $myHls = new MyHLS($sV['domain']);
                                $stD = $myHls->getStorageData();
                                if (!empty($stD)) {
                                    if (!empty($stD['total'])) {
                                        $used = !empty($stD['used']) ? $stD['used'] : 1;
                                        $free = !empty($stD['free']) ? $stD['free'] : 1;
                                        $used = round($used / $stD['total'] * 100);
                                        $free = round($free / $stD['total'] * 100);
                                        $stD['meta'] = ['used' => $used, 'free' => $free];
                                        $stD['used'] = Helper::formatSizeUnits($stD['used']);
                                        $stD['free'] = Helper::formatSizeUnits($stD['free']);
                                        $stD['total'] = Helper::formatSizeUnits($stD['total']);
                                        //get tlinks
                                        $totalHlsLinks = $hlsLinks->get('', $sV['id']);
                                        if (!empty($totalHlsLinks)) {
                                            $totalHlsLinks = count($totalHlsLinks);
                                        } else {
                                            $totalHlsLinks = 0;
                                        }
                                        $hlsStData[] = ['server' => $sV['name'], 'tLinks' => $totalHlsLinks, 'data' => $stD];
                                    }
                                }
                            }
                        }
                    }
                    $this->addData($servers, 'servers');
                    $this->addData($hlsStData, 'hlsStData');
                    $this->addData($removedServerIds, 'removedServerIds');
                    $this->display('servers');
                    break;
                }
        }

        /**
         * API request
         * @author CodySeller <https://codyseller.com>
         * @since 1.5
         */
        protected function api($action = '') {
            $link = new Link($this->config);
            $resp = ['status' => 'failed'];
            switch ($action) {
                case 'refresh':
                    $id = Helper::getReqData('id');
                    $isHit = false;
                    if (!empty($id)) {
                        if ($link->isExit($id)) {
                            $link->load($id, 'slug');
                            $file = $link->obj;
                            if ($file['type'] == 'GDrive') {
                                if (!empty($file['data'])) {
                                    $isHit = Helper::isHit($file['data'], $id);
                                }
                                if (empty($file['data']) || !$isHit) {
                                    if (!empty($this->getDriveS($link))) {
                                        $isHit = true;
                                    }
                                }
                                if ($isHit) $resp = ['status' => 'success'];
                            }
                        }
                    }
                    break;
                }
                $this->jsonResponse($resp);
        }




        /**
         * Get gdrive sources
         * @author CodySeller <https://codyseller.com>
         * @since 1.5
         */
        protected function getDriveS($link, $alt = false) {
            $dl = $isBroken = false;
            $file = $link->obj;
            $isA = false;
            if (!($alt && $link->obj['is_alt'])) {
                $link->refresh($alt);
                if (!$link->hasError()) {
                    if (!empty($link->obj['data'])) {
                        $file = $link->obj;
                    } else {
                        $dl = true;
                        $dlSources = [['file' => PROOT . "/stream/360/{$file['slug']}/" . GDRIVE_IDENTIFY, 'q' => 360]];
                    }
                } else {
                    $isBroken = true;
                }
            }
            if (!$isBroken) {
                return !$dl ? Helper::getDriveData($file) : $dlSources;
            } else {
                return false;
            }
        }



        /**
         * Video page
         * @author CodySeller <https://codyseller.com>
         * @since 1.3
         */
        protected function video($id = '') {
            $this->firewall();
            $link = new Link;
            $isOk = false;
            if ($link->isExit($id)) {
                $link->load($id, 'slug');
                $video = new Video($link);
                if ($video->load()) {
                    // $videoData = $video->getData();
                    $logo = PROOT . '/uploads/' . $this->config['logo'];
                    $this->addData(@base64_decode($this->getPopAds()), 'popads');
                    $this->addData($this->getPlyrAds(), 'ads');
                    $this->addData($logo, 'logo');
                    $this->addData($video->getTmpLinks(), 'servers');
                    $this->addData($video->getData(), 'data');
                    $this->display('players/' . $video->getPlayer(), true);
                    $isOk = true;
                } else {
                    die("<h1>Video is unavailable</h1>");
                    exit;
                }

            }
            if (!$isOk) $this->_404();
        }
        /**
         * Get vast ads
         * @author CodySeller <https://codyseller.com>
         * @since 1.3
         */
        protected function getPlyrAds() {
            $this->db->where('type', 'vast');
            $ads = $this->db->get('ads');
            if (!empty($ads) && is_array($ads)) {
                $adList = [];
                foreach ($ads as $ad) {
                    if (!empty($ad['code'])) {
                        $adList[] = $ad['code'];
                    }
                }
                $adList = implode(', ', $adList);
                return $adList;
            }
            return '';
        }
        /**
         * Set page title
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        protected function setTitle($t = '') {
            $this->addData($t, 'ptitle');
        }
        /**
         * Get page title
         * @author CodySeller <https://codyseller.com>
         * @since 2.2
         */
        protected function getTitle() {
            return isset($this->data['ptitle']) ? $this->data['ptitle'] : '';
        }
        /**
         * Get popads
         * @author CodySeller <https://codyseller.com>
         * @since 1.3
         */
        protected function getPopAds() {
            $this->db->where('type', 'popad');
            $popads = $this->db->get('ads');
            return $popads[0]['code'];
        }
        /**
         * Links page
         * @author CodySeller <https://codyseller.com>
         * @since 1.3
         */
        protected function links($action = '', $id = '') {
            $link = new Link();
            $server = new Server();
            if (!empty($action)) {
                switch ($action) {
                    case 'new':
                    case 'edit':
                        $this->setTitle('Add/Edit Link | ' . APP_NAME . ' Application');
                        $isEdit = ($action == 'edit') ? true : false;
                        if ($isEdit) {
                            if (!empty($id) && is_numeric($id) && $link->isExit($id, 'id')) {
                                $link->load($id);
                                $isEdit = true;
                            }
                        }
                        if (Helper::isPost()) {
                            $mainLink = Helper::getReqData('main_link');
                            $altLinks = Helper::getReqData('alt');
                            $slug = Helper::getReqData('slug');
                            $status = Helper::getReqData('status');
                            $title = Helper::getReqData('title');
                            $sublist = Helper::getReqData('sub');
                            $accountId = Helper::getReqData('accountId');
                            $previewImg = $type = '';
                            $subAllowedExt = ['vtt', 'srt', 'dfxp', 'ttml', 'xml'];
                            $imgAllowedExt = ['jpg', 'jpeg', 'png'];
                            $subs = [];
                            $altLink = '';
                            if (empty($mainLink)) {
                                $this->addAlert('Main link is required !', 'danger');
                            } else {
                                if (!Helper::isUrl($mainLink)) {
                                    $this->addAlert('Invalid URL provided for main link !', 'danger');
                                } else {
                                    if (!IS_DUPLICATE) {
                                        $rep = $link->search($mainLink);
                                        if ($rep !== false) {
                                            $l = PROOT . '/links/edit/' . $rep['id'];
                                            $this->addAlert('Main link is already exist !&nbsp; <a href="' . $l . '" class="alert-link">view exist link</a>', 'danger');
                                        }
                                    }
                                    //get account
                                    //
                                    if (Helper::isDrive($mainLink) && !$this->isDriveExist($accountId)) {
                                        $this->addAlert('Current drive account does not exist !', 'danger');
                                        // $accountId = $this->getCurrentDriveAccountId(Helper::getDriveId($mainLink), $currentDriveEmail);
                                        
                                    }
                                }
                            }
                            if (!empty($altLink) && !Helper::isUrl($altLink)) {
                                $this->addAlert('Invalid URL provided for alternative link !', 'danger');
                            }
                            if (!empty($slug)) {
                                if ($link->isExit($slug)) {
                                    $this->addAlert('Video slug is already exist !', 'danger');
                                }
                            }
                            if (!$this->hasAlerts()) {
                                if (isset($_FILES['sub'])) {
                                    //attempt to upload files
                                    $upload = new Upload(SUB_UPLOAD_DIR);
                                    $upload->setExt($subAllowedExt);
                                    if (!$upload->hasError()) {
                                        $upload->upload($_FILES['sub']);
                                        $resp = $upload->getResp();
                                        if (isset($resp['s'])) {
                                            foreach ($sublist as $sk => $sv) {
                                                if (array_key_exists($sk, $resp['s'])) {
                                                    if ($isEdit) {
                                                        if (!empty($sublist[$sk]['file'])) {
                                                            $subFile = ROOT . '/uploads/' . SUB_UPLOAD_DIR . '/' . $sublist[$sk]['file'];
                                                            if (file_exists($subFile)) {
                                                                unlink($subFile);
                                                            }
                                                        }
                                                        $sublist[$sk]['file'] = $resp['s'][$sk];
                                                    } else {
                                                        $subs[] = json_encode(['label' => $sv['label'], 'file' => $resp['s'][$sk]]);
                                                    }
                                                }
                                            }
                                        }
                                        if (isset($resp['e'])) {
                                            foreach ($resp['e'] as $e) {
                                                $this->addAlert($e, 'warning');
                                            }
                                        }
                                    } else {
                                        $this->addAlert($upload->getError(), 'warning');
                                    }
                                }
                                if (isset($_FILES['preview_image'])) {
                                    $upload = new Upload(BANNER_UPLOAD_DIR);
                                    $upload->setExt($imgAllowedExt);
                                    if (!$upload->hasError()) {
                                        $upload->upload($_FILES['preview_image']);
                                        $resp = $upload->getResp();
                                        if (isset($resp['s'][0])) {
                                            $previewImg = $resp['s'][0];
                                        } else {
                                            if (isset($resp['e'])) {
                                                foreach ($resp['e'] as $e) {
                                                    $this->addAlert($e, 'warning');
                                                }
                                            }
                                        }
                                    } else {
                                        $this->addAlert($upload->getError(), 'warning');
                                    }
                                }
                                if ($isEdit && isset($_POST['preview_image'])) {
                                    $op = $_POST['preview_image'];
                                    if ((!empty($previewImg) && $previewImg != $op) || isset($_POST['pre_img_del'])) {
                                        $rp = ROOT . '/uploads/' . BANNER_UPLOAD_DIR . '/' . $op;
                                        if (file_exists($rp)) {
                                            unlink($rp);
                                        }
                                    } else {
                                        $previewImg = $op;
                                    }
                                }
                                if ($isEdit) {
                                    if (!empty($sublist) && is_array($sublist)) {
                                        foreach ($sublist as $sub) {
                                            if (is_array($sub) && !empty($sub['file'])) {
                                                if (!isset($sub['is_remove']) || (isset($sub['is_remove']) && $sub['is_remove'] == 0)) {
                                                    if (isset($sub['is_remove'])) {
                                                        unset($sub['is_remove']);
                                                    }
                                                    $subs[] = json_encode($sub);
                                                } else {
                                                    $subFile = ROOT . '/uploads/' . SUB_UPLOAD_DIR . '/' . $sub['file'];
                                                    if (file_exists($subFile)) {
                                                        unlink($subFile);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                $subs = !empty($subs) && is_array($subs) ? implode(',', $subs) : '';
                                $status = !empty($status) && $status == 'active' ? 0 : 1;
                                $type = Helper::getLinkType($mainLink);
                                $data = ['acc_id' => $accountId, 'title' => $title, 'main_link' => $mainLink, 'alt_link' => $altLink, 'subtitles' => $subs, 'slug' => $slug, 'type' => $type, 'preview_img' => $previewImg, 'status' => $status];
                                $link->setAltLinks($altLinks);
                                $link->assign($data)->save();
                                if (!$link->hasError()) {
                                    $id = $link->getID();
                                    if ($type == 'GDrive' && $isEdit) {
                                        $backupDrives = Helper::getReqData('backup_drives');
                                        if (!empty($backupDrives)) {
                                            foreach ($backupDrives as $bkdrive) {
                                                if (isset($bkdrive['id'])) {
                                                    $tmpBackupDrive = new BackupDrives;
                                                    if ($tmpBackupDrive->load($bkdrive['id'])) {
                                                        if (isset($bkdrive['is_removed']) && $bkdrive['is_removed'] == 1) {
                                                            $tmpBackupDrive->del();
                                                        } else {
                                                            if (isset($bkdrive['file'])) {
                                                                $gid = Helper::getDriveId($bkdrive['file']);
                                                                if (!empty($gid)) {
                                                                    $tmpBackupDrive->assign(['file_id' => $gid])->save();
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    // backup
                                    if ($type == 'GDrive' && !$isEdit && FH::getConfig('isAutoBackup') == 1) {
                                        $this->setupBackupDrives(Helper::getDriveId($mainLink), $id, $accountId);
                                    }
                                    //end
                                    $this->addAlert('Link saved successfully !', 'success');
                                    $this->saveAlerts();
                                    Helper::redirect("links/edit/$id");
                                } else {
                                    $this->addAlert($link->getError(), 'danger');
                                }
                            }
                        }
                        if (!$isEdit) {
                            $this->display('__new_link');
                        } else {
                            $l = $link->getObj();
                            $subtitles = $l['subtitles'];
                            if (!empty($subtitles)) {
                                $subList = @json_decode('[' . $subtitles . ']', true);
                                if (!empty($subList)) {
                                    $subtitles = $subList;
                                }
                            }
                            if (empty($subtitles)) {
                                $subtitles = [['label' => '', 'file' => '']];
                            }
                            $backupDrive = new BackupDrives;
                            $backupDriveLinks = $backupDrive->get($l['id']);
                            $hlsLink = new HlsLink;
                            $hlsLinksLists = $hlsLink->get($l['id']);
                            if (empty($l['altLinks'])) $l['altLinks'][] = ['id' => '', 'link' => ''];
                            if (empty($l['hlsLinksLists'])) $l['hlsLinksLists'][] = ['id' => '', 'link' => ''];
                            $hlsServers = $server->get('hls');
                            foreach ($hlsServers as $hlsK => $hlsV) {
                                $hlsServers[$hlsV['id']] = $hlsV;
                                unset($hlsServers[$hlsK]);
                            }
                            foreach ($hlsLinksLists as $hlsK => $hlsV) {
                                $serV = '';
                                if (array_key_exists($hlsV['server_id'], $hlsServers)) {
                                    $serV = $hlsServers[$hlsV['server_id']]['domain'];
                                }
                                $hlsLinksLists[$hlsK]['file'] = MyHLS::getHlsLink($serV, $hlsV['file_id']);
                            };
                            $l['subtitles'] = $subtitles;
                            $this->addData($l, 'link');
                            $this->addData($hlsServers, 'hlsServers');
                            $this->addData($backupDriveLinks, 'backupDriveLinks');
                            $this->addData($hlsLinksLists, 'hlsLinksLists');
                            $this->addData($link->getNextLink(), 'nextLink');
                            $this->addData($this->getDriveAccounts(true), 'driveAccounts');
                            $this->display('__edit_link');
                        }
                        break;
                    case 'del':
                        $pdel = Helper::getReqData('pdel');
                        $t = Helper::getReqData('t');
                        $linkId = Helper::getReqData('linkId');
                        $hlsLinks = new HlsLink;
                        if ($id == 'hls' && !empty($t)) {
                            $id = $t;
                            $hlsFile = $hlsLinks->findById($id);
                            if (!empty($hlsFile)) {
                               
                                if ($hlsLinks->del($id)) {
                                    if ($pdel == 'on') {
                                        $server = new Server;
                                        $hlsServer = $server->findById($hlsFile['server_id']);
                                        if (!empty($hlsServer)) {
                                            $myHls = new MyHLS($hlsServer['domain']);
                                            if ($myHls->delete($hlsFile['file_id'])) {
                                                $this->addAlert('HLS File deleted successfully !', 'success');
                                            } else {
                                                $this->addAlert('Unable to delete file from server.', 'warning');
                                            }
                                        } else {
                                            $this->addAlert('HLS server does not exist !', 'warning');
                                        }
                                    }
                                } else {
                                    $this->addAlert($hlsLinks->getError(), 'danger');
                                }
                            }
                            $this->saveAlerts();
                            Helper::redirect('links/edit/' . $linkId);
                        } else {
                            if (!empty($id) && is_numeric($id)) {
                                $link = new Link();
                                if ($link->delete($id)) {
                                    $resp = ['success' => true];
                                }
                            }
                            Helper::redirect('links/active');
                        }
                        break;
                    case 'all':
                    case 'broken':
                    case 'paused':
                    case 'active':
                        $this->setTitle('All Links | ' . APP_NAME . ' Application');
                        $links = $link->getAll($action);
                        $this->addData($links, 'links');
                        $this->addData($action, 'action');
                        $this->display('links');
                        break;
                    default:
                        $this->_404();
                        break;
                    }
                } else {
                    $this->_404();
                }
        }
    public function setupBackupDrives($fileId, $linkId, $accId, $selectedDrives = []) {
        $backupDrives = $this->getDriveAccounts(true, true);
        $backupDrive = new BackupDrives;
        $errors = $success = $exists = $errMsgs = [];
        if (!empty($selectedDrives)) {
            $backupDrives2 = [];
            foreach ($selectedDrives as $bd) {
                if (array_key_exists($bd, $backupDrives)) {
                    $backupDrives2[$bd] = $backupDrives[$bd];
                }
            }
            $backupDrives = $backupDrives2;
        }
        if (!empty($backupDrives)) {
            $mydrive = new MyDrive2($accId);
            $backups = $backupDrive->get($linkId, $accId);
            if (!empty($backups)) {
                foreach ($backups as $bk => $backV) {
                    if (array_key_exists($backV['acc_id'], $backupDrives)) {
                        $exists[] = $backupDrives[$backV['acc_id']]['email'];
                        unset($backupDrives[$backV['acc_id']]);
                    }
                }
            }
            if (!empty($backupDrives)) {
                $results = $mydrive->makeBackup($fileId, $backupDrives);
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $tmpObj = new BackupDrives;
                        $data = ['link_id' => $linkId, 'acc_id' => $result['accId'], 'file_id' => $result['fileId'], 'status' => 0];
                        $success[] = $backupDrives[$result['accId']]['email'];
                        unset($backupDrives[$result['accId']]);
                        $tmpObj->assign($data)->save();
                    }
                }
                if (!empty($backupDrives)) {
                    foreach ($backupDrives as $bv) {
                        $errors[] = $bv['email'];
                    }
                }
                if ($mydrive->hasError()) {
                    $e = $mydrive->getError();
                    if (Helper::isJson($e)) {
                        $errMsgs = json_decode($e, true);
                    } else {
                        $errMsgs[] = $e;
                    }
                }
            }
        } else {
            $errMsgs[] = 'Backup drives does not exist !';
        }
        return ['success' => $success, 'exists' => $exists, 'errors' => $errors, 'errMsgs' => $errMsgs];
    }
    /**
     * Bulk import page
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function bulk() {
        $driveAccounts = $this->getDriveAccounts(true);
        $activeDriveId = is_array($driveAccounts) ? array_key_first($driveAccounts) : 0;
        $this->addData($driveAccounts, 'driveAccounts');
        $this->addData($activeDriveId, 'activeDriveId');
        $this->setTitle('Bulk Import | ' . APP_NAME . ' Application');
        $this->display('bulk-import');
    }
    /**
     * Update settings
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function updateSettings($data = []) {
        foreach ($data as $config => $val) {
            $this->db->where('config', $config);
            $this->db->update('settings', ['var' => $val]);
        }
    }
    /**
     * Save application data
     * @author CodySeller <https://codyseller.com>
     * @since 1.4
     */
    protected function addData($data, $name = '') {
        if (!empty($name)) {
            $this->data[$name] = $data;
        } else {
            $this->data = $data;
        }
    }
    /**
     * Save alerts in session
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function saveAlerts() {
        $_SESSION['alerts'] = $this->alerts;
    }
    /**
     * Add alert
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function addAlert($msg, $type) {
        if (!array_key_exists($type, $this->alerts)) {
            $this->alerts[$type] = [];
        }
        $this->alerts[$type][] = $msg;
    }
    /**
     * Check alerts
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function hasAlerts($t = 'danger', $all = false) {
        if ((isset($this->alerts[$t]) && !empty($this->alerts[$t])) || ($all && !empty($this->alerts))) {
            return true;
        }
        return false;
    }
    /**
     * Disaply alerts
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function displayAlerts() {
        if ($this->hasAlerts('', true)) {
            $alertHtml = '';
            foreach ($this->alerts as $k => $v) {
                $alertHtml.= '<div class="alert alert-' . $k . ' alert-dismissible" role="alert"><b>Alert:&nbsp;</b>';
                if (count($v) == 1) {
                    $alertHtml.= $v[0];
                } else {
                    $list = '<ul>';
                    foreach ($v as $al) {
                        $list.= '<li>' . $al . '</li>';
                    }
                    $list.= '</ul>';
                    $alertHtml.= $list;
                }
                $alertHtml.= '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
                $alertHtml.= '</div>';
            }
            echo $alertHtml;
        }
    }
    /**
     * Get user's username
     * @since 1.3
     */
    protected function getUsername() {
        return $this->logged ? $_SESSION['user'] : '';
    }
    /**
     * Check adblock enbled or not
     * @since 2.2
     */
    protected function isAdblockEnabled() {
        return $this->config['isAdblocker'] == 1 ? true : false;
    }
    /**
     * Check video preloader
     * @since 2.2
     */
    protected function isPreloaderEnabled() {
        return $this->config['v_preloader'] == 1 ? true : false;
    }
    /**
     * Display template pages
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function display($template, $isBlank = false) {
        if (is_array($this->data)) {
            foreach ($this->data as $k => $v) {
                $$k = $v;
            }
        } else {
            $data = $this->data;
        }
        if (!file_exists(TEMPLATE . '/' . $template . '.php')) {
            //template file not found
            die('File ' . TEMPLATE . '/' . $template . '.php not found !');
        }
        if (!$isBlank) {
            $this->header();
            include (TEMPLATE . '/' . $template . '.php');
            $this->footer();
        } else {
            include (TEMPLATE . '/' . $template . '.php');
        }
    }
    /**
     * Template header
     * @since 1.3
     */
    protected function header() {
        include ($this->t(__FUNCTION__));
    }
    /**
     * Template footer
     * @since 1.3
     */
    protected function footer() {
        include ($this->t(__FUNCTION__));
    }
    /**
     * Get template part
     * @since 1.3
     */
    protected function t($template) {
        if (!file_exists(TEMPLATE . '/' . $template . '.php')) die('File ' . $template . ' does not exist !');
        return TEMPLATE . '/' . $template . '.php';
    }
    /**
     * Response JSON data
     * @since 1.3
     *
     */
    protected function jsonResponse($resp) {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: applicaton/json; charset=UTF-8");
        http_response_code(200);
        echo json_encode($resp);
        exit;
    }
    /**
     * Check active page (action)
     * @since 2.2
     */
    public function getAT($a) {
        return $this->action == $a ? 'active' : '';
    }
    /**
     * Get player
     * @since 2.2
     */
    public function getPlyr() {
        return $this->config['player'] == 'jw' ? 'jw' : 'plyr';
    }
    /**
     * Get player
     * @since 2.2
     */
    public function getJWLicense() {
        return $this->config['jw_license'];
    }
    /**
     * Firewall
     * @author CodySeller <https://codyseller.com>
     * @since 1.3
     */
    protected function firewall($s = false) {
        if (FIREWALL || ($s && !DIRECT_STREAM)) {
            $domains = ALLOWED_DOMAINS;
            if (!is_array($domains)) $domains = [];
            if ($s) {
                $domains[] = Helper::getHost();
            }
            if (!isset($_SERVER["HTTP_REFERER"])) {
                $this->display('lol', true);
                exit;
            }
            $referer = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
            if (empty($referer) || !in_array($referer, $domains)) {
                $this->_404();
                exit;
            }
        }
    }
    protected function _lol() {
        $this->display('lol', true);
        exit;
    }
    /**
     * 404 page
     * @since 1.3
     *
     */
    protected function _404() {
        header('HTTP/1.1 404 Not Found');
        die('<h1>404 page not found !</h1>');
    }
    /**
     * Server Error
     * @since 1.3
     *
     */
    protected function _400() {
        header('HTTP/1.1 400 bad request');
        die('<h1>400 Bad Request !</h1>');
    }
    /**
     * Destructor: End of the application
     * @since 1.3
     *
     */
    public function __destruct() {
        $this->db->disconnectAll();
        $this->userId = NULL;
        $this->config = $this->db = NULL;
    }
}
//End
